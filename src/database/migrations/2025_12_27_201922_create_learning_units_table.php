<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learning_units', function (Blueprint $table) {
            // PK
            $table->uuid('id')->primary();

            // Stable identifiers
            $table->string('code', 50)->unique();   // Global unique
            $table->string('title', 190);

            // Optional descriptive fields
            $table->text('description')->nullable();

            // Content type
            $table->string('content_type', 30);

            // Optional metrics
            $table->integer('estimated_minutes')->nullable();
            $table->smallInteger('difficulty_level')->nullable();

            // Extensibility
            $table->jsonb('meta')->nullable();

            $table->timestampsTz();
        });

        // ---- CHECK constraints ----

        DB::statement("
            ALTER TABLE learning_units
            ADD CONSTRAINT chk_lu_content_type
            CHECK (content_type IN ('lesson','exercise','quiz','project','assessment'))
        ");

        DB::statement("
            ALTER TABLE learning_units
            ADD CONSTRAINT chk_lu_estimated_minutes
            CHECK (estimated_minutes IS NULL OR estimated_minutes >= 1)
        ");

        DB::statement("
            ALTER TABLE learning_units
            ADD CONSTRAINT chk_lu_difficulty_level
            CHECK (difficulty_level IS NULL OR (difficulty_level BETWEEN 1 AND 5))
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('learning_units');
    }
};
