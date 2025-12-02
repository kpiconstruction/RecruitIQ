<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_opening_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('state');
            $table->string('postingTitle')->nullable();
            $table->string('JobTitle')->nullable();
            $table->string('Salary')->nullable();
            $table->json('RequiredSkill')->nullable();
            $table->string('WorkExperience')->nullable();
            $table->longText('JobDescription')->nullable();
            $table->longText('JobRequirement')->nullable();
            $table->longText('JobBenefits')->nullable();
            $table->longText('AdditionalNotes')->nullable();
            $table->json('tickets')->nullable();
            $table->json('licences')->nullable();
            $table->json('qualifications')->nullable();
            $table->longText('coreDuties')->nullable();
            $table->string('payBand')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_opening_templates');
    }
};

