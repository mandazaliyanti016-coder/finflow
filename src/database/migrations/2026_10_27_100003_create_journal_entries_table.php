<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->date('entry_date');
            $table->foreignId('branch_id')->nullable()->constrained('branches');
            $table->string('description')->nullable();
            $table->string('source_type', 50)->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->foreignId('reversed_entry_id')->nullable()->unique()->constrained('journal_entries');
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamp('posted_at')->useCurrent();
            $table->timestamps();

            $table->index(['entry_date', 'branch_id']);
            $table->index(['source_type', 'source_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_entries');
    }
};
