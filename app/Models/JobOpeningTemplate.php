<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobOpeningTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'state',
        'postingTitle',
        'JobTitle',
        'Salary',
        'RequiredSkill',
        'WorkExperience',
        'JobDescription',
        'JobRequirement',
        'JobBenefits',
        'AdditionalNotes',
        'tickets',
        'licences',
        'qualifications',
        'coreDuties',
        'payBand',
    ];

    protected $casts = [
        'RequiredSkill' => 'array',
        'tickets' => 'array',
        'licences' => 'array',
        'qualifications' => 'array',
    ];
}
