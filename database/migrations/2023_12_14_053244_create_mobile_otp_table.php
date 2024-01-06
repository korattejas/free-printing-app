<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mobile_otp', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
//            $table->foreign('user_id')->references('id')->on('users');
            $table->enum('user_type', [0, 1])->default(0)->comment('0 = temp user, 1 = verified user');
            $table->bigInteger('mobile_no')->unique();
            $table->unsignedInteger('otp')->unique();
            $table->timestamp('mobile_otp_verified_at')->nullable();
            $table->timestamp('mobile_otp_expire_at')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mobile_otp');
    }
};
