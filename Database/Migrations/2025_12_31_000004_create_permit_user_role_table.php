<?php

declare(strict_types=1);

/**
 * Anchor Framework
 *
 * Migration to create permit_user_role pivot table.
 *
 * @author BenIyke <beniyke34@gmail.com> | Twitter: @BigBeniyke
 */

use Database\Migration\BaseMigration;
use Database\Schema\Schema;

class CreatePermitUserRoleTable extends BaseMigration
{
    public function up(): void
    {
        Schema::create('permit_user_role', function ($table) {
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('role_id');

            $table->primary(['user_id', 'role_id']);

            $table->foreign('user_id')
                ->references('id')
                ->on('user')
                ->onDelete('cascade');

            $table->foreign('role_id')
                ->references('id')
                ->on('permit_role')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permit_user_role');
    }
}
