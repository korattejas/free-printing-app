<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('temporary_users', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->bigInteger('mobile_no')->unique();
            $table->string('email', 50)->unique();
            $table->string('password');
            $table->date('dob')->nullable();
            $table->enum('gender', [0, 1])->comment('0=male, 1=female')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('temporary_user');
    }
};
