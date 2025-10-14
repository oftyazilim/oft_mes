<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserGridLayout extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'page_key',
        'name',
        'include_filters',
        'state',
        'meta',
    ];

    protected $casts = [
        'include_filters' => 'boolean',
        'state' => 'array',
        'meta' => 'array',
    ];
}
