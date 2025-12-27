<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\Curriculum;
use App\Models\CurriculumNode;
use App\Models\LearningUnit;
use App\Models\Competency;

use App\Actions\Curriculum\CreateDraftVersionAction;
use App\Actions\Curriculum\PublishVersionAction;
use App\Actions\Curriculum\ClonePublishedToDraftAction;

class DemoCurriculumSeeder extends Seeder
{
    public function run(): void
    {
        // 1) Curriculum (اگر می‌خواهی چندبار اجرا شود، بهتر است title را هم یکتا کنی یا updateOrCreate بزنی)
        $curriculum = Curriculum::updateOrCreate(
            ['title' => 'Demo Curriculum'],
            []
        );

        // 2) Draft v1 (UNIQUE (curriculum_id, version) داریم :contentReference[oaicite:2]{index=2})
        $draftV1 = $curriculum->versions()->where('version', 'v1')->first();

        if (!$draftV1) {
            $draftV1 = app(CreateDraftVersionAction::class)->execute($curriculum, [
                'version' => 'v1',
                'title'   => 'Demo Curriculum v1',
                'notes'   => 'Initial draft version',
            ]);
        }

        // اگر v1 قبلاً published شده، دیگر نباید رویش node/pivot بسازیم (Rule Matrix) :contentReference[oaicite:3]{index=3} :contentReference[oaicite:4]{index=4}
$status = $draftV1->status;
$statusValue = is_object($status) && property_exists($status, 'value') ? $status->value : (string) $status;

if ($statusValue !== 'draft') {
            // اگر قبلاً run شده، فقط مطمئن شو draft v2 وجود دارد (یا بساز) و تمام
            $draftV2 = $curriculum->versions()->where('version', 'v2')->first();
            if (!$draftV2) {
                app(ClonePublishedToDraftAction::class)->execute($curriculum, [
                    'version' => 'v2',
                    'title'   => 'Demo Curriculum v2 (Draft)',
                    'notes'   => 'Cloned from published v1',
                ]);
            }
            return;
        }

        // 3) Tree nodes (UNIQUE (curriculum_version_id, parent_id, order_index) داریم :contentReference[oaicite:5]{index=5})
        // برای idempotency: با (version_id, parent_id, order_index) پیدا کن و update کن.
        $subject = CurriculumNode::updateOrCreate(
            [
                'curriculum_version_id' => $draftV1->id,
                'parent_id' => null,
                'order_index' => 0,
            ],
            [
                'type'  => 'subject',
                'title' => 'Mathematics',
                'slug'  => 'mathematics',
                'depth' => 0,
            ]
        );

        $chapter = CurriculumNode::updateOrCreate(
            [
                'curriculum_version_id' => $draftV1->id,
                'parent_id' => $subject->id,
                'order_index' => 0,
            ],
            [
                'type'  => 'chapter',
                'title' => 'Algebra Basics',
                'slug'  => 'algebra-basics',
                'depth' => 1,
            ]
        );

        $topic = CurriculumNode::updateOrCreate(
            [
                'curriculum_version_id' => $draftV1->id,
                'parent_id' => $chapter->id,
                'order_index' => 0,
            ],
            [
                'type'  => 'topic',
                'title' => 'Linear Equations',
                'slug'  => 'linear-equations',
                'depth' => 2,
            ]
        );

        // 4) Learning Units (code global unique) :contentReference[oaicite:6]{index=6}
        $unitLesson = LearningUnit::updateOrCreate(
            ['code' => 'ALG-LIN-LESSON'],
            [
                'title'             => 'Introduction to Linear Equations',
                'description'       => 'Concepts and examples of linear equations.',
                'content_type'      => 'lesson',
                'estimated_minutes' => 30,
                'difficulty_level'  => 2,
            ]
        );

        $unitExercise = LearningUnit::updateOrCreate(
            ['code' => 'ALG-LIN-EXERCISE'],
            [
                'title'             => 'Linear Equations Practice',
                'description'       => 'Practice problems for linear equations.',
                'content_type'      => 'exercise',
                'estimated_minutes' => 20,
                'difficulty_level'  => 3,
            ]
        );

        // 5) Competencies (code global unique) :contentReference[oaicite:7]{index=7}
        $compSolve = Competency::updateOrCreate(
            ['code' => 'MATH-LIN-SOLVE'],
            [
                'title'       => 'Solve linear equations',
                'description' => 'Ability to solve basic linear equations.',
                'domain'      => 'math',
                'level_scale' => 1,
            ]
        );

        $compModel = Competency::updateOrCreate(
            ['code' => 'MATH-LIN-MODEL'],
            [
                'title'       => 'Model problems with equations',
                'description' => 'Translate word problems into equations.',
                'domain'      => 'math',
                'level_scale' => 2,
            ]
        );

        // 6) Attach units to topic (pivot unique ها) :contentReference[oaicite:8]{index=8}
        // sync => هم idempotent است هم order_index را update می‌کند
        $topic->learningUnits()->sync([
            $unitLesson->id   => ['order_index' => 0],
            $unitExercise->id => ['order_index' => 1],
        ]);

        // 7) Attach competencies to units (pivot unique + role constraint) :contentReference[oaicite:9]{index=9}
        $unitLesson->competencies()->sync([
            $compSolve->id => ['role' => 'introduce', 'weight' => 3],
        ]);

        $unitExercise->competencies()->sync([
            $compSolve->id => ['role' => 'reinforce', 'weight' => 4],
            $compModel->id => ['role' => 'assess', 'weight' => 2],
        ]);

        // 8) Publish v1 (اگر قبلاً published باشد، Action خودش جلویش را می‌گیرد)
        app(PublishVersionAction::class)->execute($draftV1);

        // 9) Clone Published → Draft v2 (اگر قبلاً ساخته شده، تکراری نسازد)
        $draftV2 = $curriculum->versions()->where('version', 'v2')->first();
        if (!$draftV2) {
            app(ClonePublishedToDraftAction::class)->execute($curriculum, [
                'version' => 'v2',
                'title'   => 'Demo Curriculum v2 (Draft)',
                'notes'   => 'Cloned from published v1',
            ]);
        }
    }
}
