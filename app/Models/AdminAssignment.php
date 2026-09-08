<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminAssignment extends Model
{
    protected $fillable = [
        'admin_id',
        'leave_request_id',
        'reason',
    ];

    protected function casts(): array
    {
        return [
            'leave_request_id' => 'integer',
        ];
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function leaveRequest(): BelongsTo
    {
        return $this->belongsTo(LeaveRequest::class);
    }
}
