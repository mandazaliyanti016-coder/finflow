<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journal_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_entry_id')->constrained('journal_entries');
            $table->foreignId('account_id')->constrained('accounts');
            $table->unsignedBigInteger('debit')->default(0);
            $table->unsignedBigInteger('credit')->default(0);

            $table->index(['account_id', 'journal_entry_id']);
        });

        // Satu baris hanya boleh berisi satu sisi (debit ATAU kredit), dan nilainya > 0.
        DB::statement('ALTER TABLE journal_lines ADD CONSTRAINT journal_lines_one_side_check
            CHECK ((debit > 0 AND credit = 0) OR (debit = 0 AND credit > 0))');
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_lines');
    }
};
