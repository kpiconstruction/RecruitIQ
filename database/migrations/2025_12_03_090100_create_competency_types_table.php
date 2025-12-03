<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('competency_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('state');
            $table->unsignedSmallInteger('auto_validity_years')->nullable();
            $table->boolean('override_exp_req')->nullable();
            $table->boolean('override_comp_date_req')->nullable();
            $table->boolean('override_issue_req')->nullable();
            $table->boolean('requires_front_back_photos')->default(false);
            $table->string('licence_number_label')->nullable();
            $table->foreignId('assignar_competency_id')->nullable()->constrained('assignar_competencies')->nullOnDelete();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('competency_types');
    }
};
