<?php

namespace App\Domain\Ledger\Services;

use App\Domain\Ledger\Repositories\JournalRepository;
use App\Models\JournalEntry;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class LedgerService
{
    public const ACC_CASH = '1-1000';
    public const ACC_SALES = '4-1000';

    public function __construct(private JournalRepository $journals)
    {
    }

    /**
     * Posting jurnal berpasangan dalam SATU transaksi database (ACID).
     * $lines: [['account_id'=>1,'debit'=>100], ['account_id'=>2,'credit'=>100]]
     */
    public function post(array $header, array $lines): JournalEntry
    {
        $lines = $this->normalizeLines($lines);
        $this->assertBalanced($lines);

        return DB::transaction(fn () => $this->journals->create($header, $lines));
    }

    /** FR-1.2: penjualan tunai => Debit Kas, Kredit Pendapatan Penjualan. */
    public function postCashSale(int $branchId, int $amount, string $date, ?string $description = null, ?int $userId = null): JournalEntry
    {
        $ids = $this->journals->accountIdsByCode([self::ACC_CASH, self::ACC_SALES]);
        if (count($ids) < 2) {
            throw new InvalidArgumentException('Akun Kas/Pendapatan belum ada. Jalankan seeder COA.');
        }

        return $this->post([
            'entry_date' => $date,
            'branch_id' => $branchId,
            'description' => $description ?? 'Penjualan tunai',
            'source_type' => 'pos_sale',
            'created_by' => $userId,
        ], [
            ['account_id' => $ids[self::ACC_CASH], 'debit' => $amount],
            ['account_id' => $ids[self::ACC_SALES], 'credit' => $amount],
        ]);
    }

    /** FR-1.4: koreksi lewat jurnal pembalik (debit dan kredit ditukar). */
    public function reverse(JournalEntry $entry, string $date, ?int $userId = null): JournalEntry
    {
        $lines = $entry->lines->map(fn ($l) => [
            'account_id' => $l->account_id,
            'debit' => (int) $l->credit,
            'credit' => (int) $l->debit,
        ])->all();

        return $this->post([
            'entry_date' => $date,
            'branch_id' => $entry->branch_id,
            'description' => 'Pembalik jurnal #'.$entry->id,
            'source_type' => 'reversal',
            'source_id' => $entry->id,
            'reversed_entry_id' => $entry->id,
            'created_by' => $userId,
        ], $lines);
    }

    private function normalizeLines(array $lines): array
    {
        return array_map(fn ($l) => [
            'account_id' => (int) $l['account_id'],
            'debit' => (int) ($l['debit'] ?? 0),
            'credit' => (int) ($l['credit'] ?? 0),
        ], $lines);
    }

    private function assertBalanced(array $lines): void
    {
        if (count($lines) < 2) {
            throw new InvalidArgumentException('Jurnal minimal 2 baris.');
        }

        $debit = 0;
        $credit = 0;
        foreach ($lines as $l) {
            if ($l['debit'] < 0 || $l['credit'] < 0 || ($l['debit'] > 0) === ($l['credit'] > 0)) {
                throw new InvalidArgumentException('Setiap baris harus berisi tepat satu sisi (debit atau kredit) > 0.');
            }
            $debit += $l['debit'];
            $credit += $l['credit'];
        }

        if ($debit !== $credit) {
            throw new InvalidArgumentException("Jurnal tidak seimbang: debit {$debit} != kredit {$credit}.");
        }
    }
}
