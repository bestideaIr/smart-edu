<?php

namespace App\Policies;

use App\Models\User;
use App\Models\CurriculumNode;

class CurriculumNodePolicy
{
    /**
     * Edit node (title, type, etc.)
     */
    public function edit(User $user, CurriculumNode $node): bool
    {
        return $user->can('curriculum.edit')
            && (string) $node->version?->status === 'draft';
    }

    /**
     * Reorder siblings.
     */
    public function reorder(User $user, CurriculumNode $node): bool
    {
        return $this->edit($user, $node);
    }

    /**
     * Attach / detach learning units.
     */
    public function manageUnits(User $user, CurriculumNode $node): bool
    {
        return $this->edit($user, $node);
    }

    /**
     * Delete node.
     */
    public function delete(User $user, CurriculumNode $node): bool
    {
        return $this->edit($user, $node);
    }
}
