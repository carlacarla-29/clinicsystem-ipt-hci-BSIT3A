<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * ============================================================
 *  Student Model
 * ============================================================
 *
 * Maps to the `students` table.
 *
 * A student is the core entity of the clinic system.
 * One student can have many clinic visits over time.
 * ============================================================
 */
class Student extends Model
{
    /**
     * Columns allowed for mass assignment.
     * These match exactly what the StoreStudentRequest validates.
     *
     * @var array<string>
     */
    protected $fillable = [
        'user_id',
        'student_id',   // School-assigned ID (e.g. "2024-0001"), must be unique
        'name',         // Full name of the student
        'grade_level',  // e.g. "Grade 7", "Grade 12"
        'section',      // e.g. "Sampaguita"
        'gender',       // male | female
        'birthdate',    // Used to compute age if needed
    ];

    /**
     * Cast birthdate to a Carbon date instance so you can do:
     * $student->birthdate->age  → auto-calculates age
     * $student->birthdate->format('F d, Y')
     *
     * @var array<string, string>
     */
    protected $casts = [
        'birthdate' => 'date',
    ];

    // ============================================================
    // RELATIONSHIPS
    // ============================================================

    /**
     * A student can have many visits over time (one-to-many).
     *
     * Usage:
     *   $student->visits              → all visits (Collection)
     *   $student->visits()->count()   → total number of visits
     *   $student->visits()->latest('visited_at')->first() → most recent visit
     */
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
