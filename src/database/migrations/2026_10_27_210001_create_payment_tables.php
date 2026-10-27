<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $t) {
            $t->id();
            $t->foreignId('branch_id')->constrained();
            $t->string('invoice_number')->unique();
            $t->string('external_ref')->unique();
            $t->unsignedBigInteger('amount');
            $t->string('status')->default('pending');
            $t->text('qris_payload')->nullable();
            $t->timestamp('expires_at')->nullable();
            $t->timestamp('paid_at')->nullable();
            $t->timestamps();
        });

        Schema::create('payments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('invoice_id')->unique()->constrained();
            $t->unsignedBigInteger('amount');
            $t->timestamp('paid_at');
            $t->unsignedBigInteger('journal_entry_id')->nullable();
            $t->timestamps();
        });

        Schema::create('webhook_events', function (Blueprint $t) {
            $t->id();
            $t->string('event_id')->unique();
            $t->jsonb('payload');
            $t->timestamps();
        });

        DB::statement("CREATE UNIQUE INDEX journal_entries_qris_unique
            ON journal_entries (source_id) WHERE source_type = 'qris_payment'");
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS journal_entries_qris_unique');
        Schema::dropIfExists('webhook_events');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('invoices');
    }
};
