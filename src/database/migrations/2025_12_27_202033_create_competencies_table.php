<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('competencies', function (Blueprint $table) {
            // PK
            $table->uuid('id')->primary();

            // Stable identifiers
            $table->string('code', 50)->unique(); // Global unique
            $table->string('title', 190);

            // Optional description
            $table->text('description')->nullable();

            // Optional grouping
            $table->string('domain', 50)->nullable();

            // Optional scale info
            $table->smallInteger('level_scale')->nullable();

            // Extensibility
            $table->jsonb('meta')->nullable();

            $table->timestampsTz();
        });

        // ---- CHECK constraints ----

        DB::statement("
            ALTER TABLE competencies
            ADD CONSTRAINT chk_comp_level_scale
            CHECK (level_scale IS NULL OR level_scale >= 1)
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('competencies');
    }
};
