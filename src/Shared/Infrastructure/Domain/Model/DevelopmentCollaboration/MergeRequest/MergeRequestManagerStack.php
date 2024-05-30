<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\Model\DevelopmentCollaboration\MergeRequest;

use Invis1ble\ProjectManagement\Shared\Domain\Exception\UnsupportedProjectException;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project\ProjectId;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Details;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequest;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequestId;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequestManagerInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Title;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Branch;

final readonly class MergeRequestManagerStack implements MergeRequestManagerInterface
{
    /**
     * @var iterable<MergeRequestManagerInterface>
     */
    private iterable $mergeRequestManagers;

    public function __construct(iterable $mergeRequestManagers)
    {
        $managers = [];

        foreach ($mergeRequestManagers as $manager) {
            if (!$manager instanceof MergeRequestManagerInterface) {
                throw new \InvalidArgumentException(sprintf(
                    'Merge Request Manager must be an instance of %s, %s given',
                    MergeRequestManagerInterface::class,
                    get_debug_type($manager),
                ));
            }

            $managers[] = $manager;
        }

        $this->mergeRequestManagers = $managers;
    }

    public function createMergeRequest(
        ProjectId $projectId,
        Title $title,
        Branch\Name $sourceBranchName,
        Branch\Name $targetBranchName,
    ): MergeRequest {
        foreach ($this->mergeRequestManagers as $manager) {
            if ($manager->supports($projectId)) {
                return $manager->createMergeRequest(
                    projectId: $projectId,
                    title: $title,
                    sourceBranchName: $sourceBranchName,
                    targetBranchName: $targetBranchName,
                );
            }
        }

        throw new UnsupportedProjectException($projectId);
    }

    public function mergeMergeRequest(ProjectId $projectId, MergeRequestId $mergeRequestId): MergeRequest
    {
        foreach ($this->mergeRequestManagers as $manager) {
            if ($manager->supports($projectId)) {
                return $manager->mergeMergeRequest($projectId, $mergeRequestId);
            }
        }

        throw new UnsupportedProjectException($projectId);
    }

    public function supports(ProjectId $projectId): bool
    {
        foreach ($this->mergeRequestManagers as $manager) {
            if ($manager->supports($projectId)) {
                return true;
            }
        }

        return false;
    }

    public function details(ProjectId $projectId, MergeRequestId $mergeRequestId): Details
    {
        foreach ($this->mergeRequestManagers as $manager) {
            if ($manager->supports($projectId)) {
                return $manager->details($projectId, $mergeRequestId);
            }
        }

        throw new UnsupportedProjectException($projectId);
    }
}
