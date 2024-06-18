<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Tests\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest;

use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Branch;
use Psr\Http\Message\UriFactoryInterface;

trait CreateMergeRequestsTrait
{
    public function createMergeRequests(
        UriFactoryInterface $uriFactory,
        int $id = 4,
        int $backendProjectId = 1,
        int $frontendProjectId = 2,
        string $projectName = 'my-group/my-project',
        string $title = 'Close PROJECT-2 Fix terrible bug',
        string $sourceBranchName = 'PROJECT-2',
        string $targetBranchName = 'master',
        MergeRequest\Status $jiraStatus = MergeRequest\Status::Open,
        MergeRequest\Details\Status\Dictionary $gitlabStatus = MergeRequest\Details\Status\Dictionary::Mergeable,
    ): MergeRequest\MergeRequestList {
        $mergeRequestId = MergeRequest\MergeRequestId::from($id);

        return new MergeRequest\MergeRequestList(
            new MergeRequest\MergeRequest(
                id: $mergeRequestId,
                title: MergeRequest\Title::fromString($title),
                projectId: Project\ProjectId::from($backendProjectId),
                projectName: Project\Name::fromString($projectName),
                sourceBranchName: Branch\Name::fromString($sourceBranchName),
                targetBranchName: Branch\Name::fromString($targetBranchName),
                status: $jiraStatus,
                guiUrl: $uriFactory->createUri("https://gitlab.example.com/$projectName/-/merge_requests/$mergeRequestId"),
                details: new MergeRequest\Details\Details(
                    status: MergeRequest\Details\Status\StatusFactory::createStatus($gitlabStatus),
                ),
            ),
            new MergeRequest\MergeRequest(
                id: $mergeRequestId,
                title: MergeRequest\Title::fromString($title),
                projectId: Project\ProjectId::from($frontendProjectId),
                projectName: Project\Name::fromString($projectName),
                sourceBranchName: Branch\Name::fromString($sourceBranchName),
                targetBranchName: Branch\Name::fromString($targetBranchName),
                status: $jiraStatus,
                guiUrl: $uriFactory->createUri("https://gitlab.example.com/$projectName/-/merge_requests/$mergeRequestId"),
                details: new MergeRequest\Details\Details(
                    status: MergeRequest\Details\Status\StatusFactory::createStatus($gitlabStatus),
                ),
            ),
        );
    }
}
