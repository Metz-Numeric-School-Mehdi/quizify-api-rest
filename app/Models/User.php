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

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = ["username", "firstname", "lastname", "email", "password", "role", "profile_photo"];

    /**
     * Get the role associated with this user
     */
    public function role(): BelongsTo
    {
        return $this->BelongsTo(Role::class);
    }

    public function badges()
    {
        return $this->belongsToMany(\App\Models\Badge::class, 'user_badges');
    }

    public function quizzes(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\Quiz::class, 'quiz_user')->withPivot('score');
    }

    public function quizSessions(): BelongsToMany
    {
        return $this->belongsToMany(Quiz::class);
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = ["password", "remember_token"];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            "password" => "hashed",
            "email_verified_at" => "datetime",
        ];
    }
}
