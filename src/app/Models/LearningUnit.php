<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Pivots\LearningUnitCompetency;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class LearningUnit extends Model
{
    use HasUuids;

    protected $table = 'learning_units';

    protected $guarded = [];

    protected $casts = [
        'duration_minutes' => 'integer', // اگر نام ستون duration این باشد
    ];

    public function nodes(): BelongsToMany
    {
        return $this->belongsToMany(CurriculumNode::class, 'curriculum_node_learning_unit', 'learning_unit_id', 'curriculum_node_id')
            ->using(\App\Models\Pivots\CurriculumNodeLearningUnit::class)
            ->withPivot(['order_index']);
    }

    public function competencies()
    {
        return $this->belongsToMany(Competency::class, 'learning_unit_competency', 'learning_unit_id', 'competency_id')
            ->using(LearningUnitCompetency::class)
            ->withPivot(['role', 'weight']);
    }
}
