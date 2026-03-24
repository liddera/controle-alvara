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
        // Se a tabela tiver colunas antigas, vamos renomeá-las e ajustar
        Schema::table('alert_configs', function (Blueprint $table) {
            // Adicionamos owner_id
            if (!Schema::hasColumn('alert_configs', 'owner_id')) {
                $table->foreignId('owner_id')->after('id')->nullable()->index();
            }

            // Renomeamos dias_antes para days_before se existir
            if (Schema::hasColumn('alert_configs', 'dias_antes')) {
                $table->renameColumn('dias_antes', 'days_before');
            }

            // Renomeamos ativo para is_active se existir
            if (Schema::hasColumn('alert_configs', 'ativo')) {
                $table->renameColumn('ativo', 'is_active');
            }

            // Adicionamos tipo_alvara_id se não existir
            if (!Schema::hasColumn('alert_configs', 'tipo_alvara_id')) {
                $table->foreignId('tipo_alvara_id')->after('user_id')->nullable()->constrained()->onDelete('cascade');
            }

            // Removemos a coluna 'tipo' (string) se existir
            if (Schema::hasColumn('alert_configs', 'tipo')) {
                $table->dropColumn('tipo');
            }
        });

        // Agora aplicamos o índice único (limpando primeiro duplicatas se houver)
        Schema::table('alert_configs', function (Blueprint $table) {
             // O Laravel/Postgres pode ter criado índices automáticos, vamos tentar limpar antes de criar o nosso oficial
             $table->unique(['owner_id', 'user_id', 'tipo_alvara_id', 'days_before'], 'unique_user_alert');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alert_configs', function (Blueprint $table) {
            $table->dropUnique('unique_user_alert');
            $table->dropColumn(['owner_id', 'tipo_alvara_id']);
            $table->renameColumn('days_before', 'dias_antes');
            $table->renameColumn('is_active', 'ativo');
            $table->string('tipo')->nullable();
        });
    }
};
