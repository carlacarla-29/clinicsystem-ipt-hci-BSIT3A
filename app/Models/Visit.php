<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * ============================================================
 *  Visit Model
 * ============================================================
 *
 * GUIDE: What is a Model?
 * ------------------------
 * A Model is the "M" in MVC. It represents ONE table in the database
 * and handles all data logic: reading, writing, relationships.
 *
 * This Visit model maps to the `visits` table.
 *
 * KEY CONCEPTS:
 *   $fillable  → Columns allowed for mass assignment (e.g. Visit::create([...]))
 *               Any column NOT listed here is protected from mass assignment,
 *               which prevents malicious users from injecting extra fields.
 *
 *   $casts     → Automatically converts column values to PHP types.
 *               'visited_at' => 'datetime' means you can call $visit->visited_at->format('Y-m-d')
 *               without manually parsing the string.
 *
 *   Relationships → Methods that link this model to other models.
 *               student()   → a visit belongs to one student
 *               recorder()  → a visit was recorded by one user (nurse/admin)
 *               medicines() → a visit can have many medicines (pivot table)
 * ============================================================
 */
class Visit extends Model
{
    /**
     * Columns that can be set via Visit::create([...]) or $visit->fill([...]).
     * Always list only the columns you intentionally accept from user input.
     *
     * @var array<string>
     */
    protected $fillable = [
        'student_id',   // FK → students.id
        'complaint',    // What the student came in for
        'diagnosis',    // Nurse/doctor's diagnosis
        'treatment',    // Treatment given
        'status',       // pending | treated | referred
        'visited_at',   // Date and time of the visit
        'recorded_by',  // FK → users.id (who logged this visit)
    ];

    /**
     * Automatically cast these columns to specific PHP types.
     * 'datetime' cast lets you use Carbon methods: $visit->visited_at->format(...)
     *
     * @var array<string, string>
     */
    protected $casts = [
        'visited_at' => 'datetime',
    ];

    // ============================================================
    // RELATIONSHIPS
    // ============================================================

    /**
     * A visit belongs to one student.
     *
     * Usage: $visit->student->name
     * SQL:   SELECT * FROM students WHERE id = visits.student_id
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * A visit was recorded by one user (the clinic nurse or admin).
     * We specify 'recorded_by' because the FK column name is not the
     * default 'user_id' — Laravel needs to know the correct column.
     *
     * Usage: $visit->recorder->name
     */
    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /**
     * A visit can have many medicines dispensed (many-to-many).
     * The pivot table 'visit_medicines' links visits ↔ medicines
     * and also stores how much of each medicine was given.
     *
     * Usage: $visit->medicines  (returns a Collection of Medicine models)
     *        $visit->medicines->first()->pivot->quantity_given
     *
     * withPivot()      → also load these extra columns from the pivot table
     * withTimestamps() → let the pivot table track created_at / updated_at
     */
    public function medicines(): BelongsToMany
    {
        return $this->belongsToMany(Medicine::class, 'visit_medicines')
                    ->withPivot('quantity_given')
                    ->withTimestamps();
    }
}
