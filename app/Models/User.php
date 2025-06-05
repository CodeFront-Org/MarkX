<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
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
        'role',
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
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Check if user is a manager
     */
    public function isManager()
    {
        return $this->role === 'rfq_approver';
    }

    /**
     * Check if user is a marketer
     */
    public function isMarketer()
    {
        return $this->role === 'rfq_processor';
    }

    /**
     * Check if user is a client
     */
    public function isClient()
    {
        return $this->role === 'client';
    }

    /**
     * Get the quotes that belong to the user (as a client)
     */
    public function quotes()
    {
        return $this->hasMany(Quote::class, 'user_id');
    }

    /**
     * Get the quotes that were created by the user (as a marketer)
     */
    public function marketedQuotes()
    {
        return $this->hasMany(Quote::class, 'marketer_id');
    }

    // No invoice relationship

    /**
     * Check if user has a specific role
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Check if user is a finance user
     */
    public function isFinance(): bool
    {
        return $this->hasRole('lpo_admin');
    }

    /**
     * Get the user's role
     *
     * @return string
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Get available roles
     *
     * @return array
     */
    public static function getAvailableRoles(): array
    {
        return ['rfq_approver', 'rfq_processor', 'lpo_admin'];
    }
}
