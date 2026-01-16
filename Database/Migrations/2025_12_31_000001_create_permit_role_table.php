<?php

declare(strict_types=1);

/**
 * Anchor Framework
 *
 * Migration to create permit_role table.
 *
 * @author BenIyke <beniyke34@gmail.com> | Twitter: @BigBeniyke
 */

use Database\Migration\BaseMigration;
use Database\Schema\Schema;

class CreatePermitRoleTable extends BaseMigration
{
    public function up(): void
    {
        Schema::create('permit_role', function ($table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->dateTimestamps();

            $table->foreign('parent_id')
                ->references('id')
                ->on('permit_role')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permit_role');
    }
}
