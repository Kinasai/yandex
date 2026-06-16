<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'yandex_review_id',
        'author_name',
        'rating',
        'review_text',
        'review_date'
    ];

    protected $casts = [
        'rating' => 'integer',
        'review_date' => 'date',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    // Хелпер для группировки по звёздам
    public static function ratingDistribution(int $organizationId): array
    {
        return self::where('organization_id', $organizationId)
            ->selectRaw('rating, count(*) as count')
            ->groupBy('rating')
            ->pluck('count', 'rating')
            ->toArray();
    }
}
