<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Branch;
use App\Models\CashReconciliation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CashReconciliationController extends Controller
{
    public function index(Request $request)
    {
        $branches = Branch::where('is_active', true)->orderBy('code')->get();
        $branchId = (int) $request->query('branch_id', $branches->first()?->id);
        $date = $request->query('date', now()->toDateString());

        $systemCash = $branchId ? $this->systemCash($branchId, $date) : 0;

        $history = CashReconciliation::join('branches', 'branches.id', '=', 'cash_reconciliations.branch_id')
            ->orderByDesc('recon_date')->orderBy('branches.code')->limit(20)
            ->get(['cash_reconciliations.*', 'branches.code as branch_code']);

        return view('reconciliation.index', compact('branches', 'branchId', 'date', 'systemCash', 'history'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
            'recon_date' => ['required', 'date'],
            'physical_cash' => ['required', 'integer', 'min:0'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $system = $this->systemCash((int) $data['branch_id'], $data['recon_date']);
        $physical = (int) $data['physical_cash'];

        CashReconciliation::updateOrCreate(
            ['branch_id' => $data['branch_id'], 'recon_date' => $data['recon_date']],
            [
                'system_cash' => $system,
                'physical_cash' => $physical,
                'difference' => $physical - $system,
                'note' => $data['note'] ?? null,
                'created_by' => $request->user()->id,
            ]
        );

        return redirect()->route('reconciliation.index', ['branch_id' => $data['branch_id'], 'date' => $data['recon_date']])
            ->with('success', 'Rekonsiliasi kas disimpan.');
    }

    /** Saldo kas per cabang sampai dengan tanggal tertentu, dihitung dari buku besar. */
    private function systemCash(int $branchId, string $date): int
    {
        $kasId = Account::where('code', '1-1000')->value('id');

        return (int) DB::table('journal_lines')
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
            ->where('journal_lines.account_id', $kasId)
            ->where('journal_entries.branch_id', $branchId)
            ->where('journal_entries.entry_date', '<=', $date)
            ->selectRaw('COALESCE(SUM(debit),0) - COALESCE(SUM(credit),0) AS bal')
            ->value('bal');
    }
}
