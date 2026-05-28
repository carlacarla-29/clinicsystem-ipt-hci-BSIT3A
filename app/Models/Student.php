<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Student extends Model
{
    protected $fillable = [
        'user_id',
        'student_id',   // School-assigned ID (e.g. "2024-0001"), must be unique
        'name',         // Full name of the student
        'grade_level',  // e.g. "Grade 7", "Grade 12"
        'section',      // e.g. "Sampaguita"
        'gender',       // male | female
        'birthdate',    // Used to compute age if needed
    ];

    protected $casts = [
        'birthdate' => 'date',
    ];

    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class);
    }

    public function latestVisit(): HasOne
    {
        return $this->hasOne(Visit::class)->latestOfMany('visited_at');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeOwnedBy(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }
}
