<?php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Seeder;

class ChartOfAccountsSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            ['1-1000', 'Kas', 'aset'],
            ['1-1100', 'Bank', 'aset'],
            ['1-1200', 'Piutang Usaha', 'aset'],
            ['1-1300', 'Persediaan', 'aset'],
            ['2-1000', 'Utang Usaha', 'liabilitas'],
            ['2-1100', 'Utang Pajak', 'liabilitas'],
            ['3-1000', 'Modal Pemilik', 'ekuitas'],
            ['4-1000', 'Pendapatan Penjualan', 'pendapatan'],
            ['5-1000', 'Harga Pokok Penjualan', 'beban'],
            ['6-1000', 'Beban Gaji', 'beban'],
            ['6-1100', 'Beban Sewa', 'beban'],
            ['6-1200', 'Beban Listrik dan Air', 'beban'],
            ['6-1300', 'Beban Pajak Final', 'beban'],
        ];

        foreach ($accounts as [$code, $name, $type]) {
            Account::updateOrCreate(['code' => $code], [
                'name' => $name,
                'type' => $type,
                'normal_balance' => in_array($type, ['aset', 'beban']) ? 'debit' : 'kredit',
            ]);
        }
    }
}
