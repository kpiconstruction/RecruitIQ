<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_candidates', function (Blueprint $table) {
            $table->boolean('HealthDeclarationAccepted')->default(false);
            $table->string('HealthSignatureName')->nullable();
            $table->timestamp('HealthSignatureAt')->nullable();
            $table->string('HealthSignatureIP')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('job_candidates', function (Blueprint $table) {
            $table->dropColumn(['HealthDeclarationAccepted', 'HealthSignatureName', 'HealthSignatureAt', 'HealthSignatureIP']);
        });
    }
};
