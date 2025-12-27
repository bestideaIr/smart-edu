<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('curriculums', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('title', 190);

            // اختیاری ولی مفید: کد پایدار برای ارجاع
            $table->string('code', 50)->nullable()->unique();

            $table->jsonb('meta')->nullable();

            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('curriculums');
    }
};
