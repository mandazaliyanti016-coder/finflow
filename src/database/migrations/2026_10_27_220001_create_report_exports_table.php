<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_exports', function (Blueprint $t) {
            $t->id();
            $t->string('status')->default('pending'); // pending | processing | done | failed
            $t->jsonb('params');
            $t->string('file_path')->nullable();
            $t->text('error')->nullable();
            $t->unsignedInteger('duration_ms')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_exports');
    }
};
