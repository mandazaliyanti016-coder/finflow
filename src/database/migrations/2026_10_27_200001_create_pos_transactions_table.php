<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches');
            $table->date('trx_date');
            $table->string('type', 10);
            $table->unsignedBigInteger('gross_amount')->default(0);
            $table->unsignedBigInteger('cogs_amount')->default(0);
            $table->unsignedBigInteger('expense_amount')->default(0);
            $table->string('expense_account_code', 20)->nullable();
            $table->string('description')->nullable();
            $table->string('external_ref', 100)->nullable();
            $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries');
            $table->timestamps();

            $table->index(['branch_id', 'trx_date']);
            $table->unique(['branch_id', 'external_ref']);
        });

        DB::statement("ALTER TABLE pos_transactions ADD CONSTRAINT pos_type_check CHECK (type IN ('sale','expense'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_transactions');
    }
};
