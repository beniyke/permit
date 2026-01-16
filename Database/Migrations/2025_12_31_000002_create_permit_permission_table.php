<?php

declare(strict_types=1);

/**
 * Anchor Framework
 *
 * Migration to create permit_permission table.
 *
 * @author BenIyke <beniyke34@gmail.com> | Twitter: @BigBeniyke
 */

use Database\Migration\BaseMigration;
use Database\Schema\Schema;

class CreatePermitPermissionTable extends BaseMigration
{
    public function up(): void
    {
        Schema::create('permit_permission', function ($table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->text('description')->nullable();
            $table->string('group', 50)->nullable()->index();
            $table->dateTimestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permit_permission');
    }
}
