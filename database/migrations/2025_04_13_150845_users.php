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
        Schema::create("users", function (Blueprint $table) {
            $table->id();
            $table->string("username")->unique();
            $table->string("email")->unique();
            $table->string("password");
            $table->string("firstname")->nullable();
            $table->string("lastname")->nullable();
            $table->foreignId("role_id")->nullable()->constrained()->cascadeOnDelete();
            $table->string("profile_photo")->nullable();
            $table->integer("ranking")->unique()->nullable();
            $table->foreignId("team_id")->nullable()->constrained()->cascadeOnDelete();
            $table->timestamps(0);
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("users");
    }
};
