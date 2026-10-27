<?php

namespace App\Domain\Branch\Services;

use App\Domain\Ledger\Repositories\JournalRepository;
use App\Domain\Ledger\Services\LedgerService;
use App\Models\PosTransaction;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class BranchService
{
    public const ACC_CASH = '1-1000';
    public const ACC_INVENTORY = '1-1300';
    public const ACC_SALES = '4-1000';
    public const ACC_COGS = '5-1000';

    public const EXPENSE_ACCOUNTS = ['6-1000', '6-1100', '6-1200'];

    public function __construct(
        private LedgerService $ledger,
        private JournalRepository $journals,
    ) {
    }

    /**
     * Penjualan: Debit Kas, Kredit Pendapatan; bila ada HPP: Debit HPP, Kredit Persediaan.
     * Idempoten per (cabang, external_ref).
     */
    public function recordSale(int $branchId, string $date, int $gross, int $cogs = 0, ?string $description = null, ?string $ref = null, ?int $userId = null): PosTransaction
    {
        if ($gross <= 0) {
            throw new InvalidArgumentException('Omzet harus lebih dari 0.');
        }
        if ($cogs < 0 || $cogs > $gross * 10) {
            throw new InvalidArgumentException('HPP tidak valid.');
        }

        return DB::transaction(function () use ($branchId, $date, $gross, $cogs, $description, $ref, $userId) {
            if ($existing = $this->findByRef($branchId, $ref)) {
                return $existing;
            }

            $ids = $this->accountIds([self::ACC_CASH, self::ACC_SALES, self::ACC_COGS, self::ACC_INVENTORY]);

            $lines = [
                ['account_id' => $ids[self::ACC_CASH], 'debit' => $gross],
                ['account_id' => $ids[self::ACC_SALES], 'credit' => $gross],
            ];
            if ($cogs > 0) {
                $lines[] = ['account_id' => $ids[self::ACC_COGS], 'debit' => $cogs];
                $lines[] = ['account_id' => $ids[self::ACC_INVENTORY], 'credit' => $cogs];
            }

            $entry = $this->ledger->post([
                'entry_date' => $date,
                'branch_id' => $branchId,
                'description' => $description ?? 'Penjualan cabang',
                'source_type' => 'pos_sale',
                'created_by' => $userId,
            ], $lines);

            $trx = PosTransaction::create([
                'branch_id' => $branchId,
                'trx_date' => $date,
                'type' => 'sale',
                'gross_amount' => $gross,
                'cogs_amount' => $cogs,
                'description' => $description,
                'external_ref' => $ref,
                'journal_entry_id' => $entry->id,
            ]);

            $this->bumpSummary($branchId, $date, revenue: $gross, cogs: $cogs, opex: 0);

            return $trx;
        });
    }

    /** Biaya operasional: Debit Beban, Kredit Kas. */
    public function recordExpense(int $branchId, string $date, int $amount, string $accountCode, ?string $description = null, ?string $ref = null, ?int $userId = null): PosTransaction
    {
        if ($amount <= 0) {
            throw new InvalidArgumentException('Nominal biaya harus lebih dari 0.');
        }
        if (! in_array($accountCode, self::EXPENSE_ACCOUNTS, true)) {
            throw new InvalidArgumentException('Akun biaya tidak valid.');
        }

        return DB::transaction(function () use ($branchId, $date, $amount, $accountCode, $description, $ref, $userId) {
            if ($existing = $this->findByRef($branchId, $ref)) {
                return $existing;
            }

            $ids = $this->accountIds([self::ACC_CASH, $accountCode]);

            $entry = $this->ledger->post([
                'entry_date' => $date,
                'branch_id' => $branchId,
                'description' => $description ?? 'Biaya operasional cabang',
                'source_type' => 'pos_expense',
                'created_by' => $userId,
            ], [
                ['account_id' => $ids[$accountCode], 'debit' => $amount],
                ['account_id' => $ids[self::ACC_CASH], 'credit' => $amount],
            ]);

            $trx = PosTransaction::create([
                'branch_id' => $branchId,
                'trx_date' => $date,
                'type' => 'expense',
                'expense_amount' => $amount,
                'expense_account_code' => $accountCode,
                'description' => $description,
                'external_ref' => $ref,
                'journal_entry_id' => $entry->id,
            ]);

            $this->bumpSummary($branchId, $date, revenue: 0, cogs: 0, opex: $amount);

            return $trx;
        });
    }

    private function findByRef(int $branchId, ?string $ref): ?PosTransaction
    {
        if ($ref === null || $ref === '') {
            return null;
        }

        return PosTransaction::where('branch_id', $branchId)->where('external_ref', $ref)->first();
    }

    private function accountIds(array $codes): array
    {
        $ids = $this->journals->accountIdsByCode($codes);
        foreach ($codes as $c) {
            if (! isset($ids[$c])) {
                throw new InvalidArgumentException("Akun {$c} belum ada. Jalankan seeder COA.");
            }
        }

        return $ids;
    }

    /** Ringkasan harian di-update atomik (UPSERT) di transaksi yang sama dengan jurnal. */
    private function bumpSummary(int $branchId, string $date, int $revenue, int $cogs, int $opex): void
    {
        DB::statement('
            INSERT INTO branch_daily_summary (branch_id, summary_date, revenue, cogs, gross_profit, opex, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, now(), now())
            ON CONFLICT (branch_id, summary_date) DO UPDATE SET
                revenue = branch_daily_summary.revenue + EXCLUDED.revenue,
                cogs = branch_daily_summary.cogs + EXCLUDED.cogs,
                gross_profit = (branch_daily_summary.revenue + EXCLUDED.revenue) - (branch_daily_summary.cogs + EXCLUDED.cogs),
                opex = branch_daily_summary.opex + EXCLUDED.opex,
                updated_at = now()
        ', [$branchId, $date, $revenue, $cogs, $revenue - $cogs, $opex]);
    }
}
