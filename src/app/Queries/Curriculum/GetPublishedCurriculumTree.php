<?php

namespace App\Queries\Curriculum;

use App\Models\Curriculum;
use App\Models\CurriculumNode;
use Illuminate\Support\Collection;

class GetPublishedCurriculumTree
{
    /**
     * Return the full ordered curriculum tree for the published version.
     * Read-only by design.
     */
    public function execute(Curriculum $curriculum): Collection
    {
        $version = $curriculum->publishedVersion()->first();

        if (!$version) {
            return collect();
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
                  ->with([
                      'competencies' => fn ($c) =>
                          $c->orderBy('learning_unit_competency.weight', 'desc'),
                  ]),
        ];
    }
}
