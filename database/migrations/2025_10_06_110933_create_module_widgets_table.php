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
        Schema::create('module_widgets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('module_id');
            $table->string('widget_key');
            $table->string('widget_name');
            $table->string('component_class', 500);
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('icon', 100)->nullable();
            $table->enum('width', ['quarter', 'half', 'three-quarter', 'full'])->default('half');
            $table->enum('height', ['small', 'medium', 'large', 'auto'])->default('medium');
            $table->integer('order_index')->default(0);
            $table->boolean('is_active')->default(true);
            $table->string('permission')->nullable();
            $table->json('config')->nullable();
            $table->timestamps();
            
            $table->foreign('module_id')->references('id')->on('modules')->onDelete('cascade');
            $table->unique(['module_id', 'widget_key'], 'unique_widget');
            $table->index(['is_active', 'order_index']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('module_widgets');
    }
};
