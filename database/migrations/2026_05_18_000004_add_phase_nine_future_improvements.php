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
        Schema::create('communities', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->default('Squad Limpul');
            $table->string('slug')->unique();
            $table->json('branding')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('code_batches', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('source')->nullable();
            $table->unsignedSmallInteger('quantity');
            $table->timestamp('expires_at')->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamps();

            $table->index(['source', 'created_at']);
        });

        Schema::table('access_codes', function (Blueprint $table) {
            $table->foreignUuid('code_batch_id')->nullable()->after('created_by')->constrained('code_batches')->nullOnDelete();
            $table->foreignUuid('community_id')->nullable()->after('code_batch_id')->constrained('communities')->nullOnDelete();
        });

        Schema::table('participants', function (Blueprint $table) {
            $table->json('question_order_snapshot')->nullable()->after('discord_username');
            $table->string('discord_user_id')->nullable()->after('question_order_snapshot');
            $table->timestamp('discord_verified_at')->nullable()->after('discord_user_id');
            $table->json('discord_metadata')->nullable()->after('discord_verified_at');
        });

        Schema::table('answers', function (Blueprint $table) {
            $table->timestamp('answer_started_at')->nullable()->after('client_saved_at');
            $table->unsignedSmallInteger('client_duration_seconds')->nullable()->after('answer_started_at');
            $table->unsignedSmallInteger('visibility_change_count')->default(0)->after('client_duration_seconds');
            $table->unsignedSmallInteger('offline_sync_count')->default(0)->after('visibility_change_count');
        });

        Schema::create('assessment_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('key')->unique();
            $table->json('value');
            $table->foreignUuid('updated_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('interviews', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('participant_id')->constrained('participants')->cascadeOnDelete();
            $table->foreignUuid('admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->string('interviewer_name');
            $table->timestamp('interview_at')->nullable();
            $table->text('questions_summary');
            $table->text('answers_summary');
            $table->string('outcome');
            $table->timestamps();

            $table->index(['participant_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interviews');
        Schema::dropIfExists('assessment_settings');

        Schema::table('answers', function (Blueprint $table) {
            $table->dropColumn([
                'answer_started_at',
                'client_duration_seconds',
                'visibility_change_count',
                'offline_sync_count',
            ]);
        });

        Schema::table('participants', function (Blueprint $table) {
            $table->dropColumn([
                'question_order_snapshot',
                'discord_user_id',
                'discord_verified_at',
                'discord_metadata',
            ]);
        });

        Schema::table('access_codes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('community_id');
            $table->dropConstrainedForeignId('code_batch_id');
        });

        Schema::dropIfExists('code_batches');
        Schema::dropIfExists('communities');
    }
};
