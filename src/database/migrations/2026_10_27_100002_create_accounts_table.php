<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name');
            $table->string('type', 20);
            $table->string('normal_balance', 6);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        DB::statement("ALTER TABLE accounts ADD CONSTRAINT accounts_type_check
            CHECK (type IN ('aset','liabilitas','ekuitas','pendapatan','beban'))");
        DB::statement("ALTER TABLE accounts ADD CONSTRAINT accounts_normal_balance_check
            CHECK (normal_balance IN ('debit','kredit'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
