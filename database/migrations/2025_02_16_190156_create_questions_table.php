<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->string('title_hu')->nullable();
            $table->string('title_en')->nullable();
            $table->text('description_hu')->nullable();
            $table->text('description_en')->nullable();
            $table->text('initial_code');
            $table->text('solution')->nullable();
            $table->string('difficulty');
            $table->json('test_cases');
            $table->string('source')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};