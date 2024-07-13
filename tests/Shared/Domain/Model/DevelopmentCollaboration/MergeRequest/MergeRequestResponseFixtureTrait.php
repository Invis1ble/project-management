<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Tests\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest;

use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Branch;
use Psr\Http\Message\UriInterface;

trait MergeRequestResponseFixtureTrait
{
    public function mergeRequestResponseFixture(
        Project\ProjectId $projectId,
        Project\Name $projectName,
        MergeRequest\MergeRequestIid $iid,
        MergeRequest\Title $title,
        Branch\Name $sourceBranchName,
        Branch\Name $targetBranchName,
        MergeRequest\Status $status,
        MergeRequest\Details\Status\Dictionary $detailedStatus,
        UriInterface $guiUrl,
    ): array {
        $mergeRequest = json_decode(
            file_get_contents(__DIR__ . '/fixture/response/merge_request.200.json'),
            true,
        );

        return [
            'iid' => $iid->value(),
            'project_id' => $projectId->value(),
            'project_name' => (string) $projectName,
            'title' => (string) $title,
            'source_branch' => (string) $sourceBranchName,
            'target_branch' => (string) $targetBranchName,
            'status' => $status->value,
            'detailed_merge_status' => $detailedStatus->value,
            'web_url' => (string) $guiUrl,
        ] + $mergeRequest;
    }
}
