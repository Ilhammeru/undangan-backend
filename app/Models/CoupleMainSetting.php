<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoupleMainSetting extends Model
{
    use HasFactory;

    protected $table = 'couple_main_setting';
    protected $fillable = [
        'couple_id',
        'link',
        'theme_id',
        'category_id',
        'reception_time_zone',
        'reception_date',
        'reception_start',
        'reception_end',
        'reception_until_finish',
        'reception_address',
        'reception_embed_maps',
        'contract_date',
        'contract_date_is_same_with_reception',
        'contract_time_zone',
        'contract_start',
        'contract_end',
        'contract_until_finish',
        'contract_address',
        'contract_address_is_same_with_reception',
        'contract_embed_maps',
        'contract_embed_maps_is_same_with_reception',
    ];

    public function couple():BelongsTo
    {
        return $this->belongsTo(Couple::class, 'couple_id', 'id');
    }
}
