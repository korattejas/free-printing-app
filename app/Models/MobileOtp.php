<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MobileOtp extends Model
{
    use HasFactory;

    protected $table = 'mobile_otp';

    protected $fillable = [
        'user_id',
        'mobile_no',
        'user_type',
        'otp',
        'mobile_otp_expire_at',
        'mobile_otp_verified_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

}
