<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Ui\Command;

use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

abstract class Command extends SymfonyCommand
{
    protected SymfonyStyle $io;

    protected function configure(): void
    {
        $this
            ->addOption(
                'no-banner',
                null,
                InputOption::VALUE_NONE,
                'Do not show the project banner.',
            )
        ;

        $this
            ->addOption(
                'no-logo',
                null,
                InputOption::VALUE_NONE,
                'Do not show the project logo.',
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        if (!$input->getOption('no-banner')) {
            $this->showBanner($this->io, !$input->getOption('no-logo'));
        }

        return self::SUCCESS;
    }

    protected function showBanner(SymfonyStyle $io, bool $showLogo): void
    {
        if ($showLogo) {
            $io->writeln(file_get_contents(__DIR__ . '/banner/logo.txt'));
        } else {
            $io->newLine();
        }

        $io->writeln(file_get_contents(__DIR__ . '/banner/title.txt'));
    }
}
