<?php

namespace App\Queries\Curriculum;

use App\Models\Curriculum;
use Illuminate\Support\Collection;

class GetCurriculumOverview
{
    /**
     * Lightweight overview for admin / dashboard.
     */
    public function execute(Curriculum $curriculum): Collection
    {
        return $curriculum->versions()
            ->select([
                'id',
                'status',
                'published_at',
                'archived_at',
                'created_at',
            ])
            ->orderByRaw("
                CASE status
                    WHEN 'published' THEN 1
                    WHEN 'draft' THEN 2
                    WHEN 'archived' THEN 3
                END
            ")
            ->orderByDesc('created_at')
            ->get();
    }
}
