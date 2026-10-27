<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LedgerController extends Controller
{
    public function show(Request $request, Account $account)
    {
        $from = $request->query('from');
        $to = $request->query('to');
        $sign = $account->normal_balance === 'debit' ? 1 : -1;

        $base = DB::table('journal_lines')
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
            ->where('journal_lines.account_id', $account->id);

        $opening = 0;
        if ($from) {
            $o = (clone $base)->where('journal_entries.entry_date', '<', $from)
                ->selectRaw('COALESCE(SUM(debit),0) d, COALESCE(SUM(credit),0) c')->first();
            $opening = ($o->d - $o->c) * $sign;
        }

        $q = (clone $base)->select(
            'journal_entries.id as entry_id', 'journal_entries.entry_date',
            'journal_entries.description', 'journal_lines.debit', 'journal_lines.credit'
        );
        if ($from) {
            $q->where('journal_entries.entry_date', '>=', $from);
        }
        if ($to) {
            $q->where('journal_entries.entry_date', '<=', $to);
        }
        $rows = $q->orderBy('journal_entries.entry_date')->orderBy('journal_entries.id')->get();

        $balance = $opening;
        foreach ($rows as $r) {
            $balance += ($r->debit - $r->credit) * $sign;
            $r->balance = $balance;
        }

        return view('ledger.show', [
            'account' => $account,
            'accounts' => Account::orderBy('code')->get(),
            'rows' => $rows,
            'opening' => $opening,
            'from' => $from,
            'to' => $to,
        ]);
    }
}
