<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('csv_fields', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('upload_id'); // Reference to the upload
            $table->string('unique_key');
            $table->string('product_title');
            $table->string('product_description', 1500);
            $table->string('style');
            $table->string('sanmar_mainframe_color');
            $table->string('size');
            $table->string('color_name');
            $table->string('piece_price');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('csv_fields');
    }
};
