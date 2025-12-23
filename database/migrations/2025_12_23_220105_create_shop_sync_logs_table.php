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
//        Schema::create('shop_sync_logs', function (Blueprint $table) {
//            $table->id();
//            $table->unsignedBigInteger('shop_id');
//            $table->foreign('shop_id')->references('id')->on('shops')->onDelete('cascade');
//            $table->string('model_type');
//            $table->timestamps();
//        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
//        Schema::dropIfExists('shop_sync_logs');
    }
};
