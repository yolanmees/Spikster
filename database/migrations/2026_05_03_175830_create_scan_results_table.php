<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scan_results', function (Blueprint $table) {
            $table->id();
            $table->string('server_id')->nullable()->index();
            $table->string('site_id')->nullable()->index();
            $table->string('type'); // 'malware', 'vulnerability', 'audit', 'site-scan'
            $table->string('status'); // 'clean', 'warning', 'infected', 'failed'
            $table->json('findings')->nullable();
            $table->integer('findings_count')->default(0);
            $table->integer('files_scanned')->nullable();
            $table->string('scanned_by')->nullable();
            $table->timestamp('scanned_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scan_results');
    }
};
