<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'parse_task_id',
        'yandex_business_id',
        'name',
        'address',
        'needs_update',
        'avg_rating',
        'total_ratings_count',
        'total_reviews_count',
        'parsed_at'
    ];

    protected $casts = [
        'avg_rating' => 'decimal:2',
        'parsed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->hasOne(User::class);
    }
    public function parseTask()
    {
        return $this->hasOne(ParseTask::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}
