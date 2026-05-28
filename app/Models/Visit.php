<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Visit extends Model
{
    protected $fillable = [
        'student_id',   // FK → students.id
        'complaint',    // What the student came in for
        'diagnosis',    // Nurse/doctor's diagnosis
        'treatment',    // Treatment given
        'status',       // pending | treated | referred
        'visited_at',   // Date and time of the visit
        'recorded_by',  // FK → users.id (who logged this visit)
    ];

    protected $casts = [
        'visited_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function medicines(): BelongsToMany
    {
        return $this->belongsToMany(Medicine::class, 'visit_medicines')
                    ->withPivot('quantity_given')
                    ->withTimestamps();
    }

    public function scopeOwnedBy(Builder $query, int $userId): Builder
    {
        return $query->where('recorded_by', $userId);
    }
}
