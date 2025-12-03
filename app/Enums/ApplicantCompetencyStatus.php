<?php

namespace App\Enums;

enum ApplicantCompetencyStatus: string
{
    case VALID = 'VALID';
    case EXPIRED = 'EXPIRED';
    case PENDING_VERIFICATION = 'PENDING_VERIFICATION';
}
