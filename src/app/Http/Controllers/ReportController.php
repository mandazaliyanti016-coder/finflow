<?php

namespace App\Http\Controllers;

use App\Domain\Reporting\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReportController extends Controller
{
    public function index(Request $request, ReportService $svc)
    {
        [$from, $to, $branchId, $wp] = $this->params($request);

        $t0 = microtime(true);
        $r  = $svc->build($from, $to, $branchId, $wp);
        $ms = (int) round((microtime(true) - $t0) * 1000);

        $branches = DB::table('branches')->get();
        return view('reports.index', $r + compact('from', 'to', 'branchId', 'wp', 'branches', 'ms'));
    }

    public function excel(Request $request, ReportService $svc)
    {
        [$from, $to, $branchId, $wp] = $this->params($request);
        $r = $svc->build($from, $to, $branchId, $wp);

        $ss = new Spreadsheet();

        $ws = $ss->getActiveSheet()->setTitle('Neraca Saldo');
        $ws->fromArray([["Neraca Saldo $from s.d. $to"], [], ['Kode', 'Akun', 'Saldo Debit', 'Saldo Kredit']], null, 'A1');
        $row = 4;
        foreach ($r['tb']['rows'] as $x) {
            $ws->fromArray([$x['code'], $x['name'], $x['saldo_debit'], $x['saldo_kredit']], null, "A$row");
            $row++;
        }
        $ws->fromArray(['', 'TOTAL', $r['tb']['total_debit'], $r['tb']['total_kredit']], null, "A$row");

        $is = $r['is'];
        $ws2 = $ss->createSheet()->setTitle('Laba Rugi');
        $ws2->fromArray([["Laporan Laba Rugi $from s.d. $to"], [], ['Kode', 'Akun', 'Jumlah (Rp)']], null, 'A1');
        $row = 4;
        $block = function (string $title, array $items, $total, string $label) use ($ws2, &$row) {
            $ws2->setCellValue("A$row", $title); $row++;
            foreach ($items as $i) { $ws2->fromArray([$i['code'], $i['name'], $i['amount']], null, "A$row"); $row++; }
            $ws2->fromArray(['', $label, $total], null, "A$row"); $row += 2;
        };
        $block('PENDAPATAN', $is['pendapatan'], $is['total_pendapatan'], 'Total Pendapatan');
        $block('HARGA POKOK PENJUALAN', $is['hpp'], $is['total_hpp'], 'Total HPP');
        $ws2->fromArray(['', 'LABA KOTOR', $is['laba_kotor']], null, "A$row"); $row += 2;
        $block('BEBAN OPERASIONAL', $is['beban'], $is['total_beban'], 'Total Beban');
        $ws2->fromArray(['', 'LABA BERSIH', $is['laba_bersih']], null, "A$row");

        $t = $r['tax'];
        $ws3 = $ss->createSheet()->setTitle('PPh Final');
        $ws3->fromArray([
            ["Estimasi PPh Final UMKM $from s.d. $to"], [],
            ['Jenis WP', $t['wp_type'] === 'op' ? 'Orang Pribadi' : 'Badan'],
            ['Omzet periode', $t['omzet_periode']],
            ['Omzet kumulatif tahun berjalan', $t['omzet_ytd']],
            ['Ambang tidak kena pajak', $t['ambang']],
            ['Dasar pengenaan pajak', $t['dpp']],
            ['Tarif', ($t['tarif'] * 100) . '%'],
            ['PPh Final terutang (estimasi)', $t['pph_final']],
        ], null, 'A1');

        foreach ($ss->getAllSheets() as $sheet) {
            foreach (['A', 'B', 'C', 'D'] as $col) { $sheet->getColumnDimension($col)->setAutoSize(true); }
        }
        $ss->setActiveSheetIndex(0);

        $writer = new Xlsx($ss);
        return response()->streamDownload(
            fn () => $writer->save('php://output'),
            "laporan-keuangan-$from-$to.xlsx",
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        );
    }

    private function params(Request $request): array
    {
        $from = $request->query('from', now()->startOfMonth()->toDateString());
        $to   = $request->query('to', now()->toDateString());
        $branchId = $request->query('branch_id') ? (int) $request->query('branch_id') : null;
        $wp = $request->query('wp', 'op') === 'badan' ? 'badan' : 'op';
        return [$from, $to, $branchId, $wp];
    }
}
