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
        Schema::create("quizzes", function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->string("title", 255)->notNullable();
            $table->string("slug", 255)->notNullable()->unique();
            $table->text("description")->nullable();
            $table->boolean("is_public")->default(false)->notNullable();
            $table->foreignId("level_id")->constrained("quiz_levels")->cascadeOnDelete();
            $table->enum("status", ["draft", "published", "archived"])->default("draft");
            $table->foreignId("user_id")->constrained()->cascadeOnDelete()->notNullable();
            $table->integer("duration")->nullable();
            $table->integer("max_attempts")->nullable();
            $table->integer("pass_score")->nullable();
            $table->string("thumbnail", 255)->nullable();
            $table->timestamps();
            $table->softDeletes()->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("quizzes");
    }
};
