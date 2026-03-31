<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_instances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->index();
            $table->string('provider', 50)->default('http-v2');
            $table->string('instance_key', 120)->unique();
            $table->string('instance_id', 120)->nullable();
            $table->text('instance_api_key')->nullable();
            $table->string('status', 40)->default('created');
            $table->longText('last_qr_code_base64')->nullable();
            $table->string('last_pairing_code', 60)->nullable();
            $table->string('last_connection_state', 40)->nullable();
            $table->timestamp('last_webhook_at')->nullable();
            $table->timestamp('connected_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_instances');
    }
};

