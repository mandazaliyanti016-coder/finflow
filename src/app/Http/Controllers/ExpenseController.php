<?php

namespace App\Http\Controllers;

use App\Domain\Branch\Services\BranchService;
use App\Models\Account;
use App\Models\Branch;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function create()
    {
        return view('expenses.create', [
            'branches' => Branch::where('is_active', true)->orderBy('code')->get(),
            'accounts' => Account::whereIn('code', BranchService::EXPENSE_ACCOUNTS)->orderBy('code')->get(),
        ]);
    }

    public function store(Request $request, BranchService $branches)
    {
        $data = $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
            'account_code' => ['required', 'in:'.implode(',', BranchService::EXPENSE_ACCOUNTS)],
            'amount' => ['required', 'integer', 'min:1'],
            'entry_date' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $trx = $branches->recordExpense(
            (int) $data['branch_id'], $data['entry_date'], (int) $data['amount'],
            $data['account_code'], $data['description'] ?? null, null, $request->user()->id,
        );

        return redirect()->route('expenses.create')
            ->with('success', "Biaya tercatat, jurnal #{$trx->journal_entry_id} diposting.");
    }
}
