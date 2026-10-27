<?php

namespace Database\Seeders;

use App\Domain\Branch\Services\BranchService;
use App\Models\Branch;
use Illuminate\Database\Seeder;

class PosDemoSeeder extends Seeder
{
    public function run(): void
    {
        $svc = app(BranchService::class);
        $data = [
            'CBG-01' => [[1500000, 900000, 200000], [1800000, 1100000, 250000], [1200000, 700000, 150000]],
            'CBG-02' => [[900000, 500000, 120000], [1100000, 650000, 140000], [1000000, 600000, 130000]],
        ];

        foreach ($data as $code => $days) {
            $branch = Branch::where('code', $code)->firstOrFail();
            foreach ($days as $i => [$gross, $cogs, $opex]) {
                $date = now()->subDays($i)->toDateString();
                $svc->recordSale($branch->id, $date, $gross, $cogs, 'Penjualan harian (demo)', "DEMO-{$code}-{$date}-S");
                $svc->recordExpense($branch->id, $date, $opex, '6-1100', 'Biaya operasional (demo)', "DEMO-{$code}-{$date}-E");
            }
        }
    }
}
