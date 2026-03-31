<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_dispatch_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('document_dispatch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('document_dispatch_message_id')->nullable()->constrained()->nullOnDelete();
            $table->string('provider', 32);
            $table->string('event_name', 64);
            $table->string('event_key')->nullable();
            $table->string('provider_message_id')->nullable();
            $table->string('normalized_status', 32)->nullable();
            $table->timestamp('occurred_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->json('payload');
            $table->timestamps();

            $table->index(['owner_id', 'provider']);
            $table->index(['provider', 'provider_message_id']);
            $table->unique(['provider', 'event_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_dispatch_events');
    }
};
