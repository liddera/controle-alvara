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
        Schema::table('personalizacoes', function (Blueprint $table) {
            $table->string('sidebar_hover_color')->nullable()->after('sidebar_text_color');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('personalizacoes', function (Blueprint $table) {
            $table->dropColumn('sidebar_hover_color');
        });
    }
};
