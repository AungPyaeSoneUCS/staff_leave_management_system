<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'name',
    'name_mm',
    'email',
    'password',
    'role',
    'department_id',
    'staff_id',
    'phone',
    'position',
    'position_mm',
    'is_active',
    'require_admin_approval',
    'profile_image',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

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
            'is_active' => 'boolean',
            'require_admin_approval' => 'boolean',
            'deactivated_at' => 'datetime',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function leaveBalances(): HasMany
    {
        return $this->hasMany(LeaveBalance::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isDepartmentHead(): bool
    {
        return $this->role === 'department_head';
    }

    public function departmentHeadOf(): HasOne
    {
        return $this->hasOne(Department::class, 'head_id');
    }

    public function isStaff(): bool
    {
        return $this->role === 'staff';
    }

    public function isActive(): bool
    {
        return $this->is_active && is_null($this->deactivated_at);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function assignedApprovals(): HasMany
    {
        return $this->hasMany(AdminAssignment::class, 'admin_id');
    }

    public function isAssignedToRoutineDuty(): bool
    {
        return $this->assignedApprovals()->exists();
    }
}
