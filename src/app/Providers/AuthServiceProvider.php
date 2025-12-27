<?php

namespace App\Providers;

use App\Models\CurriculumVersion;
use App\Models\CurriculumNode;
use App\Policies\CurriculumVersionPolicy;
use App\Policies\CurriculumNodePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        CurriculumVersion::class => CurriculumVersionPolicy::class,
        CurriculumNode::class    => CurriculumNodePolicy::class,
    ];
}
