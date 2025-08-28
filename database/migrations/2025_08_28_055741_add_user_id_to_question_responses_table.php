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
        Schema::table('question_responses', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('quiz_id')->constrained()->cascadeOnDelete();
            $table->index(['user_id', 'quiz_id']); // Index pour optimiser les requêtes
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('question_responses', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropIndex(['user_id', 'quiz_id']);
            $table->dropColumn('user_id');
        });
    }
};
