<?php

namespace App\Console\Commands;

use App\Domain\Ledger\Services\LedgerService;
use App\Models\Branch;
use App\Models\JournalLine;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class LedgerDemo extends Command
{
    protected $signature = 'ledger:demo';
    protected $description = 'Demo dan uji integritas General Ledger (M1)';

    public function handle(LedgerService $ledger): int
    {
        $branch = Branch::firstOrCreate(['code' => 'CBG-01'], ['name' => 'Cabang Pusat']);

        $this->info('1) Penjualan tunai Rp100.000 (jurnal otomatis)');
        $entry = $ledger->postCashSale($branch->id, 100000, now()->toDateString());
        foreach ($entry->lines as $l) {
            $this->line("   akun {$l->account_id}  debit {$l->debit}  kredit {$l->credit}");
        }

        $this->info('2) Jurnal tidak seimbang ditolak oleh service');
        try {
            $ledger->post(['entry_date' => now()->toDateString()], [
                ['account_id' => 1, 'debit' => 500],
                ['account_id' => 2, 'credit' => 400],
            ]);
        } catch (Throwable $e) {
            $this->line('   DITOLAK: '.$e->getMessage());
        }

        $this->info('3) Jurnal tidak seimbang ditolak oleh DATABASE (bypass service)');
        try {
            DB::transaction(function () use ($branch) {
                $id = DB::table('journal_entries')->insertGetId([
                    'entry_date' => now()->toDateString(), 'branch_id' => $branch->id,
                    'posted_at' => now(), 'created_at' => now(), 'updated_at' => now(),
                ]);
                DB::table('journal_lines')->insert(['journal_entry_id' => $id, 'account_id' => 1, 'debit' => 100, 'credit' => 0]);
            });
            $this->error('   GAGAL: jurnal tidak seimbang lolos!');
        } catch (Throwable $e) {
            $this->line('   DITOLAK DB: '.strtok($e->getMessage(), "\n"));
        }

        $this->info('4) Jurnal immutable (UPDATE ditolak)');
        try {
            JournalLine::where('id', $entry->lines->first()->id)->update(['debit' => 1]);
            $this->error('   GAGAL: jurnal berhasil diubah!');
        } catch (Throwable $e) {
            $this->line('   DITOLAK DB: '.strtok($e->getMessage(), "\n"));
        }

        $this->info('5) Koreksi lewat jurnal pembalik');
        $rev = $ledger->reverse($entry, now()->toDateString());
        $this->line("   jurnal pembalik #{$rev->id} untuk jurnal #{$entry->id}");

        $this->info('6) Neraca saldo: total debit harus = total kredit');
        $t = JournalLine::selectRaw('SUM(debit) d, SUM(credit) c')->first();
        $this->line("   debit {$t->d}  kredit {$t->c}  ".($t->d == $t->c ? 'SEIMBANG' : 'TIDAK SEIMBANG'));

        return self::SUCCESS;
    }
}
