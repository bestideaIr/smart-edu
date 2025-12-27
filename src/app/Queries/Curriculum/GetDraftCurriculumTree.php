<?php

namespace App\Queries\Curriculum;

use App\Models\CurriculumVersion;
use App\Models\CurriculumNode;
use Illuminate\Support\Collection;

class GetDraftCurriculumTree
{
    /**
     * Return ordered tree for a specific draft version.
     */
    public function execute(CurriculumVersion $version): Collection
    {
        if ((string) $version->status !== 'draft') {
            throw new \DomainException('Only draft versions can be queried as editable trees.');
        }

        return CurriculumNode::query()
            ->where('curriculum_version_id', $version->id)
            ->whereNull('parent_id')
            ->with($this->nodeRelations())
            ->orderBy('order_index')
            ->get();
    }

    protected function nodeRelations(): array
    {
        return [
            'children' => fn ($q) =>
                $q->orderBy('order_index')
                  ->with($this->nodeRelations()),

            'learningUnits' => fn ($q) =>
                $q->orderBy('curriculum_node_learning_unit.order_index')
                  ->with('competencies'),
        ];
    }
}
