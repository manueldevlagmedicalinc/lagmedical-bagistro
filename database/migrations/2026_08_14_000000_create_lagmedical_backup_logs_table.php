<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lagmedical_backup_logs', function (Blueprint $table) {
            $table->id();
            $table->string('status')->index();
            $table->boolean('monthly_snapshot')->default(false)->index();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->json('files')->nullable();
            $table->string('remote_path')->nullable();
            $table->text('message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lagmedical_backup_logs');
    }
};
