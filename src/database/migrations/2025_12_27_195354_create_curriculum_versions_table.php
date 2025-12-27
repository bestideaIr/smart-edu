<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('curriculum_versions', function (Blueprint $table) {
            // Primary Key
            $table->uuid('id')->primary();

            // Relations
            $table->uuid('curriculum_id')->index();

            // Versioning
            $table->string('version', 50);

            // Lifecycle
            $table->string('status', 20)->default('draft');

            // Display
            $table->string('title', 190);
            $table->text('notes')->nullable();

            // Lifecycle timestamps
            $table->timestampTz('published_at')->nullable();
            $table->timestampTz('archived_at')->nullable();

            // Extensibility
            $table->jsonb('meta')->nullable();

            $table->timestampsTz();

            // Unique: curriculum + version
            $table->unique(
                ['curriculum_id', 'version'],
                'uq_curriculum_versions_curriculum_version'
            );

            // FK: curriculum
            $table->foreign('curriculum_id', 'fk_curriculum_versions_curriculum')
                ->references('id')
                ->on('curriculums')
                ->onDelete('cascade');
        });

        // --- CHECK constraints ---

        // Status validity
        DB::statement("
            ALTER TABLE curriculum_versions
            ADD CONSTRAINT chk_cv_status
            CHECK (status IN ('draft', 'published', 'archived'))
        ");

        // Published integrity
        DB::statement("
            ALTER TABLE curriculum_versions
            ADD CONSTRAINT chk_cv_published_at
            CHECK (
                (status = 'published' AND published_at IS NOT NULL)
                OR status <> 'published'
            )
        ");

        // Archived integrity
        DB::statement("
            ALTER TABLE curriculum_versions
            ADD CONSTRAINT chk_cv_archived_at
            CHECK (
                (status = 'archived' AND archived_at IS NOT NULL)
                OR status <> 'archived'
            )
        ");

        // --- Partial Unique Index ---
        // Only one published version per curriculum
        DB::statement("
            CREATE UNIQUE INDEX uq_cv_one_published_per_curriculum
            ON curriculum_versions (curriculum_id)
            WHERE status = 'published'
        ");
    }

    public function down(): void
    {
        DB::statement("
            DROP INDEX IF EXISTS uq_cv_one_published_per_curriculum
        ");

        Schema::dropIfExists('curriculum_versions');
    }
};
