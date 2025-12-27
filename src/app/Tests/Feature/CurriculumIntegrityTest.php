<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Models\Curriculum;
use App\Models\CurriculumVersion;
use App\Models\CurriculumNode;

use App\Actions\Curriculum\CreateDraftVersionAction;
use App\Actions\Curriculum\PublishVersionAction;

class CurriculumIntegrityTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function only_one_published_version_per_curriculum_is_allowed()
    {
        $curriculum = Curriculum::create([]);

        $createDraft = app(CreateDraftVersionAction::class);
        $publish     = app(PublishVersionAction::class);

        $draft1 = $createDraft->execute($curriculum);
        $publish->execute($draft1);

        $draft2 = $createDraft->execute($curriculum);

        $this->expectException(\DomainException::class);
        $publish->execute($draft2);
    }

    /** @test */
    public function published_version_is_immutable()
    {
        $curriculum = Curriculum::create([]);

        $createDraft = app(CreateDraftVersionAction::class);
        $publish     = app(PublishVersionAction::class);

        $draft = $createDraft->execute($curriculum, [
            // اگر ستون title/label داری اینجا بده
            // 'version_label' => 'v1'
        ]);

        $published = $publish->execute($draft);

        // Attempt to mutate published record => should fail (your model boot rule)
        $this->expectException(\RuntimeException::class);

        $published->forceFill([
            // هر فیلدی که در جدول دارید (مثلاً version_label)
            // 'version_label' => 'mutated'
            'archived_at' => now(), // صرفاً مثال
        ])->save();
    }

    /** @test */
    public function cannot_edit_nodes_under_a_published_version()
    {
        $curriculum = Curriculum::create([]);

        // Create a published version directly (or through actions)
        $version = CurriculumVersion::create([
            'curriculum_id' => $curriculum->id,
            'status'        => 'published',
            'published_at'  => now(),
            'archived_at'   => null,
        ]);

        // Create a node under published
        $node = CurriculumNode::create([
            'curriculum_version_id' => $version->id,
            'parent_id'             => null,
            'order_index'           => 1,
            // 'type' => '...' , 'depth' => 0 , 'title' => '...'  (اگر دارید)
        ]);

        // IMPORTANT: your node immutability check triggers only if relationLoaded('version')
        $node->load('version');

        $this->expectException(\RuntimeException::class);

        $node->forceFill([
            'order_index' => 2,
        ])->save();
    }
}
