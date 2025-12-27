<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('curriculum_nodes', function (Blueprint $table) {
            // PK
            $table->uuid('id')->primary();

            // Ownership
            $table->uuid('curriculum_version_id')->index();

            // Tree (Adjacency list)
            $table->uuid('parent_id')->nullable()->index();

            // Node properties
            $table->string('type', 30);     // subject/chapter/topic/subtopic...
            $table->string('title', 190);
            $table->string('slug', 190)->nullable();

            // Ordering within siblings
            $table->integer('order_index')->default(0);

            // Optional helpers (recommended)
            $table->smallInteger('depth')->default(0);
            $table->text('path')->nullable();

            // Extensibility
            $table->jsonb('meta')->nullable();

            $table->timestampsTz();

            // Uniques
            $table->unique(
                ['curriculum_version_id', 'parent_id', 'order_index'],
                'uq_cn_sibling_order'
            );
        });

        // ---- Foreign Keys ----

        // Version ownership
        DB::statement("
            ALTER TABLE curriculum_nodes
            ADD CONSTRAINT fk_cn_curriculum_version
            FOREIGN KEY (curriculum_version_id)
            REFERENCES curriculum_versions(id)
            ON DELETE CASCADE
        ");

        // Self FK (parent)
        DB::statement("
            ALTER TABLE curriculum_nodes
            ADD CONSTRAINT fk_cn_parent
            FOREIGN KEY (parent_id)
            REFERENCES curriculum_nodes(id)
            ON DELETE CASCADE
        ");

        // ---- CHECK constraints ----

        DB::statement("
            ALTER TABLE curriculum_nodes
            ADD CONSTRAINT chk_cn_order_index_non_negative
            CHECK (order_index >= 0)
        ");

        DB::statement("
            ALTER TABLE curriculum_nodes
            ADD CONSTRAINT chk_cn_depth_non_negative
            CHECK (depth >= 0)
        ");

        // Type validity (لیست را اگر لازم داری می‌توانیم دقیق‌تر/گسترده‌تر کنیم)
        DB::statement("
            ALTER TABLE curriculum_nodes
            ADD CONSTRAINT chk_cn_type
            CHECK (type IN ('subject','chapter','topic','subtopic'))
        ");

        // ---- Helpful indexes ----

        // Optional: slug uniqueness within a version when slug is present
        DB::statement("
            CREATE UNIQUE INDEX uq_cn_version_slug_not_null
            ON curriculum_nodes (curriculum_version_id, slug)
            WHERE slug IS NOT NULL
        ");

        // Optional: speed up tree loading per version+parent
        DB::statement("
            CREATE INDEX idx_cn_version_parent
            ON curriculum_nodes (curriculum_version_id, parent_id)
        ");
    }

    public function down(): void
    {
        DB::statement("DROP INDEX IF EXISTS idx_cn_version_parent");
        DB::statement("DROP INDEX IF EXISTS uq_cn_version_slug_not_null");

        Schema::dropIfExists('curriculum_nodes');
    }
};
