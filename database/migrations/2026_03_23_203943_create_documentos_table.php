<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alvara_id')->constrained()->cascadeOnDelete();
            $table->string('nome_arquivo');
            $table->string('caminho');
            $table->string('tipo')->nullable();
            $table->unsignedBigInteger('tamanho')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documentos');
    }
};
