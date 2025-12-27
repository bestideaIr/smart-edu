<?php

namespace App\Actions\Curriculum;

use App\Models\Curriculum;
use App\Models\CurriculumNode;
use App\Models\CurriculumVersion;
use Illuminate\Support\Facades\DB;

class ClonePublishedToDraftAction
{
    public function __construct(
        private readonly CreateDraftVersionAction $createDraft,
    ) {}

    /**
     * Clone the published version (structure + node-unit pivots) into a new draft version.
     */
    public function execute(Curriculum $curriculum, array $draftOverrides = []): CurriculumVersion
    {
        return DB::transaction(function () use ($curriculum, $draftOverrides) {
            $published = CurriculumVersion::query()
                ->where('curriculum_id', $curriculum->id)
                ->where('status', 'published')
                ->lockForUpdate()
                ->first();

            if (!$published) {
                throw new \DomainException('No published version exists to clone.');
            }

            $draft = $this->createDraft->execute($curriculum, $draftOverrides);

            // 1) Fetch all nodes from published, in deterministic order (parents first)
            $publishedNodes = CurriculumNode::query()
                ->where('curriculum_version_id', $published->id)
                ->orderBy('depth')       // اگر depth داری
                ->orderBy('order_index') // sibling ordering
                ->get();

            // 2) Clone nodes with parent remapping
            $idMap = []; // oldNodeId => newNodeId

            foreach ($publishedNodes as $oldNode) {
                $new = $oldNode->replicate([
                    'curriculum_version_id',
                    'parent_id',
                    'created_at',
                    'updated_at',
                ]);

                $new->curriculum_version_id = $draft->id;

                // parent remap
                $new->parent_id = $oldNode->parent_id ? ($idMap[$oldNode->parent_id] ?? null) : null;

                $new->save();

                $idMap[$oldNode->id] = $new->id;
            }

            // 3) Clone node ↔ learning_unit pivot rows
            // NOTE: replace column names if your pivot differs.
            $pivotRows = DB::table('curriculum_node_learning_unit')
                ->whereIn('curriculum_node_id', array_keys($idMap))
                ->orderBy('curriculum_node_id')
                ->orderBy('order_index') // pivot ordering
                ->get();

            foreach ($pivotRows as $row) {
                DB::table('curriculum_node_learning_unit')->insert([
                    'curriculum_node_id' => $idMap[$row->curriculum_node_id],
                    'learning_unit_id'   => $row->learning_unit_id,
                    'order_index'        => $row->order_index,
                ]);
            }

            return $draft->fresh();
        });
    }
}
