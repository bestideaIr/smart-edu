<?php

namespace App\Models\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;

class CurriculumNodeLearningUnit extends Pivot
{
    protected $table = 'curriculum_node_learning_unit';

    public $incrementing = false;
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'order_index' => 'integer',
    ];
}
