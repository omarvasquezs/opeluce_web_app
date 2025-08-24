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
        // Settings for Auto Kerato-Refractometer (shared folder polling)
        Schema::create('refractometer_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('default');
            // Original network/UNC share path, e.g. \\127.0.0.1\\kr
            $table->string('unc_path')->nullable();
            // Local mount / mapped path on the Laravel host (e.g. /mnt/kr)
            $table->string('local_mount_path')->nullable();
            // Optional alternate path (e.g. a Windows drive letter on a Windows host)
            $table->string('alternate_path')->nullable();
            // Polling interval (seconds) for new XML files (if you implement a watcher job)
            $table->unsignedInteger('poll_interval_seconds')->default(30);
            // File pattern mask (glob) to filter XML files, e.g. M-Serial*.xml
            $table->string('file_pattern')->default('*.xml');
            // Keep how many historical parsed records in cache (optional usage)
            $table->unsignedInteger('history_limit')->default(500);
            // Generic JSON options if needed later (e.g. timezone, device model metadata)
            $table->json('options')->nullable();
            $table->boolean('enabled')->default(true);
            $table->timestamps();
            $table->unique(['name']);
        });

        // Settings for Lensometer (PostgreSQL connection metadata)
        Schema::create('lensometer_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('default');
            $table->string('host')->nullable();
            $table->unsignedInteger('port')->nullable();
            $table->string('database')->nullable();
            $table->string('username')->nullable();
            // Do NOT store plaintext password ideally; leave nullable so .env can supply it.
            $table->string('password_encrypted')->nullable(); // If you implement encryption later.
            $table->string('schema')->nullable();
            $table->string('table_name')->default('lensmeterresulttbl');
            // Optional SSL / connection flags, etc.
            $table->json('options')->nullable();
            $table->boolean('enabled')->default(true);
            $table->timestamps();
            $table->unique(['name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lensometer_settings');
        Schema::dropIfExists('refractometer_settings');
    }
};
