<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

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
        'roles',
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
        'roles' => 'array',
    ];

    /**
     * Get array of all assigned roles for the user
     */
    public function getRolesArray(): array
    {
        if (!empty($this->roles) && is_array($this->roles)) {
            return array_values(array_unique($this->roles));
        }
        return !empty($this->role) ? [$this->role] : [];
    }

    /**
     * Check if user is a manager
     */
    public function isManager()
    {
        return $this->hasRole('rfq_approver');
    }

    /**
     * Check if user is an RFQ Approver (alias for isManager for consistency)
     */
    public function isRfqApprover()
    {
        return $this->hasRole('rfq_approver');
    }

    /**
     * Check if user is a marketer
     */
    public function isMarketer()
    {
        return $this->hasRole('rfq_processor');
    }

    /**
     * Check if user is an RFQ Processor (alias for isMarketer for consistency)
     */
    public function isRfqProcessor()
    {
        return $this->hasRole('rfq_processor');
    }

    /**
     * Check if user is a client
     */
    public function isClient()
    {
        return $this->hasRole('client');
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
        $userRoles = $this->getRolesArray();
        return in_array($role, $userRoles, true) || $this->role === $role;
    }

    /**
     * Check if user is a finance user
     */
    public function isFinance(): bool
    {
        return $this->hasRole('lpo_admin');
    }

    /**
     * Check if user is an LPO Admin (alias for isFinance for consistency)
     */
    public function isLpoAdmin(): bool
    {
        return $this->hasRole('lpo_admin');
    }

    /**
     * Check if user is a Super Admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('superadmin');
    }

    /**
     * The approver chain step assigned to this user, if any.
     */
    public function approvalChainStep()
    {
        return $this->hasOne(ApprovalChainStep::class);
    }

    /**
     * Whether the user sees org-wide data (all quotes, all users) rather than
     * just their own. Approvers, LPO admins and super admins are privileged.
     */
    public function canViewAllQuotes(): bool
    {
        $roles = $this->getRolesArray();
        return !empty(array_intersect($roles, ['rfq_approver', 'lpo_admin', 'superadmin'])) || in_array($this->role, ['rfq_approver', 'lpo_admin', 'superadmin']);
    }

    /**
     * Get the user's primary role or roles string
     *
     * @return string
     */
    public function getRole()
    {
        return $this->role ?? implode(', ', $this->getRolesArray());
    }

    /**
     * Scope query to find users with a given role
     */
    public function scopeWithRole($query, string $role)
    {
        return $query->where(function ($q) use ($role) {
            $q->where('role', $role)
              ->orWhereJsonContains('roles', $role);
        });
    }

    /**
     * Get available roles
     *
     * @return array
     */
    public static function getAvailableRoles(): array
    {
        return ['rfq_approver', 'rfq_processor', 'lpo_admin', 'superadmin'];
    }
}
