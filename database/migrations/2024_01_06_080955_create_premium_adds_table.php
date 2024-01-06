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
        Schema::create('premium_adds', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('category_id');
            $table->string('education_id', 50)->nullable();
            $table->string('image', 191)->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->tinyInteger('type')->default(1)->comment('1=simple,2=premium');
            $table->text('description')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('premium_add');
    }
};
