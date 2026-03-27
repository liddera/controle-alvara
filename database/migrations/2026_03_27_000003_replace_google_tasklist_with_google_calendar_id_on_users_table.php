<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'google_tasklist_id') && ! Schema::hasColumn('users', 'google_calendar_id')) {
                $table->renameColumn('google_tasklist_id', 'google_calendar_id');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'google_calendar_id')) {
                $table->string('google_calendar_id')->nullable()->default('primary')->after('google_token_expires_at');
            }
        });

        DB::table('users')
            ->where(function ($query) {
                $query->whereNull('google_calendar_id')
                    ->orWhere('google_calendar_id', '@default')
                    ->orWhere('google_calendar_id', '');
            })
            ->update(['google_calendar_id' => 'primary']);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'google_calendar_id') && ! Schema::hasColumn('users', 'google_tasklist_id')) {
                $table->renameColumn('google_calendar_id', 'google_tasklist_id');
            }
        });
    }
};
