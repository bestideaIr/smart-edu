<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Competency extends Model
{
    use HasUuids;

    protected $table = 'competencies';

    protected $guarded = [];

    public function learningUnits(): BelongsToMany
    {
        return $this->belongsToMany(LearningUnit::class, 'learning_unit_competency', 'competency_id', 'learning_unit_id')
            ->using(\App\Models\Pivots\LearningUnitCompetency::class)
            ->withPivot(['role', 'weight']);
    }
}
