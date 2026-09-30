<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Import extends Model
{
    use HasFactory;

    protected $fillable = [
        'source',
        'imported_at',
        'rows_read',
        'rows_inserted',
        'rows_ignored',
        'errors',
    ];

    protected $casts = [
        'imported_at' => 'datetime',
        'errors' => 'array',
    ];
}
