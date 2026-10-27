<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MvpSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(ChartOfAccountsSeeder::class);

        User::updateOrCreate(['email' => 'admin@finflow.test'], [
            'name' => 'Admin FinFlow',
            'password' => Hash::make('password'),
        ]);

        Branch::firstOrCreate(['code' => 'CBG-01'], ['name' => 'Cabang Pusat']);
        Branch::firstOrCreate(['code' => 'CBG-02'], ['name' => 'Cabang Kedua']);
    }
}
