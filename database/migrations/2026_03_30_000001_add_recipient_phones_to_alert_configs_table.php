<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alert_configs', function (Blueprint $table) {
            $table->json('recipient_phones')->nullable()->after('recipient_emails');
        });
    }

    public function down(): void
    {
        Schema::table('alert_configs', function (Blueprint $table) {
            $table->dropColumn('recipient_phones');
        });
    }
};

