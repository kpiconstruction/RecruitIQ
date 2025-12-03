<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobRole extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'state',
        'active',
    ];

    /** @return HasMany<RoleCompetencyRequirement> */
    public function competencyRequirements(): HasMany
    {
        return $this->hasMany(RoleCompetencyRequirement::class);
    }
}
