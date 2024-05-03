<?php

declare(strict_types=1);

namespace ReleaseManagement\Release\Ui\Command\PrepareRelease;

use Invis1ble\Messenger\Command\CommandBusInterface;
use ReleaseManagement\Release\Application\UseCase\Command\CreateRelease\CreateReleaseCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'release:prepare', description: 'Prepares new release')]
final class PrepareReleaseCommand extends Command
{
    public function __construct(
        private readonly CommandBusInterface $commandBus,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>Preparing new release</info>');

        $this->commandBus->dispatch(new CreateReleaseCommand(null));

        return Command::SUCCESS;
    }
}
