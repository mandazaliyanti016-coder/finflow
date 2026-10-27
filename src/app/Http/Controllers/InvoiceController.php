<?php

namespace App\Http\Controllers;

use App\Domain\Ledger\Services\LedgerService;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InvoiceController extends Controller
{
    public function index()
    {
        $invoices = Invoice::orderByDesc('id')->limit(50)->get();
        return view('invoices.index', compact('invoices'));
    }

    public function create()
    {
        $branches = DB::table('branches')->get();
        return view('invoices.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'branch_id' => 'required|integer|exists:branches,id',
            'amount'    => 'required|integer|min:1000',
        ]);

        $ref = 'QR-' . strtoupper(Str::random(10));

        $invoice = Invoice::create([
            'branch_id'      => $data['branch_id'],
            'invoice_number' => 'INV-' . now()->format('ymd') . '-' . strtoupper(Str::random(4)),
            'external_ref'   => $ref,
            'amount'         => $data['amount'],
            'status'         => 'pending',
            'qris_payload'   => "MOCK-QRIS|{$ref}|{$data['amount']}",
            'expires_at'     => now()->addMinutes(30),
        ]);

        return redirect()->route('invoices.show', $invoice);
    }

    public function show(Invoice $invoice)
    {
        $this->expireIfNeeded($invoice);
        return view('invoices.show', compact('invoice'));
    }

    public function status(Invoice $invoice)
    {
        $this->expireIfNeeded($invoice);
        return response()->json(['status' => $invoice->status]);
    }

    /** Demo: kirim webhook bertanda tangan ke handler yang sama (tanpa HTTP, agar tidak deadlock). */
    public function simulate(Invoice $invoice, LedgerService $ledger)
    {
        $body = json_encode([
            'event_id'     => 'sim-' . Str::uuid(),
            'external_ref' => $invoice->external_ref,
            'amount'       => (int) $invoice->amount,
        ]);
        $sig = hash_hmac('sha256', $body, config('services.qris.webhook_secret'));

        $req = Request::create('/api/webhooks/payment', 'POST', [], [], [], [
            'CONTENT_TYPE'     => 'application/json',
            'HTTP_X_SIGNATURE' => $sig,
        ], $body);

        $res = app(PaymentWebhookController::class)->handle($req, $ledger);
        $result = $res->getData(true)['status'] ?? 'unknown';

        return redirect()->route('invoices.show', $invoice)->with('result', $result);
    }

    private function expireIfNeeded(Invoice $invoice): void
    {
        if ($invoice->status === 'pending' && $invoice->expires_at && $invoice->expires_at->isPast()) {
            $invoice->update(['status' => 'expired']);
        }
    }
}
