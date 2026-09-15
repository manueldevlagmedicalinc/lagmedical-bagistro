<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('cms_pro_pages')) {
            return;
        }

        Schema::table('cms_pro_pages', function (Blueprint $table) {
            if (! Schema::hasColumn('cms_pro_pages', 'custom_css')) {
                $table->longText('custom_css')->nullable();
            }

            if (! Schema::hasColumn('cms_pro_pages', 'custom_js')) {
                $table->longText('custom_js')->nullable();
            }

            if (! Schema::hasColumn('cms_pro_pages', 'draft_custom_css')) {
                $table->longText('draft_custom_css')->nullable();
            }

            if (! Schema::hasColumn('cms_pro_pages', 'draft_custom_js')) {
                $table->longText('draft_custom_js')->nullable();
            }
        });

        Schema::table('cms_pro_revisions', function (Blueprint $table) {
            if (! Schema::hasColumn('cms_pro_revisions', 'custom_css')) {
                $table->longText('custom_css')->nullable();
            }

            if (! Schema::hasColumn('cms_pro_revisions', 'custom_js')) {
                $table->longText('custom_js')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('cms_pro_pages')) {
            return;
        }

        Schema::table('cms_pro_pages', function (Blueprint $table) {
            if (Schema::hasColumn('cms_pro_pages', 'custom_css')) {
                $table->dropColumn('custom_css');
            }

            if (Schema::hasColumn('cms_pro_pages', 'custom_js')) {
                $table->dropColumn('custom_js');
            }

            if (Schema::hasColumn('cms_pro_pages', 'draft_custom_css')) {
                $table->dropColumn('draft_custom_css');
            }

            if (Schema::hasColumn('cms_pro_pages', 'draft_custom_js')) {
                $table->dropColumn('draft_custom_js');
            }
        });

        Schema::table('cms_pro_revisions', function (Blueprint $table) {
            if (Schema::hasColumn('cms_pro_revisions', 'custom_css')) {
                $table->dropColumn('custom_css');
            }

            if (Schema::hasColumn('cms_pro_revisions', 'custom_js')) {
                $table->dropColumn('custom_js');
            }
        });
    }
};
