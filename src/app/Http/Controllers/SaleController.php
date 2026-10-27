<?php

namespace App\Http\Controllers;

use App\Domain\Ledger\Services\LedgerService;
use App\Models\Branch;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    public function create()
    {
        $branches = Branch::where('is_active', true)->orderBy('code')->get();

        return view('sales.create', compact('branches'));
    }

    public function store(Request $request, LedgerService $ledger)
    {
        $data = $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
            'amount' => ['required', 'integer', 'min:1'],
            'entry_date' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $entry = $ledger->postCashSale(
            (int) $data['branch_id'],
            (int) $data['amount'],
            $data['entry_date'],
            $data['description'] ?? null,
            $request->user()->id,
        );

        return redirect()
            ->route('sales.create')
            ->with('success', "Jurnal #{$entry->id} berhasil diposting (Debit Kas, Kredit Pendapatan Penjualan).");
    }
}
