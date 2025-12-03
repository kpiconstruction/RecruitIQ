<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assignar_competencies', function (Blueprint $table) {
            $table->id();
            $table->string('assignar_competency_id')->unique();
            $table->string('name');
            $table->boolean('exp_req')->default(false);
            $table->boolean('comp_date_req')->default(false);
            $table->boolean('issue_req')->default(false);
            $table->string('licence_number_label')->nullable();
            $table->string('state');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignar_competencies');
    }
};
