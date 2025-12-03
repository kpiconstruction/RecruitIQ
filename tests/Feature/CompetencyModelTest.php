<?php

use App\Enums\ApplicantCompetencySource;
use App\Enums\ApplicantCompetencyStatus;
use App\Livewire\CareerApplyJob;
use App\Models\ApplicantCompetency;
use App\Models\AssignarCompetency;
use App\Models\CompetencyType;
use App\Models\JobOpenings;
use App\Models\JobRole;
use App\Models\RoleCompetencyRequirement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Config::set('database.default', 'sqlite');
    Config::set('database.connections.sqlite.database', ':memory:');
});

it('prepopulates role-driven competencies', function () {
    $role = JobRole::create(['name' => 'Traffic Controller', 'state' => 'VIC', 'active' => true]);
    $a1 = AssignarCompetency::create(['assignar_competency_id' => 'A-1', 'name' => 'White Card', 'exp_req' => true, 'comp_date_req' => false, 'issue_req' => true, 'state' => 'VIC', 'active' => true]);
    $t1 = CompetencyType::create(['name' => 'White Card', 'state' => 'VIC', 'override_issue_req' => null, 'override_comp_date_req' => null, 'override_exp_req' => null, 'requires_front_back_photos' => false, 'assignar_competency_id' => $a1->id, 'active' => true]);
    $t2 = CompetencyType::create(['name' => 'Traffic Control', 'state' => 'VIC', 'override_issue_req' => true, 'override_comp_date_req' => true, 'override_exp_req' => true, 'requires_front_back_photos' => true, 'active' => true]);
    $t3 = CompetencyType::create(['name' => 'Spotter', 'state' => 'VIC', 'override_issue_req' => false, 'override_comp_date_req' => false, 'override_exp_req' => false, 'requires_front_back_photos' => false, 'active' => true]);
    RoleCompetencyRequirement::create(['role_id' => $role->id, 'competency_type_id' => $t1->id, 'is_required' => true]);
    RoleCompetencyRequirement::create(['role_id' => $role->id, 'competency_type_id' => $t2->id, 'is_required' => true]);
    RoleCompetencyRequirement::create(['role_id' => $role->id, 'competency_type_id' => $t3->id, 'is_required' => false]);

    $job = JobOpenings::factory()->create(['JobTitle' => 'Traffic Controller', 'postingTitle' => 'Traffic Controller', 'State' => 'VIC', 'JobOpeningSystemID' => 'REF123']);

    Livewire::test(CareerApplyJob::class, ['jobReferenceNumber' => 'REF123'])
        ->set('data', [
            'FirstName' => 'A',
            'LastName' => 'B',
            'mobile' => '0400000000',
            'Email' => 'a@b.com',
            'experience' => '1year',
            'Street' => 'S',
            'City' => 'C',
            'Country' => 'AU',
            'ZipCode' => '3000',
            'State' => 'VIC',
        ])
        ->call('create');

    expect(ApplicantCompetency::where('source', ApplicantCompetencySource::ROLE_REQUIRED)->count())->toBe(2);
    expect(ApplicantCompetency::where('source', ApplicantCompetencySource::ROLE_OPTIONAL)->count())->toBe(1);
});

it('applicant-added custom competency status changes with evidence', function () {
    $role = JobRole::create(['name' => 'Traffic Controller', 'state' => 'VIC', 'active' => true]);
    $job = JobOpenings::factory()->create(['JobTitle' => 'Traffic Controller', 'postingTitle' => 'Traffic Controller', 'State' => 'VIC', 'JobOpeningSystemID' => 'REF124']);

    Livewire::test(CareerApplyJob::class, ['jobReferenceNumber' => 'REF124'])
        ->set('data', [
            'FirstName' => 'A',
            'LastName' => 'B',
            'mobile' => '0400000000',
            'Email' => 'a@b.com',
            'experience' => '1year',
            'Street' => 'S',
            'City' => 'C',
            'Country' => 'AU',
            'ZipCode' => '3000',
            'State' => 'VIC',
            'ApplicantCompetencies' => [
                ['competency_type_id' => null, 'source' => 'APPLICANT_ADDED'],
            ],
        ])
        ->call('create');

    $comp = ApplicantCompetency::where('source', ApplicantCompetencySource::APPLICANT_ADDED)->first();
    expect($comp->status)->toBe(ApplicantCompetencyStatus::PENDING_VERIFICATION);

    $comp->reference_number = 'RN';
    $comp->front_image = 'path/front.png';
    $comp->save();
    expect($comp->status)->toBe(ApplicantCompetencyStatus::VALID);
});

