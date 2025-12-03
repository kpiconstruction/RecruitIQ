<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoleCompetencyRequirement extends Model
{
    use HasFactory;

    protected $fillable = [
        'role_id',
        'competency_type_id',
        'is_required',
        'notes',
    ];

    /** @return BelongsTo<JobRole,RoleCompetencyRequirement> */
    public function role(): BelongsTo
    {
        return $this->belongsTo(JobRole::class, 'role_id');
    }

    /** @return BelongsTo<CompetencyType,RoleCompetencyRequirement> */
    public function competencyType(): BelongsTo
    {
        return $this->belongsTo(CompetencyType::class);
    }

    protected static function booted(): void
    {
        static::saving(function (RoleCompetencyRequirement $model) {
            $role = $model->role()->first();
            $type = $model->competencyType()->first();
            if ($role && $type && $role->state !== $type->state) {
                throw new \InvalidArgumentException('Competency Type state must match Role state.');
            }
        });
    }
}
