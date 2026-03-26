<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personalizacoes', function (Blueprint $row) {
            $row->id();
            $row->foreignId('owner_id')->constrained('users')->onDelete('cascade');
            $row->string('logo_path')->nullable();
            $row->string('favicon_path')->nullable();
            $row->string('sidebar_bg_color')->default('#1f2937'); // Gray-800
            $row->string('sidebar_text_color')->default('#ffffff'); 
            $row->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personalizacoes');
    }
};
