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
            $table->string('subcategory')->nullable()->after('category');
            $table->json('risk_tags')->nullable()->after('red_flag_options');
            $table->string('consistency_pair_id')->nullable()->after('consistency_pair');
            $table->string('research_basis')->nullable()->after('admin_notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table): void {
            $table->dropColumn([
                'subcategory',
                'risk_tags',
                'consistency_pair_id',
                'research_basis',
            ]);
        });
    }
};
