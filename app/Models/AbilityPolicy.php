<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AbilityPolicy extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'actions',
        'subjects',
        'description',
    ];

    protected $casts = [
        'actions' => 'array',
        'subjects' => 'array',
    ];
}
