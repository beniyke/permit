<?php

declare(strict_types=1);

/**
 * Anchor Framework
 *
 * Migration to create permit_user_permission table.
 *
 * @author BenIyke <beniyke34@gmail.com> | Twitter: @BigBeniyke
 */

use Database\Migration\BaseMigration;
use Database\Schema\Schema;

class CreatePermitUserPermissionTable extends BaseMigration
{
    public function up(): void
    {
        Schema::create('permit_user_permission', function ($table) {
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('permission_id');
            $table->string('type', 10)->default('grant');

            $table->primary(['user_id', 'permission_id']);

            $table->foreign('user_id')
                ->references('id')
                ->on('user')
                ->onDelete('cascade');

            $table->foreign('permission_id')
                ->references('id')
                ->on('permit_permission')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permit_user_permission');
    }
}
