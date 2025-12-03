<?php

namespace App\Enums;

enum ApplicantCompetencySource: string
{
    case ROLE_REQUIRED = 'ROLE_REQUIRED';
    case ROLE_OPTIONAL = 'ROLE_OPTIONAL';
    case APPLICANT_ADDED = 'APPLICANT_ADDED';
    case MANAGER_ADDED = 'MANAGER_ADDED';
}
