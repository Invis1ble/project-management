<?php

declare(strict_types=1);

namespace ProjectManagement\ReleasePublication\Infrastructure\Domain\Model;

use ProjectManagement\ReleasePublication\Domain\Model\ReleasePublication;
use ProjectManagement\ReleasePublication\Domain\Model\ReleasePublicationFactoryInterface;
use ProjectManagement\ReleasePublication\Domain\Model\ReleasePublicationInterface;
use ProjectManagement\ReleasePublication\Domain\Model\SourceCodeRepository\Branch\Name;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\IssueList;

final readonly class ReleasePublicationFactory implements ReleasePublicationFactoryInterface
{
    public function createReleasePublication(
        Name $branchName,
        IssueList $readyToMergeTasks,
    ): ReleasePublicationInterface {
        return ReleasePublication::create(
            $branchName,
            $readyToMergeTasks,
        );
    }
}
