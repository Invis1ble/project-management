<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\Model\DevelopmentCollaboration\MergeRequest;

use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project\ProjectId;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequest;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequestManagerInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Title;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\UpdateExtraDeployBranchMergeRequestFactoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Branch;

final readonly class UpdateExtraDeployBranchMergeRequestFactory implements UpdateExtraDeployBranchMergeRequestFactoryInterface
{
    public function __construct(
        private ProjectId $projectId,
        private MergeRequestManagerInterface $mergeRequestManager,
        private ?Branch\Name $extraDeployBranchName,
    ) {
    }

    public function createMergeRequest(): ?MergeRequest
    {
        if (null === $this->extraDeployBranchName) {
            return null;
        }

        return $this->mergeRequestManager->createMergeRequest(
            projectId: $this->projectId,
            title: Title::fromString('Update from develop'),
            sourceBranchName: Branch\Name::fromString('develop'),
            targetBranchName: $this->extraDeployBranchName,
        );
    }
}
