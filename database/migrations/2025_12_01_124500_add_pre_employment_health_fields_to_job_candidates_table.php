<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_candidates', function (Blueprint $table) {
            $table->string('HealthPreExistingCondition')->nullable();
            $table->text('HealthPreExistingDetails')->nullable();
            $table->string('HealthMedicationTreatment')->nullable();
            $table->text('HealthMedicationDetails')->nullable();
            $table->string('HealthOtherCircumstances')->nullable();
            $table->text('HealthOtherDetails')->nullable();
            $table->boolean('DrugAlcoholConsent')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('job_candidates', function (Blueprint $table) {
            $table->dropColumn([
                'HealthPreExistingCondition',
                'HealthPreExistingDetails',
                'HealthMedicationTreatment',
                'HealthMedicationDetails',
                'HealthOtherCircumstances',
                'HealthOtherDetails',
                'DrugAlcoholConsent',
            ]);
        });
    }
};
