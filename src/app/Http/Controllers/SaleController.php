<?php

namespace App\Http\Controllers;

use App\Domain\Branch\Services\BranchService;
use App\Models\Branch;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    public function create()
    {
        $branches = Branch::where('is_active', true)->orderBy('code')->get();

        return view('sales.create', compact('branches'));
    }

    public function store(Request $request, BranchService $branches)
    {
        $data = $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
            'amount' => ['required', 'integer', 'min:1'],
            'cogs' => ['nullable', 'integer', 'min:0'],
            'entry_date' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $trx = $branches->recordSale(
            (int) $data['branch_id'],
            $data['entry_date'],
            (int) $data['amount'],
            (int) ($data['cogs'] ?? 0),
            $data['description'] ?? null,
            null,
            $request->user()->id,
        );

        return redirect()->route('sales.create')
            ->with('success', "Penjualan tercatat, jurnal #{$trx->journal_entry_id} diposting dan ringkasan cabang diperbarui.");
    }
}
