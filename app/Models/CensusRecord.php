<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CensusRecord extends Model
{
    protected $fillable = [
        'barangay_id', 'user_id', 'household_no', 'full_name', 'sex', 'birth_date',
        'age', 'household_size', 'purok', 'address', 'occupation', 'income_bracket',
        'is_head', 'civil_status', 'education', 'religion', 'is_pwd', 'is_senior',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'is_head' => 'boolean',
        'is_pwd' => 'boolean',
        'is_senior' => 'boolean',
        'age' => 'integer',
        'household_size' => 'integer',
    ];

    public function barangay(): BelongsTo
    {
        return $this->belongsTo(Barangay::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function sexOptions(): array
    {
        return ['male' => 'Male', 'female' => 'Female'];
    }

    public static function incomeBrackets(): array
    {
        return ['below_10k' => 'Below ₱10,000', '10k_20k' => '₱10,000–₱20,000', '20k_40k' => '₱20,000–₱40,000', 'above_40k' => 'Above ₱40,000'];
    }
}