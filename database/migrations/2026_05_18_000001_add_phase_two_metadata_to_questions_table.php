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
        Schema::table('questions', function (Blueprint $table) {
            $table->unsignedSmallInteger('display_order')->nullable()->unique()->after('question_number');
            $table->json('public_options')->nullable()->after('options');
            $table->json('red_flag_options')->nullable()->after('scoring_map');
            $table->json('consistency_pair')->nullable()->after('red_flag_options');
            $table->string('consistency_check')->nullable()->after('consistency_pair');
            $table->text('admin_notes')->nullable()->after('consistency_check');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropUnique(['display_order']);
            $table->dropColumn([
                'display_order',
                'public_options',
                'red_flag_options',
                'consistency_pair',
                'consistency_check',
                'admin_notes',
            ]);
        });
    }
};
