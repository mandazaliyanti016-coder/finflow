<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branch_daily_summary', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches');
            $table->date('summary_date');
            $table->bigInteger('revenue')->default(0);
            $table->bigInteger('cogs')->default(0);
            $table->bigInteger('gross_profit')->default(0);
            $table->bigInteger('opex')->default(0);
            $table->timestamps();

            $table->unique(['branch_id', 'summary_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branch_daily_summary');
    }
};
