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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->nullableUuidMorphs('settingable');
            $table->string('scope')->nullable();
            $table->string('setting');
            $table->string('type')->nullable();
            $table->json('value');
            $table->timestamps();

            // Add indexes for performance
            $table->index('scope');
            $table->index('setting');
            $table->index(['settingable_type', 'settingable_id', 'setting'], 'settings_morph_setting_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
