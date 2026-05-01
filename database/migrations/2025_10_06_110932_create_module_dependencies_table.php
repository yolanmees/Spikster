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
        Schema::create('module_dependencies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('module_id');
            $table->uuid('required_module_id')->nullable();
            $table->string('required_package')->nullable();
            $table->string('required_version', 50)->nullable();
            $table->enum('dependency_type', ['module', 'package', 'php', 'extension'])->default('module');
            $table->boolean('is_satisfied')->default(false);
            $table->timestamps();

            $table->foreign('module_id')->references('id')->on('modules')->onDelete('cascade');
            $table->foreign('required_module_id')->references('id')->on('modules')->onDelete('cascade');
            $table->index(['module_id']);
            $table->index(['is_satisfied']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('module_dependencies');
    }
};
