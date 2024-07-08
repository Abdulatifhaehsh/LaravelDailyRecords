<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Hashash\ProjectService\Traits\ModelTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, ModelTrait;

    const table = 'users';
    const uuid = 'uuid';
    const name = 'name';
    const age = 'age';
    const gender = 'gender';
    const createdAt = 'created_at';
    const updatedAt = 'updated_at';
    const deletedAt = 'deleted_at';

    protected $table = self::table;

    protected $fillable = [self::uuid, self::name, self::age, self::gender, self::createdAt, self::updatedAt];

    protected $casts = [
        self::age => 'integer',
    ];

    protected $hidden = [
        self::deletedAt
    ];

    protected static function booted()
    {
        static::deleted(function ($user) {
            // Update averages for the day the user was created
            $date = $user->created_at->toDateString();
            DailyRecord::updateAveragesAndCounts($date);
        });
    }
}
