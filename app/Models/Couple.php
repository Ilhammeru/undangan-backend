<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Couple extends Model
{
    use HasFactory;

    protected $table = 'couple';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'male_nickname',
        'male_name',
        'male_photo',
        'male_parents',
        'male_instagram',
        'male_address',
        'female_nickname',
        'female_name',
        'female_photo',
        'female_parents',
        'female_instagram',
        'female_address',
    ];
}
