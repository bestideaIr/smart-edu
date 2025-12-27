<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\Public\CurriculumController as PublicCurriculumController;
use App\Http\Controllers\Api\Admin\CurriculumController as AdminCurriculumController;
use App\Http\Controllers\Api\Admin\CurriculumVersionController;

/*
|--------------------------------------------------------------------------
| Public API (Read-only)
|--------------------------------------------------------------------------
*/
Route::prefix('public')
    ->group(function () {
        Route::get(
            'curriculums/{curriculum}/tree',
            [PublicCurriculumController::class, 'show']
        );
    });

/*
|--------------------------------------------------------------------------
| Admin API
|--------------------------------------------------------------------------
*/
Route::prefix('admin')
    ->middleware(['auth:sanctum']) // یا auth:api / jwt / هرچی داری
    ->group(function () {

        // Curriculum overview (versions list)
        Route::get(
            'curriculums/{curriculum}/versions',
            [AdminCurriculumController::class, 'overview']
        );

        // Draft tree (editable)
        Route::get(
            'curriculum-versions/{version}/tree',
            [CurriculumVersionController::class, 'showDraft']
        );
    });
