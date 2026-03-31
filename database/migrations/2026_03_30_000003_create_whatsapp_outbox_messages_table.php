<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_outbox_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->index();
            $table->string('instance_key', 120);
            $table->string('type', 40);
            $table->string('to', 20);
            $table->json('payload');
            $table->string('provider_message_id', 120)->nullable();
            $table->string('status', 20)->default('queued');
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->text('last_error')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['owner_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_outbox_messages');
    }
};

