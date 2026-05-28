<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Medicine extends Model
{
    protected $fillable = [
        'user_id',
        'name',     // Medicine name (e.g. "Paracetamol 500mg")
        'unit',     // Unit of measure: tablet | ml | sachet | capsule
        'quantity', // Current stock count — decremented when dispensed
    ];

    public function visits(): BelongsToMany
    {
        return $this->belongsToMany(Visit::class, 'visit_medicines')
                    ->withPivot('quantity_given')
                    ->withTimestamps();
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
