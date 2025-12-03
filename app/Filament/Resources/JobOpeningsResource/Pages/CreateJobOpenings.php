<?php

namespace App\Filament\Resources\JobOpeningsResource\Pages;

use App\Filament\Resources\JobOpeningsResource;
use App\Models\JobOpeningTemplate;
use Filament\Resources\Pages\CreateRecord;

class CreateJobOpenings extends CreateRecord
{
    protected static string $resource = JobOpeningsResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['Status'] = 'New';

        if (! empty($data['save_as_template']) && ! empty($data['template_name']) && ! empty($data['template_state'])) {
            JobOpeningTemplate::create([
                'name' => $data['template_name'],
                'state' => $data['template_state'],
                'postingTitle' => $data['postingTitle'] ?? null,
                'JobTitle' => $data['JobTitle'] ?? null,
                'Salary' => $data['Salary'] ?? null,
                'RequiredSkill' => $data['RequiredSkill'] ?? [],
                'WorkExperience' => $data['WorkExperience'] ?? null,
                'JobDescription' => $data['JobDescription'] ?? null,
                'JobRequirement' => $data['JobRequirement'] ?? null,
                'JobBenefits' => $data['JobBenefits'] ?? null,
                'AdditionalNotes' => $data['AdditionalNotes'] ?? null,
                'coreDuties' => null,
                'tickets' => [],
                'licences' => [],
                'qualifications' => [],
                'payBand' => null,
            ]);
        }

        return $data;
    }
}
