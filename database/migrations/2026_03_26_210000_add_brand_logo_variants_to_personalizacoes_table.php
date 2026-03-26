<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personalizacoes', function (Blueprint $table) {
            $table->string('header_logo_path')->nullable()->after('logo_path');
            $table->string('sidebar_compact_logo_path')->nullable()->after('header_logo_path');
        });

        DB::table('personalizacoes')
            ->whereNull('header_logo_path')
            ->update([
                'header_logo_path' => DB::raw('logo_path'),
            ]);

        DB::table('personalizacoes')
            ->whereNull('sidebar_compact_logo_path')
            ->update([
                'sidebar_compact_logo_path' => DB::raw('logo_path'),
            ]);
    }

    public function down(): void
    {
        Schema::table('personalizacoes', function (Blueprint $table) {
            $table->dropColumn(['header_logo_path', 'sidebar_compact_logo_path']);
        });
    }
};
