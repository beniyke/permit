<?php

declare(strict_types=1);

/**
 * Anchor Framework
 *
 * CLI command to sync roles and permissions from configuration.
 *
 * @author BenIyke <beniyke34@gmail.com> | Twitter: @BigBeniyke
 */

namespace Permit\Commands;

use Exception;
use Permit\Permit;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class PermitSyncCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('permit:sync')
            ->setDescription('Sync roles and permissions from configuration');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Syncing Roles and Permissions');

        // Define default roles and permissions
        $rolesWithPermissions = [
            'super-admin' => [
                'name' => 'Super Administrator',
                'description' => 'Full system access',
                'permissions' => [], // Super admin bypasses all checks anyway
            ],
            'admin' => [
                'name' => 'Administrator',
                'description' => 'Administrative access',
                'permissions' => [
                    'users.view',
                    'users.create',
                    'users.update',
                    'users.delete',
                    'roles.view',
                    'roles.manage',
                    'settings.view',
                    'settings.update',
                ],
            ],
            'moderator' => [
                'name' => 'Moderator',
                'description' => 'Content moderation access',
                'inherits' => 'user',
                'permissions' => [
                    'users.view',
                    'content.moderate',
                ],
            ],
            'user' => [
                'name' => 'User',
                'description' => 'Standard user access',
                'permissions' => [
                    'profile.view',
                    'profile.update',
                ],
            ],
        ];

        try {
            Permit::sync($rolesWithPermissions);

            $io->success('Roles and permissions synced successfully!');

            // Show summary
            $roles = Permit::roles()->all();
            $permissions = Permit::permissions()->all();

            $io->table(
                ['Roles Created', 'Permissions Created'],
                [[count($roles), count($permissions)]]
            );

            return Command::SUCCESS;
        } catch (Exception $e) {
            $io->error('Failed to sync: ' . $e->getMessage());

            return Command::FAILURE;
        }
    }
}
