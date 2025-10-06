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
        Schema::create('module_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('module_id');
            $table->string('setting_key');
            $table->text('setting_value')->nullable();
            $table->enum('setting_type', ['string', 'boolean', 'integer', 'float', 'json', 'array'])->default('string');
            $table->boolean('is_encrypted')->default(false);
            $table->boolean('is_visible')->default(true);
            $table->string('group_name', 100)->nullable();
            $table->integer('order_index')->default(0);
            $table->timestamps();
            
            $table->foreign('module_id')->references('id')->on('modules')->onDelete('cascade');
            $table->unique(['module_id', 'setting_key'], 'unique_module_setting');
            $table->index(['module_id', 'group_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('module_settings');
    }
};
