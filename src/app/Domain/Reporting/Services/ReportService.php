<?php

namespace App\Domain\Reporting\Services;

use Illuminate\Support\Facades\DB;

class ReportService
{
    /** PP 55/2022: PPh Final UMKM 0,5% dari omzet bruto. Verifikasi tarif terkini sebelum dipakai. */
    public const FINAL_RATE = 0.005;
    /** WP orang pribadi: omzet s.d. Rp500 juta per tahun tidak dikenai PPh Final. */
    public const OP_THRESHOLD = 500000000;

    public function build(string $from, string $to, ?int $branchId, string $wpType): array
    {
        $tb  = $this->trialBalance($from, $to, $branchId);
        $is  = $this->incomeStatement($tb);
        $tax = $this->finalTax($from, $to, $branchId, $wpType);

        return compact('tb', 'is', 'tax');
    }

    public function trialBalance(string $from, string $to, ?int $branchId): array
    {
        $raw = DB::table('journal_lines as l')
            ->join('journal_entries as e', 'e.id', '=', 'l.journal_entry_id')
            ->join('accounts as a', 'a.id', '=', 'l.account_id')
            ->whereBetween('e.entry_date', [$from, $to])
            ->when($branchId, fn ($q) => $q->where('e.branch_id', $branchId))
            ->groupBy('a.code', 'a.name', 'a.type')
            ->orderBy('a.code')
            ->selectRaw('a.code, a.name, a.type, SUM(l.debit) AS debit, SUM(l.credit) AS credit')
            ->get();

        $rows = [];
        $totalD = 0;
        $totalK = 0;
        foreach ($raw as $r) {
            $debit  = (int) $r->debit;
            $credit = (int) $r->credit;
            $net    = $debit - $credit;
            $row = [
                'code' => $r->code, 'name' => $r->name, 'type' => $r->type,
                'debit' => $debit, 'credit' => $credit,
                'saldo_debit'  => $net > 0 ? $net : 0,
                'saldo_kredit' => $net < 0 ? -$net : 0,
            ];
            $totalD += $row['saldo_debit'];
            $totalK += $row['saldo_kredit'];
            $rows[] = $row;
        }

        return ['rows' => $rows, 'total_debit' => $totalD, 'total_kredit' => $totalK, 'balanced' => $totalD === $totalK];
    }

    public function incomeStatement(array $tb): array
    {
        $rev = []; $hpp = []; $opex = [];
        foreach ($tb['rows'] as $r) {
            if ($r['type'] === 'pendapatan') {
                $rev[] = ['code' => $r['code'], 'name' => $r['name'], 'amount' => $r['credit'] - $r['debit']];
            } elseif ($r['type'] === 'beban') {
                $item = ['code' => $r['code'], 'name' => $r['name'], 'amount' => $r['debit'] - $r['credit']];
                if (str_starts_with($r['code'], '5-')) { $hpp[] = $item; } else { $opex[] = $item; }
            }
        }

        $sum = fn (array $a) => array_sum(array_column($a, 'amount'));
        $totalRev = $sum($rev); $totalHpp = $sum($hpp); $totalOpex = $sum($opex);

        return [
            'pendapatan' => $rev, 'hpp' => $hpp, 'beban' => $opex,
            'total_pendapatan' => $totalRev, 'total_hpp' => $totalHpp,
            'laba_kotor' => $totalRev - $totalHpp,
            'total_beban' => $totalOpex,
            'laba_bersih' => $totalRev - $totalHpp - $totalOpex,
        ];
    }

    public function finalTax(string $from, string $to, ?int $branchId, string $wpType): array
    {
        // Asumsi: periode berada dalam satu tahun pajak.
        $yearStart = substr($from, 0, 4) . '-01-01';
        $period = $this->omzet($from, $to, $branchId);
        $before = $from > $yearStart
            ? $this->omzet($yearStart, date('Y-m-d', strtotime("$from -1 day")), $branchId)
            : 0;
        $ytd = $before + $period;

        $threshold = $wpType === 'op' ? self::OP_THRESHOLD : 0;
        $taxable   = max(0, $ytd - $threshold) - max(0, $before - $threshold);

        return [
            'wp_type' => $wpType, 'omzet_periode' => $period, 'omzet_ytd' => $ytd,
            'ambang' => $threshold, 'dpp' => $taxable,
            'tarif' => self::FINAL_RATE, 'pph_final' => (int) round($taxable * self::FINAL_RATE),
        ];
    }

    private function omzet(string $from, string $to, ?int $branchId): int
    {
        return (int) DB::table('journal_lines as l')
            ->join('journal_entries as e', 'e.id', '=', 'l.journal_entry_id')
            ->join('accounts as a', 'a.id', '=', 'l.account_id')
            ->where('a.type', 'pendapatan')
            ->whereBetween('e.entry_date', [$from, $to])
            ->when($branchId, fn ($q) => $q->where('e.branch_id', $branchId))
            ->selectRaw('COALESCE(SUM(l.credit) - SUM(l.debit), 0) AS v')
            ->value('v');
    }
}
