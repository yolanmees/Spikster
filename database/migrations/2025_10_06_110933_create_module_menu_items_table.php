<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('module_menu_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('module_id');
            $table->uuid('parent_id')->nullable();
            $table->string('title');
            $table->string('route_name')->nullable();
            $table->json('route_params')->nullable();
            $table->string('url', 500)->nullable();
            $table->string('icon', 100)->nullable();
            $table->enum('icon_type', ['heroicon', 'fontawesome', 'custom'])->default('heroicon');
            $table->string('permission')->nullable();
            $table->integer('order_index')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_external')->default(false);
            $table->enum('target', ['_self', '_blank'])->default('_self');
            $table->enum('badge_type', ['none', 'count', 'text', 'dot'])->default('none');
            $table->string('badge_source')->nullable();
            $table->string('badge_color', 50)->default('blue');
            $table->enum('menu_location', ['main', 'admin', 'user', 'footer'])->default('main');
            $table->timestamps();

            $table->foreign('module_id')->references('id')->on('modules')->onDelete('cascade');
            $table->foreign('parent_id')->references('id')->on('module_menu_items')->onDelete('cascade');
            $table->index(['module_id', 'menu_location']);
            $table->index(['parent_id']);
            $table->index(['menu_location', 'order_index']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('module_menu_items');
    }
};
