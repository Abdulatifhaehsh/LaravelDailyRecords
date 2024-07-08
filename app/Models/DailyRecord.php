<?php

namespace App\Models;

use Hashash\ProjectService\Bases\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DailyRecord extends BaseModel
{
    use HasFactory, SoftDeletes;

    const table = 'daily_records';
    const date = 'date';
    const maleCount = 'male_count';
    const femaleCount = 'female_count';
    const maleAvgAge = 'male_avg_age';
    const femaleAvgAge = 'female_avg_age';
    const createdAt = 'created_at';
    const updatedAt = 'updated_at';
    const deletedAt = 'deleted_at';

    protected $table = self::table;
    protected $fillable = [self::date, self::maleCount, self::femaleCount, self::maleAvgAge, self::femaleAvgAge, self::createdAt, self::updatedAt];

    protected $casts = [
        self::date => 'date',
        self::maleCount => 'integer',
        self::femaleCount => 'integer',
        self::maleAvgAge => 'float',
        self::femaleAvgAge => 'float',
    ];

    protected $hidden = [
        self::deletedAt
    ];

    public static function updateAveragesAndCounts($date)
    {
        // Calculate average age of males
        $maleData = User::where('gender', 'male')
            ->whereDate('created_at', $date)
            ->selectRaw('count(*) as count, avg(age) as avg_age')
            ->first();

        // Calculate average age of females
        $femaleData = User::where('gender', 'female')
            ->whereDate('created_at', $date)
            ->selectRaw('count(*) as count, avg(age) as avg_age')
            ->first();

        // Update DailyRecord
        self::updateOrCreate(
            ['date' => $date],
            [
                'male_count' => $maleData->count,
                'male_avg_age' => $maleData->avg_age ?: 0,
                'female_count' => $femaleData->count,
                'female_avg_age' => $femaleData->avg_age ?: 0,
            ]
        );
    }
}
