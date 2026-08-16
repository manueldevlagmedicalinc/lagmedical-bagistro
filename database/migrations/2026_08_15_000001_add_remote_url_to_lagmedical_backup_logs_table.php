<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lagmedical_backup_logs', function (Blueprint $table) {
            $table->string('remote_url')->nullable()->after('remote_path');
        });
    }

    public function down(): void
    {
        Schema::table('lagmedical_backup_logs', function (Blueprint $table) {
            $table->dropColumn('remote_url');
        });
    }
};
