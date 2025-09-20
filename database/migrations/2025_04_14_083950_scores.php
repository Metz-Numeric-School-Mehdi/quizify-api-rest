<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create("scores", function (Blueprint $table) {
            $table->id();
            $table->foreignId("user_id")->constrained()->cascadeOnDelete();
            $table->foreignId("quiz_id")->constrained("quizzes")->cascadeOnDelete();
            $table->integer("score");
            $table->integer("total_questions")->nullable();
            $table->integer("correct_answers")->nullable();
            $table->integer("time_taken")->nullable(); // in minutes
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("scores");
    }
};
