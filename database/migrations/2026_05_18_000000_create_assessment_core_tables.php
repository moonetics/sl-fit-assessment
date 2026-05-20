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
        Schema::create('admins', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('email')->unique();
            $table->string('password_hash')->nullable();
            $table->string('role')->default('admin');
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();
        });

        Schema::create('access_codes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code_hash')->unique();
            $table->string('display_code')->nullable();
            $table->string('status')->default('Unused');
            $table->timestamp('expires_at')->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('locked_reason')->nullable();
            $table->timestamps();

            $table->index(['status', 'expires_at']);
        });

        Schema::create('participants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('access_code_id')->unique()->constrained('access_codes')->cascadeOnDelete();
            $table->string('display_name');
            $table->string('discord_username');
            $table->timestamps();
        });

        Schema::create('questions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedSmallInteger('question_number')->unique();
            $table->text('text');
            $table->string('question_type');
            $table->string('category')->nullable();
            $table->string('scoring_direction');
            $table->json('options')->nullable();
            $table->json('scoring_map')->nullable();
            $table->boolean('is_consistency_item')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['question_type', 'is_active']);
        });

        Schema::create('answers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('participant_id')->constrained('participants')->cascadeOnDelete();
            $table->foreignUuid('question_id')->constrained('questions')->cascadeOnDelete();
            $table->string('answer_value');
            $table->integer('score_value')->nullable();
            $table->unsignedInteger('revision')->default(1);
            $table->timestamp('saved_at')->nullable();
            $table->timestamps();

            $table->unique(['participant_id', 'question_id']);
        });

        Schema::create('assessment_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('participant_id')->constrained('participants')->cascadeOnDelete();
            $table->string('session_token_hash');
            $table->string('device_id');
            $table->text('user_agent')->nullable();
            $table->string('ip_hash')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->unsignedInteger('refresh_count')->default(0);
            $table->unsignedInteger('resume_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['participant_id', 'is_active']);
        });

        Schema::create('results', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('participant_id')->unique()->constrained('participants')->cascadeOnDelete();
            $table->unsignedTinyInteger('community_fit_score')->nullable();
            $table->unsignedTinyInteger('competitive_fit_score')->nullable();
            $table->string('risk_level')->nullable();
            $table->string('honesty_status')->nullable();
            $table->string('member_type')->nullable();
            $table->string('final_status')->nullable();
            $table->json('category_scores')->nullable();
            $table->json('red_flags')->nullable();
            $table->json('suspicious_flags')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->foreignUuid('overridden_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->text('override_reason')->nullable();
            $table->timestamps();
        });

        Schema::create('admin_notes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('participant_id')->constrained('participants')->cascadeOnDelete();
            $table->foreignUuid('admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->text('note');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('actor_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->string('action');
            $table->string('entity_type');
            $table->uuid('entity_id')->nullable();
            $table->json('before_data')->nullable();
            $table->json('after_data')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['entity_type', 'entity_id']);
            $table->index(['action', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('admin_notes');
        Schema::dropIfExists('results');
        Schema::dropIfExists('assessment_sessions');
        Schema::dropIfExists('answers');
        Schema::dropIfExists('questions');
        Schema::dropIfExists('participants');
        Schema::dropIfExists('access_codes');
        Schema::dropIfExists('admins');
    }
};
