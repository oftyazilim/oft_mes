<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'email',
        'category',
        'rating',
        'message',
        'page_url',
        'user_agent',
        'screenshot_path',
        'status',
    ];

    // API çıktısına otomatik eklenecek sanal alanlar
    protected $appends = ['screenshot_url'];

    public function getScreenshotUrlAttribute(): ?string
    {
        $path = $this->screenshot_path;
        if (!$path) return null;
        return asset('storage/' . ltrim($path, '/'));
    }
}
