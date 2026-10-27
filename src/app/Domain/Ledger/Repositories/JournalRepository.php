<?php

namespace App\Domain\Ledger\Repositories;

use App\Models\Account;
use App\Models\JournalEntry;

class JournalRepository
{
    /** @return array<string,int> kode akun => id akun */
    public function accountIdsByCode(array $codes): array
    {
        return Account::whereIn('code', $codes)->pluck('id', 'code')->all();
    }

    public function create(array $header, array $lines): JournalEntry
    {
        $entry = JournalEntry::create($header);
        $entry->lines()->createMany($lines);

        return $entry->load('lines');
    }
}
