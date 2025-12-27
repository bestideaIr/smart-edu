<?php // app/Enums/CompetencyRole.php
namespace App\Enums;

enum CompetencyRole: string
{
    case Introduce = 'introduce';
    case Reinforce = 'reinforce';
    case Assess = 'assess';
}
