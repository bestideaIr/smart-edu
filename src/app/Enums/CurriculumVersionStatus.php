<?php // app/Enums/CurriculumVersionStatus.php
namespace App\Enums;

enum CurriculumVersionStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Archived = 'archived';
}
