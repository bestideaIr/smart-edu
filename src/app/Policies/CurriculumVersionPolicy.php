<?php

namespace App\Policies;

use App\Models\User;
use App\Models\CurriculumVersion;

class CurriculumVersionPolicy
{
    /**
     * View any version (draft / published).
     * معمولاً admin یا editor
     */
    public function view(User $user, CurriculumVersion $version): bool
    {
        return $user->can('curriculum.view');
    }

    /**
     * Edit a version (ONLY draft).
     */
    public function edit(User $user, CurriculumVersion $version): bool
    {
        return $user->can('curriculum.edit')
            && (string) $version->status === 'draft';
    }

    /**
     * Publish a version.
     */
    public function publish(User $user, CurriculumVersion $version): bool
    {
        return $user->can('curriculum.publish')
            && (string) $version->status === 'draft';
    }

    /**
     * Clone published → draft.
     */
    public function clone(User $user, CurriculumVersion $version): bool
    {
        return $user->can('curriculum.clone')
            && (string) $version->status === 'published';
    }

    /**
     * Archive a version (اختیاری، اگر دارید).
     */
    public function archive(User $user, CurriculumVersion $version): bool
    {
        return $user->can('curriculum.archive')
            && (string) $version->status === 'published';
    }
}
