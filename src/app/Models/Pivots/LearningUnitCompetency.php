<?php

namespace App\Models\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;

class LearningUnitCompetency extends Pivot
{
    protected $table = 'learning_unit_competency';

    public $incrementing = false;
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'weight' => 'integer',
    ];
}
