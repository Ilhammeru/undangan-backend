<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ThemeDetail extends Model
{
    use HasFactory;

    protected $table = 'theme_detail';
    protected $fillable = [
        'theme_id',
        'path_to_theme',
        'path_to_background'
    ];

    public function theme():BelongsTo
    {
        return $this->belongsTo(Theme::class, 'theme_id', 'id');
    }
}
