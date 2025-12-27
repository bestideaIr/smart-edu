<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use App\Models\Pivots\CurriculumNodeLearningUnit;

class CurriculumNode extends Model
{
    protected $table = 'curriculum_nodes';

    protected $guarded = [];

    protected static function booted(): void
    {
        static::saving(function (self $node) {
            // تغییر فقط در Draft (در سطح اپلیکیشن)
            // اگر نسخه لود نشده باشد، می‌تونی eager-load کنی یا این چک را در Service بگذاری.
            if ($node->relationLoaded('version') && $node->version?->status === 'published') {
                throw new \RuntimeException('Cannot mutate nodes under a published version.');
            }
        });
    }

    public function version(): BelongsTo
    {
        return $this->belongsTo(CurriculumVersion::class, 'curriculum_version_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('order_index');
    }

    public function learningUnits(): BelongsToMany
    {
        return $this->belongsToMany(LearningUnit::class, 'curriculum_node_learning_unit', 'curriculum_node_id', 'learning_unit_id')
            ->using(CurriculumNodeLearningUnit::class)
            ->withPivot(['order_index'])
            ->orderBy('pivot_order_index'); // اگر ستون pivot دقیقاً order_index است، این خط را تغییر بده (پایین توضیح دادم)
    }
}
