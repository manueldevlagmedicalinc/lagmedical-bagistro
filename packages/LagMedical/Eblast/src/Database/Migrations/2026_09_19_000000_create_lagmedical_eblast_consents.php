<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lagmedical_eblast_consents', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('subscriber_id')->nullable();
            $table->string('email');
            $table->unsignedInteger('channel_id')->nullable();
            $table->string('source');
            $table->text('consent_text');
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('consented_at');
            $table->timestamps();
            $table->index(['email', 'source']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lagmedical_eblast_consents');
    }
};
