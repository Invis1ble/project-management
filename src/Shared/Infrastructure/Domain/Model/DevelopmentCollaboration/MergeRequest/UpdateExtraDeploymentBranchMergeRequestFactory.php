<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\Model\DevelopmentCollaboration\MergeRequest;

use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project\ProjectId;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequest;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequestManagerInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Title;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\UpdateExtraDeploymentBranchMergeRequestFactoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Branch;

final readonly class UpdateExtraDeploymentBranchMergeRequestFactory implements UpdateExtraDeploymentBranchMergeRequestFactoryInterface
{
    public function __construct(
        private ProjectId $projectId,
        private MergeRequestManagerInterface $mergeRequestManager,
        private ?Branch\Name $extraDeploymentBranchName,
        private Branch\Name $developmentBranchName = new Branch\Name('develop'),
    ) {
    }

    public function createMergeRequest(): ?MergeRequest
    {
        if (null === $this->extraDeploymentBranchName) {
            return null;
        }

        return $this->mergeRequestManager->createMergeRequest(
            projectId: $this->projectId,
            title: Title::fromString("Update from $this->developmentBranchName"),
            sourceBranchName: $this->developmentBranchName,
            targetBranchName: $this->extraDeploymentBranchName,
        );
    }

    public function extraDeploymentBranchName(): ?Branch\Name
    {
        return $this->extraDeploymentBranchName;
    }

    public function developmentBranchName(): Branch\Name
    {
        return $this->developmentBranchName;
    }
}
