<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\ReleasePublication\Infrastructure\Domain\Model;

use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\ReleasePublication;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\ReleasePublicationFactoryInterface;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\ReleasePublicationInterface;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\SourceCodeRepository\Branch\Name;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\IssueList;

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
