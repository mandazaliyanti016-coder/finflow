<?php

namespace App\Jobs;

use App\Domain\Reporting\Services\ReportService;
use App\Models\ReportExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;

class GenerateReportPdf implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public function __construct(public int $exportId)
    {
    }

    public function handle(ReportService $svc): void
    {
        $export = ReportExport::findOrFail($this->exportId);
        $export->update(['status' => 'processing']);
        $t0 = microtime(true);

        $p = $export->params;
        $data = $svc->build($p['from'], $p['to'], $p['branch_id'] ?? null, $p['wp']);
        $data += ['from' => $p['from'], 'to' => $p['to']];

        $pdf = Pdf::loadView('reports.pdf', $data)->setPaper('a4')->output();

        $path = "exports/laporan-{$export->id}.pdf";
        Storage::disk('local')->put($path, $pdf);

        $export->update([
            'status' => 'done',
            'file_path' => $path,
            'duration_ms' => (int) round((microtime(true) - $t0) * 1000),
        ]);
    }

    public function failed(\Throwable $e): void
    {
        ReportExport::where('id', $this->exportId)->update(['status' => 'failed', 'error' => $e->getMessage()]);
    }
}
