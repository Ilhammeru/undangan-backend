<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryInvitation extends Model
{
    use HasFactory;

    protected $table = 'category_invitation';
    protected $fillable = ['name'];
}
