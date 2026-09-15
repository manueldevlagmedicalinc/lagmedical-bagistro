<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('cms_pro_pages') && ! Schema::hasColumn('cms_pro_pages', 'draft_blocks')) {
            Schema::table('cms_pro_pages', function (Blueprint $table) {
                $table->json('draft_blocks')->nullable()->after('blocks');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('cms_pro_pages') && Schema::hasColumn('cms_pro_pages', 'draft_blocks')) {
            Schema::table('cms_pro_pages', function (Blueprint $table) {
                $table->dropColumn('draft_blocks');
            });
        }
    }
};
