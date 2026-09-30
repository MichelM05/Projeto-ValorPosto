<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FuelPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'station_id',
        'fuel_type',
        'price',
        'collected_at',
    ];

    protected $casts = [
        'price' => 'decimal:3',
        'collected_at' => 'date',
    ];

    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class);
    }
}
