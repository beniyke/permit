<?php

declare(strict_types=1);

/**
 * Anchor Framework
 *
 * CLI command to clear permission cache.
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

class PermitCacheCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('permit:cache')
            ->setDescription('Clear and rebuild permission cache');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Clearing Permission Cache');

        try {
            Permit::clearCache();

            $io->success('Permission cache cleared successfully!');

            return Command::SUCCESS;
        } catch (Exception $e) {
            $io->error('Failed to clear cache: ' . $e->getMessage());

            return Command::FAILURE;
        }
    }
}
