<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_candidates', function (Blueprint $table) {
            $table->string('CriminalHistory')->nullable();
            $table->text('CriminalDetails')->nullable();
            $table->boolean('DeclarationAccepted')->default(false);
            $table->string('SignatureName')->nullable();
            $table->timestamp('SignatureAt')->nullable();
            $table->string('SignatureIP')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('job_candidates', function (Blueprint $table) {
            $table->dropColumn(['CriminalHistory', 'CriminalDetails', 'DeclarationAccepted', 'SignatureName', 'SignatureAt', 'SignatureIP']);
        });
    }
};
