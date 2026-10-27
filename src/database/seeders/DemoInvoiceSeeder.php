<?php

namespace Database\Seeders;

use App\Models\Invoice;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoInvoiceSeeder extends Seeder
{
    public function run(): void
    {
        $branchId = DB::table('branches')->value('id');

        Invoice::updateOrCreate(['external_ref' => 'INV-DEMO-001'], [
            'branch_id' => $branchId, 'invoice_number' => 'D-1',
            'amount' => 150000, 'status' => 'pending', 'qris_payload' => 'MOCK-QRIS-001',
        ]);
        Invoice::updateOrCreate(['external_ref' => 'INV-DEMO-002'], [
            'branch_id' => $branchId, 'invoice_number' => 'D-2',
            'amount' => 200000, 'status' => 'pending', 'qris_payload' => 'MOCK-QRIS-002',
        ]);
    }
}
