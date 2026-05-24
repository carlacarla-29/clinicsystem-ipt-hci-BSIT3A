<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * ============================================================
 *  Medicine Model
 * ============================================================
 *
 * Maps to the `medicines` table.
 *
 * Tracks medicine inventory in the clinic.
 * When a medicine is dispensed during a visit, the stock
 * quantity is decremented automatically in VisitController.
 *
 * Additional Feature: Medicine Inventory Management
 * ============================================================
 */
class Medicine extends Model
{
    /**
     * Columns allowed for mass assignment.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',     // Medicine name (e.g. "Paracetamol 500mg")
        'unit',     // Unit of measure: tablet | ml | sachet | capsule
        'quantity', // Current stock count — decremented when dispensed
    ];

    // ============================================================
    // RELATIONSHIPS
    // ============================================================

    /**
     * A medicine can be dispensed in many visits (many-to-many).
     * The pivot table 'visit_medicines' records how much was given per visit.
     *
     * Usage:
     *   $medicine->visits               → all visits this medicine was used in
     *   $medicine->visits->first()->pivot->quantity_given → amount given
     */
    public function visits(): BelongsToMany
    {
        return $this->belongsToMany(Visit::class, 'visit_medicines')
                    ->withPivot('quantity_given')
                    ->withTimestamps();
    }
}
