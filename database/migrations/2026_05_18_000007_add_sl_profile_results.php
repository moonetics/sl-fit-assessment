<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table): void {
            $table->string('profile_axis')->nullable()->after('admin_notes');
            $table->string('profile_pole')->nullable()->after('profile_axis');
        });

        Schema::table('results', function (Blueprint $table): void {
            $table->string('profile_code')->nullable()->after('auto_final_status');
            $table->string('profile_name')->nullable()->after('profile_code');
            $table->json('profile_breakdown')->nullable()->after('profile_name');
            $table->json('risk_reasons')->nullable()->after('suspicious_flags');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('results', function (Blueprint $table): void {
            $table->dropColumn([
                'profile_code',
                'profile_name',
                'profile_breakdown',
                'risk_reasons',
            ]);
        });

        Schema::table('questions', function (Blueprint $table): void {
            $table->dropColumn([
                'profile_axis',
                'profile_pole',
            ]);
        });
    }
};
