<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavingGoal extends Model
{
    use HasFactory;

    protected $table = 'saving_goals';

    protected $fillable = [
        'nasabah_id',
        'nama',
        'target_amount',
        'current_amount',
        'icon',
        'deadline',
        'is_completed',
    ];

    protected function casts(): array
    {
        return [
            'target_amount' => 'float',
            'current_amount' => 'float',
            'is_completed' => 'boolean',
            'deadline' => 'date',
        ];
    }

    public function nasabah(): BelongsTo
    {
        return $this->belongsTo(Nasabah::class, 'nasabah_id');
    }

    public function getProgressPercentAttribute(): float
    {
        if ($this->target_amount <= 0) {
            return 0.0;
        }

        return (float) min(100.0, max(0.0, ($this->current_amount / $this->target_amount) * 100));
    }

    public function getRemainingAttribute(): float
    {
        return (float) max(0.0, $this->target_amount - $this->current_amount);
    }
}

