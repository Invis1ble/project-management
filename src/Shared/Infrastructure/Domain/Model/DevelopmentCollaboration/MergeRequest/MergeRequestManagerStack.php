<?php

declare(strict_types=1);

namespace ReleaseManagement\Shared\Infrastructure\Domain\Model\DevelopmentCollaboration\MergeRequest;

use ReleaseManagement\Shared\Domain\Exception\UnsupportedProjectException;
use ReleaseManagement\Shared\Domain\Model\ContinuousIntegration\Project\ProjectId;
use ReleaseManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Details;
use ReleaseManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequestId;
use ReleaseManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequestManagerInterface;

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
                    "Merge Request Manager must be an instance of %s, %s given",
                    MergeRequestManagerInterface::class,
                    is_object($manager) ? $manager::class : gettype($manager),
                ));
            }

            $managers[] = $manager;
        }

        $this->mergeRequestManagers = $managers;
    }

    public function merge(ProjectId $projectId, MergeRequestId $mergeRequestId): Details
    {
        foreach ($this->mergeRequestManagers as $manager) {
            if ($manager->supports($projectId)) {
                return $manager->merge($projectId, $mergeRequestId);
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
