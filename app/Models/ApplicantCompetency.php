<?php

namespace App\Models;

use App\Enums\ApplicantCompetencySource;
use App\Enums\ApplicantCompetencyStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicantCompetency extends Model
{
    use HasFactory;

    protected $fillable = [
        'applicant_id',
        'competency_type_id',
        'source',
        'sync_allowed',
        'status',
        'issue_date',
        'completion_date',
        'expiry_date',
        'reference_number',
        'front_image',
        'back_image',
        'notes',
    ];

    protected $casts = [
        'source' => ApplicantCompetencySource::class,
        'status' => ApplicantCompetencyStatus::class,
        'issue_date' => 'date',
        'completion_date' => 'date',
        'expiry_date' => 'date',
        'sync_allowed' => 'boolean',
    ];

    /** @return BelongsTo<Candidates,ApplicantCompetency> */
    public function applicant(): BelongsTo
    {
        return $this->belongsTo(Candidates::class, 'applicant_id');
    }

    /** @return BelongsTo<CompetencyType,ApplicantCompetency> */
    public function competencyType(): BelongsTo
    {
        return $this->belongsTo(CompetencyType::class);
    }

    protected static function booted(): void
    {
        static::saving(function (ApplicantCompetency $model) {
            $type = $model->competencyType()->first();
            $assignar = $type?->assignarCompetency;

            $effectiveIssueRequired = $type?->override_issue_req ?? $assignar?->issue_req ?? false;
            $effectiveCompDateRequired = $type?->override_comp_date_req ?? $assignar?->comp_date_req ?? false;
            $effectiveExpRequired = $type?->override_exp_req ?? $assignar?->exp_req ?? false;

            if ($effectiveExpRequired) {
                if ($type?->auto_validity_years) {
                    if (! $model->issue_date) {
                        $model->status = ApplicantCompetencyStatus::PENDING_VERIFICATION;
                    } else {
                        $model->expiry_date = $model->issue_date->copy()->addYears($type->auto_validity_years);
                    }
                } else {
                    if (! $model->expiry_date || $model->expiry_date->isPast()) {
                        $model->status = ApplicantCompetencyStatus::PENDING_VERIFICATION;
                    }
                }
            } else {
                // ensure expiry_date not set in past; no status effect unless required
            }

            // Reference number requirement based on licence_number_label and effective issue required
            $referenceRequired = ($type?->licence_number_label !== null) && $effectiveIssueRequired;

            // Image requirements
            $imagesRequired = (bool) ($type?->requires_front_back_photos);

            // Custom / unmapped competency type case
            if ($model->competency_type_id === null) {
                $hasImage = ! empty($model->front_image) || ! empty($model->back_image);
                if (! $model->reference_number || ! $hasImage) {
                    $model->status = ApplicantCompetencyStatus::PENDING_VERIFICATION;
                } else {
                    $model->status = ApplicantCompetencyStatus::VALID;
                }

                return;
            }

            // Expired precedence
            if ($effectiveExpRequired && $model->expiry_date && $model->expiry_date->isPast()) {
                $model->status = ApplicantCompetencyStatus::EXPIRED;

                return;
            }

            // Missing evidence precedence
            $missingEvidence = false;
            if ($effectiveIssueRequired && ! $model->issue_date) {
                $missingEvidence = true;
            }
            if ($effectiveCompDateRequired && ! $model->completion_date) {
                $missingEvidence = true;
            }
            if ($referenceRequired && ! $model->reference_number) {
                $missingEvidence = true;
            }
            if ($imagesRequired && (! $model->front_image || ! $model->back_image)) {
                $missingEvidence = true;
            }

            if ($missingEvidence) {
                $model->status = ApplicantCompetencyStatus::PENDING_VERIFICATION;
            } else {
                $model->status = ApplicantCompetencyStatus::VALID;
            }
        });
    }
}
