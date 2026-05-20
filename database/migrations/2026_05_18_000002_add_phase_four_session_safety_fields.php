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
        Schema::table('answers', function (Blueprint $table) {
            $table->timestamp('client_saved_at')->nullable()->after('saved_at');
            $table->string('sync_status')->default('saved')->after('client_saved_at');
        });

        Schema::table('access_codes', function (Blueprint $table) {
            $table->string('submission_attempt_id')->nullable()->after('locked_reason');
        });

        Schema::table('assessment_sessions', function (Blueprint $table) {
            $table->boolean('is_writer')->default(true)->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assessment_sessions', function (Blueprint $table) {
            $table->dropColumn('is_writer');
        });

        Schema::table('access_codes', function (Blueprint $table) {
            $table->dropColumn('submission_attempt_id');
        });

        Schema::table('answers', function (Blueprint $table) {
            $table->dropColumn(['client_saved_at', 'sync_status']);
        });
    }
};
