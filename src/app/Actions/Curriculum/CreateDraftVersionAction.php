<?php

namespace App\Actions\Curriculum;

use App\Models\Curriculum;
use App\Models\CurriculumVersion;
use Illuminate\Support\Facades\DB;

class CreateDraftVersionAction
{
    /**
     * Create a new draft version for a curriculum.
     * (Simple draft creation — cloning is handled by ClonePublishedToDraftAction.)
     */
    public function execute(Curriculum $curriculum, array $overrides = []): CurriculumVersion
    {
        return DB::transaction(function () use ($curriculum, $overrides) {
            // Optionally lock the curriculum row if you expect concurrent draft creation
            // DB::table('curriculums')->where('id', $curriculum->id)->lockForUpdate()->first();

            return CurriculumVersion::create(array_merge([
                'curriculum_id' => $curriculum->id,
                'status'        => 'draft',
                'published_at'  => null,
                'archived_at'   => null,
                // 'version_label' => ... (اگر دارید)
            ], $overrides));
        });
    }
}
