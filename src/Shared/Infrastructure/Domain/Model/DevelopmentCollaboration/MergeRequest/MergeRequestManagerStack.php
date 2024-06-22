<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\Model\DevelopmentCollaboration\MergeRequest;

use Invis1ble\ProjectManagement\Shared\Domain\Exception\UnsupportedProjectException;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project\ProjectId;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Details;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequest;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequestIid;
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
        return $this->delegate(
            projectId: $projectId,
            callback: fn (MergeRequestManagerInterface $manager): MergeRequest => $manager->createMergeRequest(
                projectId: $projectId,
                title: $title,
                sourceBranchName: $sourceBranchName,
                targetBranchName: $targetBranchName,
            ),
        );
    }

    public function mergeMergeRequest(ProjectId $projectId, MergeRequestIid $mergeRequestIid): MergeRequest
    {
        return $this->delegate(
            projectId: $projectId,
            callback: fn (MergeRequestManagerInterface $manager): MergeRequest => $manager->mergeMergeRequest(
                projectId: $projectId,
                mergeRequestIid: $mergeRequestIid,
            ),
        );
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

    public function mergeRequest(
        ProjectId $projectId,
        MergeRequestIid $mergeRequestIid,
    ): MergeRequest {
        return $this->delegate(
            projectId: $projectId,
            callback: fn (MergeRequestManagerInterface $manager): MergeRequest => $manager->mergeRequest(
                projectId: $projectId,
                mergeRequestIid: $mergeRequestIid,
            ),
        );
    }

    public function details(ProjectId $projectId, MergeRequestIid $mergeRequestIid): Details
    {
        return $this->delegate(
            projectId: $projectId,
            callback: fn (MergeRequestManagerInterface $manager): Details => $manager->details(
                projectId: $projectId,
                mergeRequestIid: $mergeRequestIid,
            ),
        );
    }

    private function delegate(ProjectId $projectId, callable $callback): mixed
    {
        foreach ($this->mergeRequestManagers as $manager) {
            if ($manager->supports($projectId)) {
                return $callback($manager, $projectId);
            }
        }

        throw new UnsupportedProjectException($projectId);
    }
}
