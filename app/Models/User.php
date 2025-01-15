<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'premium_expires_at' => 'datetime',
            'is_premium' => 'boolean',
        ];
    }

    public function teams()
    {
        return $this->belongsToMany(Team::class, 'team_user')
            ->withPivot('role_id')
            ->withTimestamps();
    }

    public function ownedTeams()
    {
        return $this->hasMany(Team::class, 'owner_id');
    }

    public function isPremium()
    {
        return $this->is_premium && $this->premium_expires_at->isFuture();
    }

    public function isTeamLeader(Team $team): bool
    {
        $leaderRole = Role::where('slug', 'leader')->first();

        return $this->teams()
            ->wherePivot('team_id', $team->id)
            ->wherePivot('role_id', $leaderRole->id)
            ->exists();
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function tags()
    {
        return $this->hasMany(Tag::class);
    }

    public function taskOrder()
    {
        return $this->hasOne(UserTaskOrder::class);
    }

    public function getCustomTaskOrder()
    {
        return $this->taskOrder()->where('is_custom_order', true)->first();
    }

    public function dueDateFilters()
    {
        return $this->hasMany(DueDateFilter::class);
    }
}
