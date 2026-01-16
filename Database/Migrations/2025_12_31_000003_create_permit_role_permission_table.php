<?php

declare(strict_types=1);

/**
 * Anchor Framework
 *
 * Migration to create permit_role_permission pivot table.
 *
 * @author BenIyke <beniyke34@gmail.com> | Twitter: @BigBeniyke
 */

use Database\Migration\BaseMigration;
use Database\Schema\Schema;

class CreatePermitRolePermissionTable extends BaseMigration
{
    public function up(): void
    {
        Schema::create('permit_role_permission', function ($table) {
            $table->unsignedBigInteger('role_id');
            $table->unsignedBigInteger('permission_id');

            $table->primary(['role_id', 'permission_id']);

            $table->foreign('role_id')
                ->references('id')
                ->on('permit_role')
                ->onDelete('cascade');

            $table->foreign('permission_id')
                ->references('id')
                ->on('permit_permission')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permit_role_permission');
    }
}
