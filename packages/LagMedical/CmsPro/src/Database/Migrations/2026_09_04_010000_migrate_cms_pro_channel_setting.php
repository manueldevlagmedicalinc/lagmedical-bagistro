<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('cms_pro_channel_settings');

        if (! Schema::hasTable('cms_pro_page_settings')) {
            Schema::create('cms_pro_page_settings', function (Blueprint $table) {
                $table->id();
                $table->integer('cms_page_id')->unsigned()->unique();
                $table->string('editor_type', 20)->default('native');
                $table->timestamps();

                $table->foreign('cms_page_id')->references('id')->on('cms_pages')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_pro_page_settings');
    }
};
