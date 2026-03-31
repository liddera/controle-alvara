<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_dispatch_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_dispatch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('documento_id')->nullable()->constrained('documentos')->nullOnDelete();
            $table->string('provider', 32);
            $table->string('channel', 32);
            $table->string('message_type', 48);
            $table->string('provider_message_id')->nullable();
            $table->string('provider_reference')->nullable();
            $table->string('provider_status_raw')->nullable();
            $table->string('current_status', 32)->default('enviando');
            $table->unsignedTinyInteger('status_rank')->default(0);
            $table->string('destination_email')->nullable();
            $table->string('destination_phone', 32)->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['document_dispatch_id', 'channel']);
            $table->index(['owner_id', 'provider']);
            $table->index(['owner_id', 'current_status']);
            $table->index(['provider', 'provider_message_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_dispatch_messages');
    }
};
