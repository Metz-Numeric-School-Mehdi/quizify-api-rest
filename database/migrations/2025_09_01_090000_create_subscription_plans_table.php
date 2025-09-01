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
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('stripe_price_id')->nullable()->unique();
            $table->string('stripe_product_id')->nullable();
            $table->decimal('price', 8, 2)->default(0);
            $table->string('currency', 3)->default('eur');
            $table->enum('billing_period', ['month', 'year'])->default('month');
            $table->text('description')->nullable();
            $table->json('features')->nullable();
            $table->integer('max_quizzes')->nullable();
            $table->integer('max_questions_per_quiz')->nullable();
            $table->integer('max_participants')->nullable();
            $table->boolean('analytics_enabled')->default(false);
            $table->boolean('export_enabled')->default(false);
            $table->boolean('team_management')->default(false);
            $table->boolean('priority_support')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};
