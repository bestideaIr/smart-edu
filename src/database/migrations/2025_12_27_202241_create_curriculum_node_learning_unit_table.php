<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('curriculum_node_learning_unit', function (Blueprint $table) {
            // Keys
            $table->uuid('curriculum_node_id');
            $table->uuid('learning_unit_id');

            // Ordering within a node
            $table->integer('order_index')->default(0);

            // Extensibility
            $table->jsonb('meta')->nullable();

            $table->timestampsTz();

            // Uniques
            $table->unique(
                ['curriculum_node_id', 'learning_unit_id'],
                'uq_cnlu_node_unit'
            );

            $table->unique(
                ['curriculum_node_id', 'order_index'],
                'uq_cnlu_node_order'
            );

            // Indexes for joins
            $table->index('curriculum_node_id', 'idx_cnlu_node');
            $table->index('learning_unit_id', 'idx_cnlu_unit');

            // FKs
            $table->foreign('curriculum_node_id', 'fk_cnlu_node')
                ->references('id')
                ->on('curriculum_nodes')
                ->onDelete('cascade');

            // RESTRICT delete if referenced (Postgres default is NO ACTION)
            // This prevents deleting a learning_unit that is still attached.
            $table->foreign('learning_unit_id', 'fk_cnlu_unit')
                ->references('id')
                ->on('learning_units')
                ->onDelete('restrict');
        });

        // ---- CHECK constraints ----
        DB::statement("
            ALTER TABLE curriculum_node_learning_unit
            ADD CONSTRAINT chk_cnlu_order_index_non_negative
            CHECK (order_index >= 0)
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('curriculum_node_learning_unit');
    }
};
