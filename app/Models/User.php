<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Cashier\Billable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes, HasApiTokens, Billable;

    protected $fillable = [
        "username",
        "firstname",
        "lastname",
        "email",
        "password",
        "role_id",
        "profile_photo",
        "avatar",
        "team_id",
        "organization_id",
        "subscription_plan_id",
    ];

    public function role(): BelongsTo
    {
        return $this->BelongsTo(Role::class);
    }

    public function badges()
    {
        return $this->belongsToMany(\App\Models\Badge::class, "user_badges");
    }

    public function quizzesCreated(): HasMany
    {
        return $this->hasMany(\App\Models\Quiz::class);
    }

    public function quizSessions(): BelongsToMany
    {
        return $this->belongsToMany(Quiz::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function questionResponses(): HasMany
    {
        return $this->hasMany(QuestionResponse::class);
    }

    public function scores(): HasMany
    {
        return $this->hasMany(Score::class);
    }

    public function subscriptionPlan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class);
    }

    protected $hidden = ["password", "remember_token"];

    protected function casts(): array
    {
        return [
            "password" => "hashed",
            "email_verified_at" => "datetime",
        ];
    }
}
