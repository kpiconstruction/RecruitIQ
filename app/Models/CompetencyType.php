<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompetencyType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'state',
        'auto_validity_years',
        'override_exp_req',
        'override_comp_date_req',
        'override_issue_req',
        'requires_front_back_photos',
        'licence_number_label',
        'assignar_competency_id',
        'active',
    ];

    /** @return HasMany<RoleCompetencyRequirement> */
    public function roleRequirements(): HasMany
    {
        return $this->hasMany(RoleCompetencyRequirement::class);
    }

    /** @return HasMany<ApplicantCompetency> */
    public function applicantCompetencies(): HasMany
    {
        return $this->hasMany(ApplicantCompetency::class);
    }

    public function assignarCompetency()
    {
        return $this->belongsTo(AssignarCompetency::class);
    }
}
