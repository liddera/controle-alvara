<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'google_token_expires_at')) {
                $table->timestamp('google_token_expires_at')->nullable()->after('google_refresh_token');
            }

            if (! Schema::hasColumn('users', 'google_tasklist_id')) {
                $table->string('google_tasklist_id')->nullable()->default('@default')->after('google_token_expires_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'google_tasklist_id')) {
                $table->dropColumn('google_tasklist_id');
            }

            if (Schema::hasColumn('users', 'google_token_expires_at')) {
                $table->dropColumn('google_token_expires_at');
            }
        });
    }
};
