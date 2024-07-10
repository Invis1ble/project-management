<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Tests\Shared\Application\Saga;

use GuzzleHttp\Psr7\Response;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Pipeline;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Branch;
use Invis1ble\ProjectManagement\Tests\Shared\Domain\Model\ContinuousIntegration\Job\PipelineJobsResponseFixtureTrait;
use Invis1ble\ProjectManagement\Tests\Shared\Domain\Model\ContinuousIntegration\Job\PlayJobResponseFixtureTrait;
use Invis1ble\ProjectManagement\Tests\Shared\Domain\Model\ContinuousIntegration\Pipeline\PipelineResponseFixtureTrait;
use Invis1ble\ProjectManagement\Tests\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequestResponseFixtureTrait;
use Invis1ble\ProjectManagement\Tests\Shared\Domain\Model\SourceCodeRepository\Commit\CreateCommitResponseFixtureTrait;
use Invis1ble\ProjectManagement\Tests\Shared\Domain\Model\SourceCodeRepository\File\FileResponseFixtureTrait;
use Invis1ble\ProjectManagement\Tests\Shared\Domain\Model\SourceCodeRepository\Tag\CreateTagResponseFixtureTrait;
use Invis1ble\ProjectManagement\Tests\Shared\Domain\Model\TaskTracker\Issue\CreateIssuesTrait;
use Invis1ble\ProjectManagement\Tests\Shared\Domain\Model\TaskTracker\Issue\IssuesResponseFixtureTrait;
use Invis1ble\ProjectManagement\Tests\Shared\Domain\Model\TaskTracker\Issue\IssueTransitionsResponseFixtureTrait;
use Invis1ble\ProjectManagement\Tests\Shared\Domain\Model\TaskTracker\Issue\MapMergeRequestsToMergeToMergedTrait;
use Invis1ble\ProjectManagement\Tests\Shared\Domain\Model\TaskTracker\Version\VersionsResponseFixtureTrait;
use Psr\Http\Message\UriInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Clock\Test\ClockSensitiveTrait;

abstract class PublicationTestCase extends KernelTestCase
{
    use ClockSensitiveTrait;
    use CreateCommitResponseFixtureTrait;
    use CreateIssuesTrait;
    use CreateTagResponseFixtureTrait;
    use FileResponseFixtureTrait;
    use IssueTransitionsResponseFixtureTrait;
    use IssuesResponseFixtureTrait;
    use MapMergeRequestsToMergeToMergedTrait;
    use MergeRequestResponseFixtureTrait;
    use PipelineJobsResponseFixtureTrait;
    use PipelineResponseFixtureTrait;
    use PlayJobResponseFixtureTrait;
    use VersionsResponseFixtureTrait;

    protected function createPipelineResponse(
        Pipeline\PipelineId $pipelineId,
        Project\ProjectId $projectId,
        Project\Name $projectName,
        Pipeline\Status $status,
        \DateTimeImmutable $createdAt,
    ): Response {
        return new Response(
            status: 200,
            body: json_encode($this->pipelineResponseFixture(
                pipelineId: $pipelineId,
                projectId: $projectId,
                projectName: $projectName,
                status: $status,
                createdAt: $createdAt,
            )),
        );
    }

    protected function createMergeRequestResponse(
        MergeRequest\MergeRequestIid $mergeRequestIid,
        Project\ProjectId $projectId,
        Project\Name $projectName,
        MergeRequest\Title $title,
        Branch\Name $sourceBranchName,
        Branch\Name $targetBranchName,
        MergeRequest\Status $status,
        MergeRequest\Details\Status\Dictionary $detailedStatus,
        UriInterface $guiUrl,
    ): Response {
        return new Response(
            status: 200,
            body: json_encode($this->mergeRequestResponseFixture(
                projectId: $projectId,
                projectName: $projectName,
                iid: $mergeRequestIid,
                title: $title,
                sourceBranchName: $sourceBranchName,
                targetBranchName: $targetBranchName,
                status: $status,
                detailedStatus: $detailedStatus,
                guiUrl: $guiUrl,
            )),
        );
    }

    protected function createCreateMergeRequestResponse(
        MergeRequest\MergeRequestIid $mergeRequestIid,
        Project\ProjectId $projectId,
        Project\Name $projectName,
        MergeRequest\Title $title,
        Branch\Name $sourceBranchName,
        Branch\Name $targetBranchName,
        UriInterface $guiUrl,
    ): Response {
        return $this->createMergeRequestResponse(
            mergeRequestIid: $mergeRequestIid,
            projectId: $projectId,
            projectName: $projectName,
            title: $title,
            sourceBranchName: $sourceBranchName,
            targetBranchName: $targetBranchName,
            status: MergeRequest\Status::Open,
            detailedStatus: MergeRequest\Details\Status\Dictionary::Mergeable,
            guiUrl: $guiUrl,
        );
    }

    protected function createMergeMergeRequestResponse(
        MergeRequest\MergeRequestIid $mergeRequestIid,
        Project\ProjectId $projectId,
        Project\Name $projectName,
        MergeRequest\Title $title,
        Branch\Name $sourceBranchName,
        Branch\Name $targetBranchName,
        UriInterface $guiUrl,
    ): Response {
        return $this->createMergeRequestResponse(
            mergeRequestIid: $mergeRequestIid,
            projectId: $projectId,
            projectName: $projectName,
            title: $title,
            sourceBranchName: $sourceBranchName,
            targetBranchName: $targetBranchName,
            status: MergeRequest\Status::Merged,
            detailedStatus: MergeRequest\Details\Status\Dictionary::NotOpen,
            guiUrl: $guiUrl,
        );
    }
}
