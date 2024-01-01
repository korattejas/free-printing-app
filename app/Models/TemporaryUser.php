<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TemporaryUser extends Model
{
    use HasFactory;

    protected $table = 'temporary_users';

    protected $fillable = [
        'name',
        'mobile_no',
        'email',
        'password',
    ];

}
