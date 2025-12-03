<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role_competency_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('job_roles')->cascadeOnDelete();
            $table->foreignId('competency_type_id')->constrained('competency_types')->cascadeOnDelete();
            $table->boolean('is_required')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['role_id', 'competency_type_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_competency_requirements');
    }
};
