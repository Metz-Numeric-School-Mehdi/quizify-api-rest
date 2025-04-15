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
        Schema::create("questions", function (Blueprint $table) {
            $table->id();
            $table->foreignId("quiz_id")->constrained("quizzes")->onDelete("cascade");
            $table->text("content");
            $table->foreignId("question_type_id")->constrained("question_types")->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("questions");
    }
};
