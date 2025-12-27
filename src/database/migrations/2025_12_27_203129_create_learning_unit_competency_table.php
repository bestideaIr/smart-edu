<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learning_unit_competency', function (Blueprint $table) {
            // Keys
            $table->uuid('learning_unit_id');
            $table->uuid('competency_id');

            // Mapping metadata
            $table->smallInteger('weight')->nullable(); // 1..5 (optional)
            $table->string('role', 20); // introduce|reinforce|assess

            $table->jsonb('meta')->nullable();

            $table->timestampsTz();

            // Unique pair
            $table->unique(
                ['learning_unit_id', 'competency_id'],
                'uq_luc_unit_comp'
            );

            // Indexes for joins
            $table->index('learning_unit_id', 'idx_luc_unit');
            $table->index('competency_id', 'idx_luc_comp');

            // FKs
            $table->foreign('learning_unit_id', 'fk_luc_unit')
                ->references('id')
                ->on('learning_units')
                ->onDelete('cascade');

            // Prevent deleting competency if still referenced
            $table->foreign('competency_id', 'fk_luc_comp')
                ->references('id')
                ->on('competencies')
                ->onDelete('restrict');
        });

        // ---- CHECK constraints ----

        DB::statement("
            ALTER TABLE learning_unit_competency
            ADD CONSTRAINT chk_luc_role
            CHECK (role IN ('introduce','reinforce','assess'))
        ");

        DB::statement("
            ALTER TABLE learning_unit_competency
            ADD CONSTRAINT chk_luc_weight
            CHECK (weight IS NULL OR (weight BETWEEN 1 AND 5))
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('learning_unit_competency');
    }
};
