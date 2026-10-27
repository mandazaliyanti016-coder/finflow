<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Support\Facades\DB;

class AccountController extends Controller
{
    public function index()
    {
        $totals = DB::table('journal_lines')
            ->select('account_id', DB::raw('SUM(debit) AS d'), DB::raw('SUM(credit) AS c'))
            ->groupBy('account_id')
            ->get()
            ->keyBy('account_id');

        $accounts = Account::orderBy('code')->get()->map(function ($a) use ($totals) {
            $t = $totals[$a->id] ?? null;
            $d = (int) ($t->d ?? 0);
            $c = (int) ($t->c ?? 0);
            $a->balance = $a->normal_balance === 'debit' ? $d - $c : $c - $d;

            return $a;
        });

        return view('accounts.index', compact('accounts'));
    }
}
