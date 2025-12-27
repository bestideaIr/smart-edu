<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Curriculum extends Model
{
    protected $table = 'curriculums';

    protected $guarded = [];

    public function versions(): HasMany
    {
        return $this->hasMany(CurriculumVersion::class, 'curriculum_id');
    }

    public function publishedVersion()
    {
        return $this->hasOne(CurriculumVersion::class, 'curriculum_id')
            ->where('status', 'published');
    }

    public function draftVersions(): HasMany
    {
        return $this->versions()->where('status', 'draft');
    }
}
