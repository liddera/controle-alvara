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
        Schema::create('alert_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->index(); // Para multi-tenancy
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('tipo_alvara_id')->nullable()->constrained()->onDelete('cascade');
            $table->integer('days_before');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Um usuário não deve ter o mesmo alerta de dias duplicado para o mesmo tipo
            $table->unique(['owner_id', 'user_id', 'tipo_alvara_id', 'days_before'], 'unique_user_alert');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alert_configs');
    }
};
