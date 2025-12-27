<?php

namespace App\Models;

use App\Enums\CurriculumVersionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class CurriculumVersion extends Model
{
    use HasUuids;

    protected $table = 'curriculum_versions';

    protected $guarded = [];

    protected $casts = [
        'published_at' => 'datetime',
        'archived_at'  => 'datetime',
        'status'       => CurriculumVersionStatus::class, // اگر enum را ساختی
    ];

    protected static function booted(): void
    {
        static::updating(function (self $model) {
            // Published immutable (قانون حیاتی)
            $wasPublished = ($model->getOriginal('status') === 'published')
                || ($model->getOriginal('status') instanceof CurriculumVersionStatus
                    && $model->getOriginal('status') === CurriculumVersionStatus::Published);

            if ($wasPublished) {
                throw new \RuntimeException('Published curriculum_version is immutable.');
            }
        });
    }

    public function curriculum(): BelongsTo
    {
        return $this->belongsTo(Curriculum::class, 'curriculum_id');
    }

    public function nodes(): HasMany
    {
        return $this->hasMany(CurriculumNode::class, 'curriculum_version_id');
    }

    public function scopePublished(Builder $q): Builder
    {
        return $q->where('status', 'published');
    }

    public function scopeDraft(Builder $q): Builder
    {
        return $q->where('status', 'draft');
    }
}
