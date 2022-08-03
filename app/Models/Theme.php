<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Theme extends Model
{
    use HasFactory;

    protected $table = 'themes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
    ];
    
    public function detail():HasOne
    {
        return $this->hasOne(ThemeDetail::class, 'theme_id', 'id');
    }
}
