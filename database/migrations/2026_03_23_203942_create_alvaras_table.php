<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alvaras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('tipo');
            $table->string('numero')->nullable();
            $table->date('data_emissao')->nullable();
            $table->date('data_vencimento');
            $table->enum('status', ['vigente', 'proximo', 'vencido'])->default('vigente');
            $table->text('observacoes')->nullable();
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('empresa_id');
            $table->index('data_vencimento');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alvaras');
    }
};
