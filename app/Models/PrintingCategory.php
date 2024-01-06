<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrintingCategory extends Model
{
    use HasFactory;

    protected $table = 'printing_categories';

    protected $fillable = [
        'name',
        'status',
    ];

}
