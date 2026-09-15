<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_pro_page_settings', function (Blueprint $table) {
            $table->id();
            $table->integer('cms_page_id')->unsigned()->unique();
            $table->string('editor_type', 20)->default('native');
            $table->timestamps();

            $table->foreign('cms_page_id')->references('id')->on('cms_pages')->cascadeOnDelete();
        });

        Schema::create('cms_pro_pages', function (Blueprint $table) {
            $table->id();
            $table->integer('cms_page_id')->unsigned();
            $table->string('locale', 10);
            $table->unsignedSmallInteger('schema_version')->default(1);
            $table->string('status', 20)->default('draft');
            $table->json('blocks')->nullable();
            $table->json('draft_blocks')->nullable();
            $table->longText('custom_css')->nullable();
            $table->longText('custom_js')->nullable();
            $table->longText('draft_custom_css')->nullable();
            $table->longText('draft_custom_js')->nullable();
            $table->timestamps();

            $table->unique(['cms_page_id', 'locale']);
            $table->foreign('cms_page_id')->references('id')->on('cms_pages')->cascadeOnDelete();
        });

        Schema::create('cms_pro_revisions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cms_pro_page_id');
            $table->integer('admin_id')->unsigned()->nullable();
            $table->string('status', 20);
            $table->json('blocks');
            $table->longText('custom_css')->nullable();
            $table->longText('custom_js')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('cms_pro_page_id');
            $table->foreign('cms_pro_page_id')->references('id')->on('cms_pro_pages')->cascadeOnDelete();
        });

        Schema::create('cms_pro_media', function (Blueprint $table) {
            $table->id();
            $table->integer('channel_id')->unsigned()->nullable();
            $table->integer('admin_id')->unsigned()->nullable();
            $table->string('disk', 30)->default('public');
            $table->string('path');
            $table->string('name');
            $table->string('mime', 100);
            $table->unsignedBigInteger('size');
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->string('alt_text')->nullable();
            $table->timestamps();

            $table->index(['channel_id', 'created_at']);
            $table->foreign('channel_id')->references('id')->on('channels')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_pro_media');
        Schema::dropIfExists('cms_pro_revisions');
        Schema::dropIfExists('cms_pro_pages');
        Schema::dropIfExists('cms_pro_page_settings');
    }
};
