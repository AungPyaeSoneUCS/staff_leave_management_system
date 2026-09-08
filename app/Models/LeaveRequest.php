<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeaveRequest extends Model
{
    protected $fillable = [
        'user_id',
        'leave_type_id',
        'start_date',
        'end_date',
        'total_days',
        'reason',
        'staff_signature',
        'attachment_path',
        'status',
        'current_approval_level',
        'reviewer_id',
        'review_remarks',
        'reviewer_remarks',
        'hr_remarks',
        'super_admin_remarks',
        'reviewer_signature',
        'reviewed_at',
        'hr_id',
        'super_admin_id',
        'hr_signature',
        'super_admin_signature',
        'is_half_day',
        'duty_exchange_user_id',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'total_days' => 'decimal:2',
            'status' => 'string',
            'current_approval_level' => 'integer',
            'reviewed_at' => 'datetime',
            'is_half_day' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function hr(): BelongsTo
    {
        return $this->belongsTo(User::class, 'hr_id');
    }

    public function super_admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'super_admin_id');
    }

    public function dutyExchangeUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'duty_exchange_user_id');
    }

    public function adminAssignments(): HasMany
    {
        return $this->hasMany(AdminAssignment::class);
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by_id');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isRevoked(): bool
    {
        return $this->status === 'revoked';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function isAwaitingDepartmentHead(): bool
    {
        return $this->isPending() && $this->current_approval_level === 1;
    }

    public function isAwaitingHR(): bool
    {
        return $this->isPending() && $this->current_approval_level === 2;
    }

    public function isAwaitingSuperAdmin(): bool
    {
        return $this->isPending() && $this->current_approval_level === 3;
    }
}
