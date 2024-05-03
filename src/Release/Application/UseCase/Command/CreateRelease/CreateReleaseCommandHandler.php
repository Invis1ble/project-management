<?php

declare(strict_types=1);

namespace ReleaseManagement\Release\Application\UseCase\Command\CreateRelease;

use Invis1ble\Messenger\Query\QueryBusInterface;
use ReleaseManagement\Release\Application\UseCase\Command\ReleaseRepositoryAwareCommandHandler;
use ReleaseManagement\Release\Application\UseCase\Query\GetLatestRelease\GetLatestReleaseQuery;
use ReleaseManagement\Release\Domain\Model\ReleaseBranchName;
use ReleaseManagement\Release\Domain\Model\ReleaseFactoryInterface;
use ReleaseManagement\Release\Domain\Model\TaskTracker\Release as TaskTrackerRelease;
use ReleaseManagement\Release\Domain\Repository\ReleaseRepositoryInterface;

final readonly class CreateReleaseCommandHandler extends ReleaseRepositoryAwareCommandHandler
{
    public function __construct(
        ReleaseRepositoryInterface $repository,
        private ReleaseFactoryInterface $factory,
        private QueryBusInterface $queryBus,
    ) {
        parent::__construct($repository);
    }

    public function __invoke(CreateReleaseCommand $command): void
    {
        /** @var TaskTrackerRelease $release */
        $release = $this->queryBus->ask(new GetLatestReleaseQuery());

        if (null === $release) {
            throw new \UnexpectedValueException('No release found');
        }

        if (!$release->released) {
            throw new \UnexpectedValueException("Latest release $release->name not yet released");
        }

        $releaseBranchName = ReleaseBranchName::fromString($release->name);

        if (null === $command->branchName) {
            $branchName = $releaseBranchName->bumpVersion();
        } elseif (!$command->branchName->versionNewerThan($releaseBranchName)) {
            throw new \InvalidArgumentException("Latest release $releaseBranchName has a newer version");
        } else {
            $branchName = $command->branchName;
        }

        $release = $this->factory->createRelease($branchName);

        $this->storeRelease($release);
    }
}
