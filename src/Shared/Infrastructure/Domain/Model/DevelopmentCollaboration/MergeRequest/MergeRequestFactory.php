<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\Model\DevelopmentCollaboration\MergeRequest;

use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequest;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequestFactoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequestId;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Status;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Title;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Branch;
use Psr\Http\Message\UriFactoryInterface;

final readonly class MergeRequestFactory implements MergeRequestFactoryInterface
{
    public function __construct(private UriFactoryInterface $uriFactory)
    {
    }

    public function createMergeRequest(
        int $id,
        string $title,
        int $projectId,
        string $projectName,
        string $sourceBranchName,
        string $targetBranchName,
        string $status,
        string $guiUrl,
    ): MergeRequest {
        return new MergeRequest(
            id: MergeRequestId::from($id),
            title: Title::fromString($title),
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
