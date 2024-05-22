<?php

declare(strict_types=1);

namespace ProjectManagement\Shared\Infrastructure\Model\DevelopmentCollaboration;

use Psr\Http\Message\UriFactoryInterface;
use ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project;
use ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequest;
use ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequestFactoryInterface;
use ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequestId;
use ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Name;
use ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Status;
use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Branch;

final readonly class MergeRequestFactory implements MergeRequestFactoryInterface
{
    public function __construct(private UriFactoryInterface $uriFactory)
    {
    }

    public function createMergeRequest(
        int $id,
        string $name,
        int $projectId,
        string $projectName,
        string $sourceBranchName,
        string $targetBranchName,
        string $status,
        string $guiUrl,
    ): MergeRequest {
        return new MergeRequest(
            id: MergeRequestId::from($id),
            name: Name::fromString($name),
            projectId: Project\ProjectId::from($projectId),
            projectName: Project\Name::fromString($projectName),
            sourceBranchName: Branch\Name::fromString($sourceBranchName),
            targetBranchName: Branch\Name::fromString($targetBranchName),
            status: Status::from($status),
            guiUrl: $this->uriFactory->createUri($guiUrl),
            details: null,
        );
    }
}
