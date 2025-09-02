<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Cashier\Subscription;

class SubscriptionPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'stripe_price_id',
        'stripe_product_id',
        'price',
        'currency',
        'billing_period',
        'description',
        'features',
        'max_quizzes',
        'max_questions_per_quiz',
        'max_participants',
        'analytics_enabled',
        'export_enabled',
        'team_management',
        'priority_support',
        'is_active',
        'sort_order'
    ];

    protected $casts = [
        'features' => 'array',
        'price' => 'decimal:2',
        'analytics_enabled' => 'boolean',
        'export_enabled' => 'boolean',
        'team_management' => 'boolean',
        'priority_support' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'subscription_plan_id');
    }

    public function subscriptions()
    {
        return $this->hasMany(\Laravel\Cashier\Subscription::class, 'stripe_price', 'stripe_price_id');
    }

    public function isFreePlan(): bool
    {
        return $this->price == 0;
    }

    public function isPremiumPlan(): bool
    {
        return $this->slug === 'premium';
    }

    public function isBusinessPlan(): bool
    {
        return $this->slug === 'business';
    }

    public function getFormattedPriceAttribute(): string
    {
        if ($this->isFreePlan()) {
            return 'Gratuit';
        }

        $priceValue = (float) $this->price;
        return number_format($priceValue, 2) . ' ' . strtoupper($this->currency);
    }
}
