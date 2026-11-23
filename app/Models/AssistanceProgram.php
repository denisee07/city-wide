<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssistanceProgram extends Model
{
    protected $fillable = [
        'title', 'description', 'type', 'eligibility', 'distribution_date',
        'distribution_time', 'location', 'barangay_id', 'beneficiary_limit', 'status', 'created_by',
    ];

    protected $casts = [
        'distribution_date' => 'date',
        'beneficiary_limit' => 'integer',
    ];

    public function barangay(): BelongsTo
    {
        return $this->belongsTo(Barangay::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function beneficiaries(): HasMany
    {
        return $this->hasMany(AssistanceBeneficiary::class, 'assistance_program_id');
    }

    public static function types(): array
    {
        return [
            'financial' => 'Financial assistance',
            'food' => 'Food packs',
            'relief' => 'Relief goods',
            'school' => 'School supplies',
            'medical' => 'Medical assistance',
            'livelihood' => 'Livelihood assistance',
            'other' => 'Other aid',
        ];
    }

    public static function statuses(): array
    {
        return ['draft' => 'Draft', 'active' => 'Active', 'completed' => 'Completed', 'cancelled' => 'Cancelled'];
    }
}