it('evidence-based status calculation', function () {
    $assignar = AssignarCompetency::create(['assignar_competency_id' => 'A-2', 'name' => 'Test', 'exp_req' => true, 'comp_date_req' => true, 'issue_req' => true, 'state' => 'VIC', 'active' => true, 'licence_number_label' => 'Licence']);
    $type = CompetencyType::create(['name' => 'Test', 'state' => 'VIC', 'override_issue_req' => null, 'override_comp_date_req' => null, 'override_exp_req' => null, 'requires_front_back_photos' => true, 'licence_number_label' => 'Licence', 'assignar_competency_id' => $assignar->id, 'active' => true]);

    $comp = ApplicantCompetency::create(['applicant_id' => 1, 'competency_type_id' => $type->id, 'source' => ApplicantCompetencySource::APPLICANT_ADDED, 'sync_allowed' => true]);
    expect($comp->status)->toBe(ApplicantCompetencyStatus::PENDING_VERIFICATION);

    $comp->issue_date = Carbon::parse('2022-01-01');
    $comp->completion_date = Carbon::parse('2022-01-02');
    $comp->reference_number = null;
    $comp->front_image = null;
    $comp->back_image = null;
    $comp->save();
    expect($comp->status)->toBe(ApplicantCompetencyStatus::PENDING_VERIFICATION);

    $comp->reference_number = 'RN';
    $comp->front_image = 'f';
    $comp->back_image = null;
    $comp->save();
    expect($comp->status)->toBe(ApplicantCompetencyStatus::PENDING_VERIFICATION);

    $comp->back_image = 'b';
    $comp->expiry_date = Carbon::now()->subDay();
    $comp->save();
    expect($comp->status)->toBe(ApplicantCompetencyStatus::EXPIRED);

    $comp->expiry_date = Carbon::now()->addYear();
    $comp->save();
    expect($comp->status)->toBe(ApplicantCompetencyStatus::VALID);
});

it('auto-expiry logic applies', function () {
    $assignar = AssignarCompetency::create(['assignar_competency_id' => 'A-3', 'name' => 'Auto', 'exp_req' => true, 'comp_date_req' => false, 'issue_req' => true, 'state' => 'VIC', 'active' => true]);
    $type = CompetencyType::create(['name' => 'Auto', 'state' => 'VIC', 'override_issue_req' => null, 'override_comp_date_req' => null, 'override_exp_req' => null, 'auto_validity_years' => 3, 'assignar_competency_id' => $assignar->id, 'active' => true]);

    $comp = ApplicantCompetency::create(['applicant_id' => 1, 'competency_type_id' => $type->id, 'source' => ApplicantCompetencySource::APPLICANT_ADDED, 'sync_allowed' => true, 'issue_date' => Carbon::parse('2020-01-01')]);
    expect($comp->expiry_date->format('Y-m-d'))->toBe('2023-01-01');
    expect($comp->status)->toBe(ApplicantCompetencyStatus::EXPIRED);
});

it('sync_allowed reflects mapping', function () {
    $typeMapped = CompetencyType::create(['name' => 'Mapped', 'state' => 'VIC', 'override_issue_req' => null, 'override_comp_date_req' => null, 'override_exp_req' => null, 'assignar_competency_id' => AssignarCompetency::create(['assignar_competency_id' => 'M-1', 'name' => 'M', 'exp_req' => false, 'comp_date_req' => false, 'issue_req' => false, 'state' => 'VIC', 'active' => true])->id, 'active' => true]);
    $typeUnmapped = CompetencyType::create(['name' => 'Unmapped', 'state' => 'VIC', 'override_issue_req' => null, 'override_comp_date_req' => null, 'override_exp_req' => null, 'active' => true]);

    $c1 = ApplicantCompetency::create(['applicant_id' => 1, 'competency_type_id' => $typeMapped->id, 'source' => ApplicantCompetencySource::APPLICANT_ADDED, 'sync_allowed' => true]);
    $c2 = ApplicantCompetency::create(['applicant_id' => 1, 'competency_type_id' => $typeUnmapped->id, 'source' => ApplicantCompetencySource::APPLICANT_ADDED, 'sync_allowed' => false]);
    expect($c1->sync_allowed)->toBeTrue();
    expect($c2->sync_allowed)->toBeFalse();
});

it('role/state enforcement blocks mismatch', function () {
    $role = JobRole::create(['name' => 'Role', 'state' => 'VIC', 'active' => true]);
    $type = CompetencyType::create(['name' => 'Type', 'state' => 'NSW', 'active' => true]);
    RoleCompetencyRequirement::create(['role_id' => $role->id, 'competency_type_id' => $type->id, 'is_required' => true]);
})->throws(InvalidArgumentException::class);
