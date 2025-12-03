<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applicant_competencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('applicant_id')->constrained('candidates')->cascadeOnDelete();
            $table->foreignId('competency_type_id')->nullable()->constrained('competency_types')->nullOnDelete();
            $table->string('source');
            $table->boolean('sync_allowed')->default(false);
            $table->string('status')->default('PENDING_VERIFICATION');
            $table->date('issue_date')->nullable();
            $table->date('completion_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('reference_number')->nullable();
            $table->string('front_image')->nullable();
            $table->string('back_image')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applicant_competencies');
    }
};
