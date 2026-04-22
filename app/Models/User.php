<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'employee_id',
        'username',
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'name',
        'email',
        'password',
        'contact_number',
        'alternate_contact',
        'role',
        'is_active',
        'account_status',        // ADDED: pending, approved, rejected
        'rejection_reason',      // ADDED: reason for rejection
        'position',
        'department',
        'profile_photo',
        'permissions',
        'last_login_at',
        'last_login_ip',
        'created_by',
        'updated_by',
        'approved_by',           // ADDED: who approved this user
        'approved_at',           // ADDED: when approved
        'rejected_by',           // ADDED: who rejected this user
        'rejected_at',           // ADDED: when rejected
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
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
            'last_login_at' => 'datetime',
            'approved_at' => 'datetime',      // ADDED
            'rejected_at' => 'datetime',      // ADDED
            'two_factor_confirmed_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'permissions' => 'array',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Get the user's full name.
     */
    public function getFullNameAttribute(): string
    {
        $name = "{$this->first_name} {$this->last_name}";
        if ($this->middle_name) {
            $name = "{$this->first_name} {$this->middle_name} {$this->last_name}";
        }
        if ($this->suffix) {
            $name .= " {$this->suffix}";
        }
        return $name;
    }

    /**
     * Check if user is admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is staff.
     */
    public function isStaff(): bool
    {
        return $this->role === 'staff';
    }

    /**
     * Check if user is regular user.
     */
    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    /**
     * Check if account is pending approval.
     */
    public function isPending(): bool
    {
        return $this->account_status === 'pending';
    }

    /**
     * Check if account is approved.
     */
    public function isApproved(): bool
    {
        return $this->account_status === 'approved';
    }

    /**
     * Check if account is rejected.
     */
    public function isRejected(): bool
    {
        return $this->account_status === 'rejected';
    }

    /**
     * Check if user has specific permission.
     */
    public function hasPermission(string $permission): bool
    {
        if ($this->isAdmin()) {
            return true;
        }
        
        $permissions = $this->permissions ?? [];
        return in_array($permission, $permissions) || in_array('*', $permissions);
    }

    /**
     * Check if user account is active.
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Update last login information.
     */
    public function updateLastLogin(string $ip = null): void
    {
        $this->update([
            'last_login_at' => now(),
            'last_login_ip' => $ip,
        ]);
    }

    /**
     * Get the user who created this user.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this user.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who approved this user.
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the user who rejected this user.
     */
    public function rejector()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    /**
     * Scope for active users only.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for specific role.
     */
    public function scopeRole($query, $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Scope for pending accounts.
     */
    public function scopePending($query)
    {
        return $query->where('account_status', 'pending');
    }

    /**
     * Scope for approved accounts.
     */
    public function scopeApproved($query)
    {
        return $query->where('account_status', 'approved');
    }
}