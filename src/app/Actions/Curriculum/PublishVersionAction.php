<?php

namespace App\Actions\Curriculum;

use App\Models\CurriculumVersion;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use App\Enums\CurriculumVersionStatus;

class PublishVersionAction
{
    /**
     * Publish a draft version.
     * Guarantees: only one published per curriculum (DB partial unique + app-level check).
     */
    public function execute(CurriculumVersion $version): CurriculumVersion
    {
        return DB::transaction(function () use ($version) {
            // Reload fresh & lock all versions of this curriculum to avoid race conditions
            $version = CurriculumVersion::query()
                ->whereKey($version->getKey())
                ->lockForUpdate()
                ->firstOrFail();

$status = $version->status;

$isDraft = $status instanceof CurriculumVersionStatus
    ? $status === CurriculumVersionStatus::Draft
    : $status === 'draft';

if (!$isDraft) {
    throw new \DomainException('Only draft versions can be published.');
}

            // Lock siblings
            $hasPublished = CurriculumVersion::query()
                ->where('curriculum_id', $version->curriculum_id)
->where('status', CurriculumVersionStatus::Published->value)
                ->lockForUpdate()
                ->exists();

            if ($hasPublished) {
                throw new \DomainException('This curriculum already has a published version.');
            }

            try {
                $version->forceFill([
                    'status'       => 'published',
                    'published_at' => now(),
                    'archived_at'  => null,
                ])->save();
            } catch (QueryException $e) {
                // In case DB partial unique index hits first (race, manual edits, etc.)
                throw new \DomainException('Publish failed due to DB constraint (only-one-published).', 0, $e);
            }

            return $version->fresh();
        });
    }
}
