<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConsolidationController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->query('from', now()->subDays(6)->toDateString());
        $to = $request->query('to', now()->toDateString());

        $perBranch = DB::table('branch_daily_summary as s')
            ->join('branches as b', 'b.id', '=', 's.branch_id')
            ->whereBetween('s.summary_date', [$from, $to])
            ->groupBy('b.id', 'b.code', 'b.name')
            ->orderBy('b.code')
            ->selectRaw('b.code, b.name, SUM(s.revenue) revenue, SUM(s.cogs) cogs, SUM(s.gross_profit) gross_profit, SUM(s.opex) opex')
            ->get();

        $perDay = DB::table('branch_daily_summary')
            ->whereBetween('summary_date', [$from, $to])
            ->groupBy('summary_date')
            ->orderBy('summary_date')
            ->selectRaw('summary_date, SUM(revenue) revenue, SUM(cogs) cogs, SUM(gross_profit) gross_profit, SUM(opex) opex')
            ->get();

        $total = (object) [
            'revenue' => $perBranch->sum('revenue'),
            'cogs' => $perBranch->sum('cogs'),
            'gross_profit' => $perBranch->sum('gross_profit'),
            'opex' => $perBranch->sum('opex'),
        ];

        return view('consolidation.index', compact('perBranch', 'perDay', 'total', 'from', 'to'));
    }
}
