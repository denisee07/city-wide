<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssistanceBeneficiary extends Model
{
    protected $fillable = [
        'assistance_program_id', 'user_id', 'reference_no', 'status', 'qr_code',
        'claimed_at', 'claimed_by',
    ];

    protected $casts = [
        'claimed_at' => 'datetime',
    ];

    public function program(): BelongsTo
    {
        return $this->belongsTo(AssistanceProgram::class, 'assistance_program_id');
    }

    public function resident(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function claimer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'claimed_by');
    }
}