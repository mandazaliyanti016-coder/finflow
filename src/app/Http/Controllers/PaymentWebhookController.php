<?php

namespace App\Http\Controllers;

use App\Domain\Ledger\Services\LedgerService;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentWebhookController extends Controller
{
    public function handle(Request $request, LedgerService $ledger)
    {
        $expected = hash_hmac('sha256', $request->getContent(), config('services.qris.webhook_secret'));
        if (!hash_equals($expected, (string) $request->header('X-Signature'))) {
            return response()->json(['error' => 'invalid signature'], 401);
        }

        $data = $request->json()->all();
        if (empty($data['event_id']) || empty($data['external_ref']) || !isset($data['amount'])) {
            return response()->json(['error' => 'bad payload'], 422);
        }

        $status = DB::transaction(function () use ($data, $ledger) {
            $invoice = Invoice::where('external_ref', $data['external_ref'])
                ->lockForUpdate()->first();
            if (!$invoice) return 'unknown_invoice';
            if ((int) $data['amount'] !== (int) $invoice->amount) return 'amount_mismatch';

            $inserted = DB::table('webhook_events')->insertOrIgnore([
                'event_id'   => $data['event_id'],
                'payload'    => json_encode($data),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            if ($inserted === 0) return 'duplicate_event';

            if ($invoice->status === 'paid') return 'already_paid';
            if ($invoice->status === 'expired') return 'invoice_expired';

            $payment = Payment::create([
                'invoice_id' => $invoice->id,
                'amount'     => $invoice->amount,
                'paid_at'    => now(),
            ]);

            $entry = $ledger->postQrisPayment(
                $invoice->branch_id,
                (int) $invoice->amount,
                now()->toDateString(),
                $payment->id,
                "Pembayaran QRIS {$invoice->invoice_number}"
            );

            $payment->update(['journal_entry_id' => $entry->id]);
            $invoice->update(['status' => 'paid', 'paid_at' => now()]);

            return 'processed';
        });

        $http = match ($status) {
            'unknown_invoice' => 404,
            'amount_mismatch' => 422,
            default => 200,
        };

        return response()->json(['status' => $status], $http);
    }
}
