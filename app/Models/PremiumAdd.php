<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PremiumAdd extends Model
{
    use HasFactory;

    protected $table = 'premium_adds';

    protected $fillable = [
        'category_id',
        'education_id',
        'image',
        'start_date',
        'end_date',
        'type',
        'description',
        'status',
    ];

}
