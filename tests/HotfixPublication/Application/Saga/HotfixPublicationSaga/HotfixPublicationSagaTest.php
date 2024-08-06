<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Tests\HotfixPublication\Application\Saga\HotfixPublicationSaga;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Invis1ble\Messenger\Command\CommandBusInterface;
use Invis1ble\Messenger\Event\TraceableEventBus;
use Invis1ble\ProjectManagement\HotfixPublication\Application\UseCase\Command\CreateHotfixPublication\CreateHotfixPublicationCommand;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Event\HotfixPublicationCreated;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Event\HotfixPublicationStatusChanged;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Event\TaskTracker\HotfixTransitionedToDone;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\HotfixPublicationId;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\Status\StatusCreated;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\Status\StatusDeploymentJobFailed;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\Status\StatusDeploymentJobInited;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\Status\StatusDeploymentJobPending;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\Status\StatusDeploymentJobSuccess;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\Status\StatusDevelopmentBranchSynchronized;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\Status\StatusDone;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\Status\StatusFrontendApplicationBranchSetToDevelopment;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\Status\StatusFrontendProductionReleaseBranchPipelineFailed;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\Status\StatusFrontendProductionReleaseBranchPipelinePending;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\Status\StatusFrontendProductionReleaseBranchPipelineSuccess;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\Status\StatusHotfixesTransitionedToDone;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\Status\StatusInterface;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\Status\StatusMergeRequestIntoExtraDeploymentBranchCreated;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\Status\StatusMergeRequestsIntoDevelopmentBranchCreated;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\Status\StatusMergeRequestsIntoReleaseBranchCreated;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\Status\StatusMergeRequestsMerged;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\Status\StatusReleaseBranchSynchronized;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\Status\StatusTagCreated;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\Status\StatusTagPipelineFailed;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\Status\StatusTagPipelinePending;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\Status\StatusTagPipelineSuccess;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Repository\HotfixPublicationRepositoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Event\ContinuousIntegration\Job\JobAwaitingTick;
use Invis1ble\ProjectManagement\Shared\Domain\Event\ContinuousIntegration\Job\JobRan;
use Invis1ble\ProjectManagement\Shared\Domain\Event\ContinuousIntegration\Job\JobRetried;
use Invis1ble\ProjectManagement\Shared\Domain\Event\ContinuousIntegration\Job\JobStatusChanged;
use Invis1ble\ProjectManagement\Shared\Domain\Event\ContinuousIntegration\Pipeline\LatestPipelineAwaitingTick;
use Invis1ble\ProjectManagement\Shared\Domain\Event\ContinuousIntegration\Pipeline\LatestPipelineStatusChanged;
use Invis1ble\ProjectManagement\Shared\Domain\Event\ContinuousIntegration\Pipeline\PipelineRetried;
use Invis1ble\ProjectManagement\Shared\Domain\Event\DevelopmentCollaboration\MergeRequest\MergeRequestAwaitingTick;
use Invis1ble\ProjectManagement\Shared\Domain\Event\DevelopmentCollaboration\MergeRequest\MergeRequestCreated;
use Invis1ble\ProjectManagement\Shared\Domain\Event\DevelopmentCollaboration\MergeRequest\MergeRequestMerged;
use Invis1ble\ProjectManagement\Shared\Domain\Event\DevelopmentCollaboration\MergeRequest\MergeRequestStatusChanged;
use Invis1ble\ProjectManagement\Shared\Domain\Event\SourceCodeRepository\Commit\CommitCreated;
use Invis1ble\ProjectManagement\Shared\Domain\Event\SourceCodeRepository\Tag\TagCreated;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Job;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Pipeline;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Branch;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Commit;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\File\Content;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Ref;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Tag;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Board\BoardId;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\IssueList;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Project;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Version;
use Invis1ble\ProjectManagement\Tests\Shared\Application\Saga\PublicationSagaTestCase;
use Invis1ble\ProjectManagement\Tests\Shared\Domain\Model\ContinuousIntegration\Job\RetryJobResponseFixtureTrait;
use Psr\Http\Message\UriFactoryInterface;

class HotfixPublicationSagaTest extends PublicationSagaTestCase
{
    use RetryJobResponseFixtureTrait;

    public function testHotfixPublication(): void
    {
        self::bootKernel();

        $container = static::getContainer();

        /** @var HotfixPublicationRepositoryInterface $hotfixPublicationRepository */
        $hotfixPublicationRepository = $container->get(HotfixPublicationRepositoryInterface::class);

        /** @var UriFactoryInterface $uriFactory */
        $uriFactory = $container->get(UriFactoryInterface::class);

        /** @var CommandBusInterface $commandBus */
        $commandBus = $container->get(CommandBusInterface::class);

        /** @var TraceableEventBus $eventBus */
        $eventBus = $container->get(TraceableEventBus::class);

        $hotfixes = $this->createIssues(
            uriFactory: $uriFactory,
        );

        $developmentBranchName = Branch\Name::fromString('develop');
        $productionReleaseBranchName = Branch\Name::fromString('master');

        $hotfixesArray = $hotfixes->toArray();
        $mrToMerge = $hotfixesArray[0]->mergeRequestsToMerge->toArray();
        $backendMrToMerge = $mrToMerge[0];
        $frontendMrToMerge = $mrToMerge[1];
        $backendProjectId = $backendMrToMerge->projectId;
        $frontendProjectId = $frontendMrToMerge->projectId;
        $backendProjectName = $backendMrToMerge->projectName;
        $frontendProjectName = $frontendMrToMerge->projectName;

        $tagName = Tag\VersionName::create();
        $tagMessage = Tag\Message::fromString("{$hotfixesArray[0]->summary} | {$hotfixesArray[0]->key}");

        $updateExtraDeployBranchMrIid = MergeRequest\MergeRequestIid::from(12345);
        $updateExtraDeployBranchMrTargetBranchName = $container->get('invis1ble_project_management.extra_deploy_branch_name');
        $updateExtraDeployBranchMrSourceBranchName = $developmentBranchName;
        $updateExtraDeployBranchMrTitle = MergeRequest\Title::fromString("Merge branch $developmentBranchName into $updateExtraDeployBranchMrTargetBranchName");

        $tagRef = Ref::fromString((string) $tagName);
        $now = new \DateTimeImmutable();
        $frontendPipelineCreatedAt = $now;
        $tagCreatedAt = $now->add(new \DateInterval('PT1M'));
        $setFrontendApplicationBranchNameCommitCreatedAt = $now->add(new \DateInterval('PT15M'));

        $deploymentJobId = Job\JobId::from(3);
        $deploymentJobName = Job\Name::fromString('Production-AWS');
        $deploymentJobCreatedAt = $tagCreatedAt->add(new \DateInterval('PT10M'));
        $pipelineJobsFixture = $this->pipelineJobsResponseFixture($deploymentJobName);

        $latestReleaseVersionName = Version\Name::fromString('v-1-0-0');

        $frontendPipelineId = Pipeline\PipelineId::from(1);
        $tagPipelineId = Pipeline\PipelineId::from(2);

        $mock = new MockHandler([
            $this->createMergeRequestResponse(
                mergeRequestIid: $backendMrToMerge->iid,
                projectId: $backendMrToMerge->projectId,
                projectName: $backendMrToMerge->projectName,
                title: $backendMrToMerge->title,
                sourceBranchName: $backendMrToMerge->sourceBranchName,
                targetBranchName: $backendMrToMerge->targetBranchName,
                status: MergeRequest\Status::Open,
                detailedStatus: MergeRequest\Details\Status\Dictionary::Mergeable,
                guiUrl: $backendMrToMerge->guiUrl,
            ),
            $this->createMergeMergeRequestResponse(
                mergeRequestIid: $backendMrToMerge->iid,
                projectId: $backendMrToMerge->projectId,
                projectName: $backendMrToMerge->projectName,
                title: $backendMrToMerge->title,
                sourceBranchName: $backendMrToMerge->sourceBranchName,
                targetBranchName: $backendMrToMerge->targetBranchName,
                guiUrl: $backendMrToMerge->guiUrl,
            ),
            $this->createMergeRequestResponse(
                mergeRequestIid: $frontendMrToMerge->iid,
                projectId: $frontendMrToMerge->projectId,
                projectName: $frontendMrToMerge->projectName,
                title: $frontendMrToMerge->title,
                sourceBranchName: $frontendMrToMerge->sourceBranchName,
                targetBranchName: $frontendMrToMerge->targetBranchName,
                status: MergeRequest\Status::Open,
                detailedStatus: MergeRequest\Details\Status\Dictionary::Mergeable,
                guiUrl: $frontendMrToMerge->guiUrl,
            ),
            $this->createMergeMergeRequestResponse(
                mergeRequestIid: $frontendMrToMerge->iid,
                projectId: $frontendMrToMerge->projectId,
                projectName: $frontendMrToMerge->projectName,
                title: $frontendMrToMerge->title,
                sourceBranchName: $frontendMrToMerge->sourceBranchName,
                targetBranchName: $frontendMrToMerge->targetBranchName,
                guiUrl: $frontendMrToMerge->guiUrl,
            ),
            new Response(
                status: 403,
                body: json_encode([
                    'message' => '403 Forbidden',
                ]),
            ),
            $this->createPipelineResponse(
                pipelineId: $frontendPipelineId,
                projectId: $frontendProjectId,
                projectName: $frontendProjectName,
                status: Pipeline\Status::Created,
                createdAt: $frontendPipelineCreatedAt,
            ),
            $this->createPipelineResponse(
                pipelineId: $frontendPipelineId,
                projectId: $frontendProjectId,
                projectName: $frontendProjectName,
                status: Pipeline\Status::WaitingForResource,
                createdAt: $frontendPipelineCreatedAt,
            ),
            $this->createPipelineResponse(
                pipelineId: $frontendPipelineId,
                projectId: $frontendProjectId,
                projectName: $frontendProjectName,
                status: Pipeline\Status::Preparing,
                createdAt: $frontendPipelineCreatedAt,
            ),
            $this->createPipelineResponse(
                pipelineId: $frontendPipelineId,
                projectId: $frontendProjectId,
                projectName: $frontendProjectName,
                status: Pipeline\Status::Pending,
                createdAt: $frontendPipelineCreatedAt,
            ),
            $this->createPipelineResponse(
                pipelineId: $frontendPipelineId,
                projectId: $frontendProjectId,
                projectName: $frontendProjectName,
                status: Pipeline\Status::Running,
                createdAt: $frontendPipelineCreatedAt,
            ),
            $this->createPipelineResponse(
                pipelineId: $frontendPipelineId,
                projectId: $frontendProjectId,
                projectName: $frontendProjectName,
                status: Pipeline\Status::Failed,
                createdAt: $frontendPipelineCreatedAt,
            ),
            $this->createPipelineResponse(
                pipelineId: $frontendPipelineId,
                projectId: $frontendProjectId,
                projectName: $frontendProjectName,
                status: Pipeline\Status::Pending,
                createdAt: $frontendPipelineCreatedAt,
            ),
            $this->createPipelineResponse(
                pipelineId: $frontendPipelineId,
                projectId: $frontendProjectId,
                projectName: $frontendProjectName,
                status: Pipeline\Status::Pending,
                createdAt: $frontendPipelineCreatedAt,
            ),
            $this->createPipelineResponse(
                pipelineId: $frontendPipelineId,
                projectId: $frontendProjectId,
                projectName: $frontendProjectName,
                status: Pipeline\Status::Running,
                createdAt: $frontendPipelineCreatedAt,
            ),
            $this->createPipelineResponse(
                pipelineId: $frontendPipelineId,
                projectId: $frontendProjectId,
                projectName: $frontendProjectName,
                status: Pipeline\Status::Success,
                createdAt: $frontendPipelineCreatedAt,
            ),
            new Response(
                status: 200,
                body: json_encode($this->createTagResponseFixture(
                    tagName: $tagName,
                    tagMessage: $tagMessage,
                    commitMessage: Commit\Message::fromString((string) $backendMrToMerge->title),
                    commitCreatedAt: $frontendPipelineCreatedAt,
                    tagCreatedAt: $tagCreatedAt,
                )),
            ),
            new Response(
                status: 403,
                body: json_encode([
                    'message' => '403 Forbidden',
                ]),
            ),
            $this->createPipelineResponse(
                pipelineId: $tagPipelineId,
                projectId: $backendProjectId,
                projectName: $backendProjectName,
                status: Pipeline\Status::Created,
                createdAt: $tagCreatedAt,
            ),
            $this->createPipelineResponse(
                pipelineId: $tagPipelineId,
                projectId: $backendProjectId,
                projectName: $backendProjectName,
                status: Pipeline\Status::WaitingForResource,
                createdAt: $tagCreatedAt,
            ),
            $this->createPipelineResponse(
                pipelineId: $tagPipelineId,
                projectId: $backendProjectId,
                projectName: $backendProjectName,
                status: Pipeline\Status::Preparing,
                createdAt: $tagCreatedAt,
            ),
            $this->createPipelineResponse(
                pipelineId: $tagPipelineId,
                projectId: $backendProjectId,
                projectName: $backendProjectName,
                status: Pipeline\Status::Pending,
                createdAt: $tagCreatedAt,
            ),
            $this->createPipelineResponse(
                pipelineId: $tagPipelineId,
                projectId: $backendProjectId,
                projectName: $backendProjectName,
                status: Pipeline\Status::Running,
                createdAt: $tagCreatedAt,
            ),
            $this->createPipelineResponse(
                pipelineId: $tagPipelineId,
                projectId: $backendProjectId,
                projectName: $frontendProjectName,
                status: Pipeline\Status::Failed,
                createdAt: $frontendPipelineCreatedAt,
            ),
            $this->createPipelineResponse(
                pipelineId: $tagPipelineId,
                projectId: $backendProjectId,
                projectName: $frontendProjectName,
                status: Pipeline\Status::Pending,
                createdAt: $frontendPipelineCreatedAt,
            ),
            $this->createPipelineResponse(
                pipelineId: $tagPipelineId,
                projectId: $backendProjectId,
                projectName: $frontendProjectName,
                status: Pipeline\Status::Pending,
                createdAt: $frontendPipelineCreatedAt,
            ),
            $this->createPipelineResponse(
                pipelineId: $tagPipelineId,
                projectId: $backendProjectId,
                projectName: $frontendProjectName,
                status: Pipeline\Status::Running,
                createdAt: $frontendPipelineCreatedAt,
            ),
            $this->createPipelineResponse(
                pipelineId: $tagPipelineId,
                projectId: $backendProjectId,
                projectName: $backendProjectName,
                status: Pipeline\Status::Success,
                createdAt: $tagCreatedAt,
            ),
            $this->createPipelineResponse(
                pipelineId: $tagPipelineId,
                projectId: $backendProjectId,
                projectName: $backendProjectName,
                status: Pipeline\Status::Success,
                createdAt: $tagCreatedAt,
            ),
            new Response(
                status: 200,
                body: json_encode($pipelineJobsFixture),
            ),
            new Response(
                status: 200,
                body: json_encode($this->playJobResponseFixture(
                    jobId: $deploymentJobId,
                    jobName: $deploymentJobName,
                    ref: $tagRef,
                    status: Job\Status\Dictionary::Created,
                    createdAt: $deploymentJobCreatedAt,
                )),
            ),
            new Response(
                status: 403,
                body: json_encode([
                    'message' => '403 Forbidden',
                ]),
            ),
            $this->createJobResponse(
                jobId: $deploymentJobId,
                jobName: $deploymentJobName,
                ref: $tagRef,
                status: Job\Status\Dictionary::WaitingForResource,
                pipelineId: $tagPipelineId,
                createdAt: $deploymentJobCreatedAt,
            ),
            $this->createJobResponse(
                jobId: $deploymentJobId,
                jobName: $deploymentJobName,
                ref: $tagRef,
                status: Job\Status\Dictionary::Preparing,
                pipelineId: $tagPipelineId,
                createdAt: $deploymentJobCreatedAt,
            ),
            $this->createJobResponse(
                jobId: $deploymentJobId,
                jobName: $deploymentJobName,
                ref: $tagRef,
                status: Job\Status\Dictionary::Pending,
                pipelineId: $tagPipelineId,
                createdAt: $deploymentJobCreatedAt,
            ),
            $this->createJobResponse(
                jobId: $deploymentJobId,
                jobName: $deploymentJobName,
                ref: $tagRef,
                status: Job\Status\Dictionary::Running,
                pipelineId: $tagPipelineId,
                createdAt: $deploymentJobCreatedAt,
            ),
            $this->createJobResponse(
                jobId: $deploymentJobId,
                jobName: $deploymentJobName,
                ref: $tagRef,
                status: Job\Status\Dictionary::Failed,
                pipelineId: $tagPipelineId,
                createdAt: $deploymentJobCreatedAt,
            ),
            new Response(
                status: 200,
                body: json_encode($this->retryJobResponseFixture(
                    jobId: $deploymentJobId,
                    jobName: $deploymentJobName,
                    ref: $tagRef,
                    status: Job\Status\Dictionary::Pending,
                    createdAt: $deploymentJobCreatedAt,
                )),
            ),
            $this->createJobResponse(
                jobId: $deploymentJobId,
                jobName: $deploymentJobName,
                ref: $tagRef,
                status: Job\Status\Dictionary::Pending,
                pipelineId: $tagPipelineId,
                createdAt: $deploymentJobCreatedAt,
            ),
            $this->createJobResponse(
                jobId: $deploymentJobId,
                jobName: $deploymentJobName,
                ref: $tagRef,
                status: Job\Status\Dictionary::Running,
                pipelineId: $tagPipelineId,
                createdAt: $deploymentJobCreatedAt,
            ),
            $this->createJobResponse(
                jobId: $deploymentJobId,
                jobName: $deploymentJobName,
                ref: $tagRef,
                status: Job\Status\Dictionary::Success,
                pipelineId: $tagPipelineId,
                createdAt: $deploymentJobCreatedAt,
            ),
            $this->createMergeRequestResponse(
                mergeRequestIid: $backendMrToMerge->iid,
                projectId: $backendMrToMerge->projectId,
                projectName: $backendMrToMerge->projectName,
                title: $backendMrToMerge->title,
                sourceBranchName: $backendMrToMerge->sourceBranchName,
                targetBranchName: $developmentBranchName,
                status: MergeRequest\Status::Open,
                detailedStatus: MergeRequest\Details\Status\Dictionary::NotOpen,
                guiUrl: $backendMrToMerge->guiUrl,
            ),
            $this->createMergeRequestResponse(
                mergeRequestIid: $frontendMrToMerge->iid,
                projectId: $frontendMrToMerge->projectId,
                projectName: $frontendMrToMerge->projectName,
                title: $frontendMrToMerge->title,
                sourceBranchName: $frontendMrToMerge->sourceBranchName,
                targetBranchName: $developmentBranchName,
                status: MergeRequest\Status::Open,
                detailedStatus: MergeRequest\Details\Status\Dictionary::NotOpen,
                guiUrl: $frontendMrToMerge->guiUrl,
            ),
            $this->createMergeRequestResponse(
                mergeRequestIid: $backendMrToMerge->iid,
                projectId: $backendMrToMerge->projectId,
                projectName: $backendMrToMerge->projectName,
                title: $backendMrToMerge->title,
                sourceBranchName: $backendMrToMerge->sourceBranchName,
                targetBranchName: $developmentBranchName,
                status: MergeRequest\Status::Open,
                detailedStatus: MergeRequest\Details\Status\Dictionary::NotOpen,
                guiUrl: $backendMrToMerge->guiUrl,
            ),
            $this->createMergeRequestResponse(
                mergeRequestIid: $backendMrToMerge->iid,
                projectId: $backendMrToMerge->projectId,
                projectName: $backendMrToMerge->projectName,
                title: $backendMrToMerge->title,
                sourceBranchName: $backendMrToMerge->sourceBranchName,
                targetBranchName: $developmentBranchName,
                status: MergeRequest\Status::Open,
                detailedStatus: MergeRequest\Details\Status\Dictionary::Preparing,
                guiUrl: $backendMrToMerge->guiUrl,
            ),
            $this->createMergeRequestResponse(
                mergeRequestIid: $backendMrToMerge->iid,
                projectId: $backendMrToMerge->projectId,
                projectName: $backendMrToMerge->projectName,
                title: $backendMrToMerge->title,
                sourceBranchName: $backendMrToMerge->sourceBranchName,
                targetBranchName: $developmentBranchName,
                status: MergeRequest\Status::Open,
                detailedStatus: MergeRequest\Details\Status\Dictionary::CiStillRunning,
                guiUrl: $backendMrToMerge->guiUrl,
            ),
            $this->createMergeRequestResponse(
                mergeRequestIid: $backendMrToMerge->iid,
                projectId: $backendMrToMerge->projectId,
                projectName: $backendMrToMerge->projectName,
                title: $backendMrToMerge->title,
                sourceBranchName: $backendMrToMerge->sourceBranchName,
                targetBranchName: $developmentBranchName,
                status: MergeRequest\Status::Open,
                detailedStatus: MergeRequest\Details\Status\Dictionary::Mergeable,
                guiUrl: $backendMrToMerge->guiUrl,
            ),
            $this->createMergeMergeRequestResponse(
                mergeRequestIid: $backendMrToMerge->iid,
                projectId: $backendMrToMerge->projectId,
                projectName: $backendMrToMerge->projectName,
                title: $backendMrToMerge->title,
                sourceBranchName: $backendMrToMerge->sourceBranchName,
                targetBranchName: $developmentBranchName,
                guiUrl: $backendMrToMerge->guiUrl,
            ),
            $this->createMergeRequestResponse(
                mergeRequestIid: $frontendMrToMerge->iid,
                projectId: $frontendMrToMerge->projectId,
                projectName: $frontendMrToMerge->projectName,
                title: $frontendMrToMerge->title,
                sourceBranchName: $frontendMrToMerge->sourceBranchName,
                targetBranchName: $developmentBranchName,
                status: MergeRequest\Status::Open,
                detailedStatus: MergeRequest\Details\Status\Dictionary::NotOpen,
                guiUrl: $frontendMrToMerge->guiUrl,
            ),
            $this->createMergeRequestResponse(
                mergeRequestIid: $frontendMrToMerge->iid,
                projectId: $frontendMrToMerge->projectId,
                projectName: $frontendMrToMerge->projectName,
                title: $frontendMrToMerge->title,
                sourceBranchName: $frontendMrToMerge->sourceBranchName,
                targetBranchName: $developmentBranchName,
                status: MergeRequest\Status::Open,
                detailedStatus: MergeRequest\Details\Status\Dictionary::Preparing,
                guiUrl: $frontendMrToMerge->guiUrl,
            ),
            $this->createMergeRequestResponse(
                mergeRequestIid: $frontendMrToMerge->iid,
                projectId: $frontendMrToMerge->projectId,
                projectName: $frontendMrToMerge->projectName,
                title: $frontendMrToMerge->title,
                sourceBranchName: $frontendMrToMerge->sourceBranchName,
                targetBranchName: $developmentBranchName,
                status: MergeRequest\Status::Open,
                detailedStatus: MergeRequest\Details\Status\Dictionary::Mergeable,
                guiUrl: $frontendMrToMerge->guiUrl,
            ),
            $this->createMergeMergeRequestResponse(
                mergeRequestIid: $frontendMrToMerge->iid,
                projectId: $frontendMrToMerge->projectId,
                projectName: $frontendMrToMerge->projectName,
                title: $frontendMrToMerge->title,
                sourceBranchName: $frontendMrToMerge->sourceBranchName,
                targetBranchName: $developmentBranchName,
                guiUrl: $frontendMrToMerge->guiUrl,
            ),
            $this->createMergeRequestResponse(
                mergeRequestIid: $backendMrToMerge->iid,
                projectId: $backendMrToMerge->projectId,
                projectName: $backendMrToMerge->projectName,
                title: $backendMrToMerge->title,
                sourceBranchName: $backendMrToMerge->sourceBranchName,
                targetBranchName: Branch\Name::fromString((string) $latestReleaseVersionName),
                status: MergeRequest\Status::Open,
                detailedStatus: MergeRequest\Details\Status\Dictionary::NotOpen,
                guiUrl: $frontendMrToMerge->guiUrl,
            ),
            $this->createMergeRequestResponse(
                mergeRequestIid: $frontendMrToMerge->iid,
                projectId: $frontendMrToMerge->projectId,
                projectName: $frontendMrToMerge->projectName,
                title: $frontendMrToMerge->title,
                sourceBranchName: $frontendMrToMerge->sourceBranchName,
                targetBranchName: Branch\Name::fromString((string) $latestReleaseVersionName),
                status: MergeRequest\Status::Open,
                detailedStatus: MergeRequest\Details\Status\Dictionary::NotOpen,
                guiUrl: $frontendMrToMerge->guiUrl,
            ),
            $this->createMergeRequestResponse(
                mergeRequestIid: $backendMrToMerge->iid,
                projectId: $backendMrToMerge->projectId,
                projectName: $backendMrToMerge->projectName,
                title: $backendMrToMerge->title,
                sourceBranchName: $backendMrToMerge->sourceBranchName,
                targetBranchName: Branch\Name::fromString((string) $latestReleaseVersionName),
                status: MergeRequest\Status::Open,
                detailedStatus: MergeRequest\Details\Status\Dictionary::Preparing,
                guiUrl: $frontendMrToMerge->guiUrl,
            ),
            $this->createMergeRequestResponse(
                mergeRequestIid: $backendMrToMerge->iid,
                projectId: $backendMrToMerge->projectId,
                projectName: $backendMrToMerge->projectName,
                title: $backendMrToMerge->title,
                sourceBranchName: $backendMrToMerge->sourceBranchName,
                targetBranchName: Branch\Name::fromString((string) $latestReleaseVersionName),
                status: MergeRequest\Status::Open,
                detailedStatus: MergeRequest\Details\Status\Dictionary::Mergeable,
                guiUrl: $frontendMrToMerge->guiUrl,
            ),
            $this->createMergeMergeRequestResponse(
                mergeRequestIid: $backendMrToMerge->iid,
                projectId: $backendMrToMerge->projectId,
                projectName: $backendMrToMerge->projectName,
                title: $backendMrToMerge->title,
                sourceBranchName: $backendMrToMerge->sourceBranchName,
                targetBranchName: Branch\Name::fromString((string) $latestReleaseVersionName),
                guiUrl: $backendMrToMerge->guiUrl,
            ),
            $this->createMergeRequestResponse(
                mergeRequestIid: $frontendMrToMerge->iid,
                projectId: $frontendMrToMerge->projectId,
                projectName: $frontendMrToMerge->projectName,
                title: $frontendMrToMerge->title,
                sourceBranchName: $frontendMrToMerge->sourceBranchName,
                targetBranchName: Branch\Name::fromString((string) $latestReleaseVersionName),
                status: MergeRequest\Status::Open,
                detailedStatus: MergeRequest\Details\Status\Dictionary::Preparing,
                guiUrl: $frontendMrToMerge->guiUrl,
            ),
            $this->createMergeRequestResponse(
                mergeRequestIid: $frontendMrToMerge->iid,
                projectId: $frontendMrToMerge->projectId,
                projectName: $frontendMrToMerge->projectName,
                title: $frontendMrToMerge->title,
                sourceBranchName: $frontendMrToMerge->sourceBranchName,
                targetBranchName: Branch\Name::fromString((string) $latestReleaseVersionName),
                status: MergeRequest\Status::Open,
                detailedStatus: MergeRequest\Details\Status\Dictionary::Mergeable,
                guiUrl: $frontendMrToMerge->guiUrl,
            ),
            $this->createMergeMergeRequestResponse(
                mergeRequestIid: $frontendMrToMerge->iid,
                projectId: $frontendMrToMerge->projectId,
                projectName: $frontendMrToMerge->projectName,
                title: $frontendMrToMerge->title,
                sourceBranchName: $frontendMrToMerge->sourceBranchName,
                targetBranchName: Branch\Name::fromString((string) $latestReleaseVersionName),
                guiUrl: $frontendMrToMerge->guiUrl,
            ),
            new Response(
                status: 200,
                body: json_encode($this->fileResponseFixture(
                    content: Content::fromString(<<<CONFIG
Deploy_react:
    host:
        _default: "v-1-0-0"
CONFIG),
                )),
            ),
            new Response(
                status: 200,
                body: json_encode($this->createCommitResponseFixture(
                    message: Commit\Message::fromString("Change frontend application branch name to $developmentBranchName"),
                    createdAt: $setFrontendApplicationBranchNameCommitCreatedAt,
                )),
            ),
            $this->createMergeRequestResponse(
                mergeRequestIid: $updateExtraDeployBranchMrIid,
                projectId: $backendProjectId,
                projectName: $backendProjectName,
                title: $updateExtraDeployBranchMrTitle,
                sourceBranchName: $updateExtraDeployBranchMrSourceBranchName,
                targetBranchName: $updateExtraDeployBranchMrTargetBranchName,
                status: MergeRequest\Status::Open,
                detailedStatus: MergeRequest\Details\Status\Dictionary::NotOpen,
                guiUrl: $frontendMrToMerge->guiUrl,
            ),
            $this->createMergeRequestResponse(
                mergeRequestIid: $updateExtraDeployBranchMrIid,
                projectId: $backendProjectId,
                projectName: $backendProjectName,
                title: $updateExtraDeployBranchMrTitle,
                sourceBranchName: $updateExtraDeployBranchMrSourceBranchName,
                targetBranchName: $updateExtraDeployBranchMrTargetBranchName,
                status: MergeRequest\Status::Open,
                detailedStatus: MergeRequest\Details\Status\Dictionary::Preparing,
                guiUrl: $frontendMrToMerge->guiUrl,
            ),
            $this->createMergeRequestResponse(
                mergeRequestIid: $updateExtraDeployBranchMrIid,
                projectId: $backendProjectId,
                projectName: $backendProjectName,
                title: $updateExtraDeployBranchMrTitle,
                sourceBranchName: $updateExtraDeployBranchMrSourceBranchName,
                targetBranchName: $updateExtraDeployBranchMrTargetBranchName,
                status: MergeRequest\Status::Open,
                detailedStatus: MergeRequest\Details\Status\Dictionary::Mergeable,
                guiUrl: $frontendMrToMerge->guiUrl,
            ),
            $this->createMergeMergeRequestResponse(
                mergeRequestIid: $updateExtraDeployBranchMrIid,
                projectId: $backendProjectId,
                projectName: $backendProjectName,
                title: $updateExtraDeployBranchMrTitle,
                sourceBranchName: $updateExtraDeployBranchMrSourceBranchName,
                targetBranchName: $updateExtraDeployBranchMrTargetBranchName,
                guiUrl: $backendMrToMerge->guiUrl,
            ),
        ]);

        /** @var Client $httpClient */
        $httpClient = $container->get('eight_points_guzzle.client.gitlab');
        /** @var HandlerStack $handlerStack */
        $handlerStack = $httpClient->getConfig('handler');
        $handlerStack->setHandler($mock);

        $mock = new MockHandler([
            new Response(
                status: 200,
                body: json_encode($this->issuesResponseFixture(
                    issueKey: $hotfixesArray[0]->key,
                    issueSummary: $hotfixesArray[0]->summary,
                    issueBoardId: $container->get(BoardId::class),
                    sprintFieldId: $container->getParameter('invis1ble_project_management.jira.sprint_filed_id'),
                )),
            ),
            new Response(
                status: 200,
                body: json_encode($this->issueTransitionsResponseFixture(
                    transitionName: $container->getParameter('invis1ble_project_management.jira.hotfix_transition_to_done'),
                )),
            ),
            new Response(
                status: 204,
            ),
            new Response(
                status: 200,
                body: json_encode($this->versionsResponseFixture(
                    latestVersionName: $latestReleaseVersionName,
                )),
            ),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $container->set('eight_points_guzzle.client.jira', new Client(['handler' => $handlerStack]));

        $createPublicationCommand = new CreateHotfixPublicationCommand(
            tagName: $tagName,
            tagMessage: $tagMessage,
            hotfixes: $hotfixes,
        );

        static::mockTime($now->sub(new \DateInterval('PT1M')));

        $commandBus->dispatch($createPublicationCommand);

        $publication = $hotfixPublicationRepository->get(
            HotfixPublicationId::fromVersionName($createPublicationCommand->tagName),
        );

        $expectedHotfixes = $this->mapMergeRequestsToMergeToMerged($createPublicationCommand->hotfixes);
        $expectedHotfixes = $this->addCopiesWithNewTargetBranchToMergeRequestsToMerge(
            issues: $expectedHotfixes,
            targetBranchName: $productionReleaseBranchName,
            newTargetBranchName: $developmentBranchName,
        );
        $expectedHotfixes = $this->addCopiesWithNewTargetBranchToMergeRequestsToMerge(
            issues: $expectedHotfixes,
            targetBranchName: $productionReleaseBranchName,
            newTargetBranchName: Branch\Name::fromString((string) $latestReleaseVersionName),
        );
        $expectedMrsToMerge = $expectedHotfixes->toArray()[0]->mergeRequestsToMerge->toArray();

        $this->assertObjectEquals($createPublicationCommand->tagName, $publication->tagName());
        $this->assertObjectEquals($createPublicationCommand->tagMessage, $publication->tagMessage());
        $this->assertObjectEquals($expectedHotfixes, $publication->hotfixes());
        $this->assertObjectEquals(new StatusDone(), $publication->status());

        $dispatchedEvents = $eventBus->getDispatchedEvents();

        $this->assertCount(102, $dispatchedEvents);

        $this->assertArrayHasKey(0, $dispatchedEvents);
        $event = $dispatchedEvents[0]->event;
        $this->assertInstanceOf(HotfixPublicationCreated::class, $event);
        $this->assertObjectEquals(new StatusCreated(), $event->status);
        $this->assertObjectEquals($createPublicationCommand->hotfixes, $event->hotfixes);

        $this->assertArrayHasKey(1, $dispatchedEvents);
        $event = $dispatchedEvents[1]->event;
        $this->assertInstanceOf(MergeRequestMerged::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals($expectedMrsToMerge[0]->iid, $event->mergeRequestIid);
        $this->assertObjectEquals($expectedMrsToMerge[0]->title, $event->title);
        $this->assertObjectEquals($expectedMrsToMerge[0]->sourceBranchName, $event->sourceBranchName);
        $this->assertObjectEquals($expectedMrsToMerge[0]->targetBranchName, $event->targetBranchName);
        $this->assertObjectEquals($expectedMrsToMerge[0]->details, $event->details);

        $this->assertArrayHasKey(2, $dispatchedEvents);
        $event = $dispatchedEvents[2]->event;
        $this->assertInstanceOf(MergeRequestMerged::class, $event);
        $this->assertObjectEquals($frontendProjectId, $event->projectId);
        $this->assertObjectEquals($expectedMrsToMerge[1]->iid, $event->mergeRequestIid);
        $this->assertObjectEquals($expectedMrsToMerge[1]->title, $event->title);
        $this->assertObjectEquals($expectedMrsToMerge[1]->sourceBranchName, $event->sourceBranchName);
        $this->assertObjectEquals($expectedMrsToMerge[1]->targetBranchName, $event->targetBranchName);
        $this->assertObjectEquals($expectedMrsToMerge[1]->details, $event->details);

        $this->assertArrayHasKey(3, $dispatchedEvents);
        $this->assertHotfixPublicationStatusChanged(
            event: $dispatchedEvents[3]->event,
            expectedPreviousStatus: new StatusCreated(),
            expectedStatus: new StatusMergeRequestsMerged(),
        );

        $this->assertArrayHasKey(4, $dispatchedEvents);
        $event = $dispatchedEvents[4]->event;
        $this->assertInstanceOf(LatestPipelineStatusChanged::class, $event);
        $this->assertObjectEquals($expectedMrsToMerge[1]->projectId, $event->projectId);
        $this->assertNull($event->previousStatus);
        $this->assertObjectEquals(Pipeline\Status::Created, $event->status);

        $this->assertArrayHasKey(5, $dispatchedEvents);
        $event = $dispatchedEvents[5]->event;
        $this->assertInstanceOf(LatestPipelineAwaitingTick::class, $event);
        $this->assertObjectEquals($frontendProjectId, $event->projectId);
        $this->assertObjectEquals($frontendPipelineId, $event->pipelineId);
        $this->assertObjectEquals(Pipeline\Status::Created, $event->status);

        $this->assertArrayHasKey(6, $dispatchedEvents);
        $event = $dispatchedEvents[6]->event;
        $this->assertInstanceOf(LatestPipelineStatusChanged::class, $event);
        $this->assertObjectEquals($frontendProjectId, $event->projectId);
        $this->assertObjectEquals(Pipeline\Status::Created, $event->previousStatus);
        $this->assertObjectEquals(Pipeline\Status::WaitingForResource, $event->status);

        $this->assertArrayHasKey(7, $dispatchedEvents);
        $event = $dispatchedEvents[7]->event;
        $this->assertInstanceOf(LatestPipelineAwaitingTick::class, $event);
        $this->assertObjectEquals($frontendProjectId, $event->projectId);
        $this->assertObjectEquals($frontendPipelineId, $event->pipelineId);
        $this->assertObjectEquals(Pipeline\Status::WaitingForResource, $event->status);

        $this->assertArrayHasKey(8, $dispatchedEvents);
        $event = $dispatchedEvents[8]->event;
        $this->assertInstanceOf(LatestPipelineStatusChanged::class, $event);
        $this->assertObjectEquals($frontendProjectId, $event->projectId);
        $this->assertObjectEquals(Pipeline\Status::WaitingForResource, $event->previousStatus);
        $this->assertObjectEquals(Pipeline\Status::Preparing, $event->status);

        $this->assertArrayHasKey(9, $dispatchedEvents);
        $event = $dispatchedEvents[9]->event;
        $this->assertInstanceOf(LatestPipelineAwaitingTick::class, $event);
        $this->assertObjectEquals($frontendProjectId, $event->projectId);
        $this->assertObjectEquals($frontendPipelineId, $event->pipelineId);
        $this->assertObjectEquals(Pipeline\Status::Preparing, $event->status);

        $this->assertArrayHasKey(10, $dispatchedEvents);
        $event = $dispatchedEvents[10]->event;
        $this->assertInstanceOf(LatestPipelineStatusChanged::class, $event);
        $this->assertObjectEquals($frontendProjectId, $event->projectId);
        $this->assertObjectEquals(Pipeline\Status::Preparing, $event->previousStatus);
        $this->assertObjectEquals(Pipeline\Status::Pending, $event->status);

        $this->assertArrayHasKey(11, $dispatchedEvents);
        $event = $dispatchedEvents[11]->event;
        $this->assertInstanceOf(LatestPipelineAwaitingTick::class, $event);
        $this->assertObjectEquals($frontendProjectId, $event->projectId);
        $this->assertObjectEquals($frontendPipelineId, $event->pipelineId);
        $this->assertObjectEquals(Pipeline\Status::Pending, $event->status);

        $this->assertArrayHasKey(12, $dispatchedEvents);
        $event = $dispatchedEvents[12]->event;
        $this->assertInstanceOf(LatestPipelineStatusChanged::class, $event);
        $this->assertObjectEquals($frontendProjectId, $event->projectId);
        $this->assertObjectEquals(Pipeline\Status::Pending, $event->previousStatus);
        $this->assertObjectEquals(Pipeline\Status::Running, $event->status);

        $this->assertArrayHasKey(13, $dispatchedEvents);
        $event = $dispatchedEvents[13]->event;
        $this->assertInstanceOf(LatestPipelineAwaitingTick::class, $event);
        $this->assertObjectEquals($frontendProjectId, $event->projectId);
        $this->assertObjectEquals($frontendPipelineId, $event->pipelineId);
        $this->assertObjectEquals(Pipeline\Status::Running, $event->status);

        $this->assertArrayHasKey(14, $dispatchedEvents);
        $event = $dispatchedEvents[14]->event;
        $this->assertInstanceOf(LatestPipelineStatusChanged::class, $event);
        $this->assertObjectEquals($frontendProjectId, $event->projectId);
        $this->assertObjectEquals(Pipeline\Status::Running, $event->previousStatus);
        $this->assertObjectEquals(Pipeline\Status::Failed, $event->status);

        $this->assertArrayHasKey(15, $dispatchedEvents);
        $this->assertHotfixPublicationStatusChanged(
            event: $dispatchedEvents[15]->event,
            expectedPreviousStatus: new StatusMergeRequestsMerged(),
            expectedStatus: new StatusFrontendProductionReleaseBranchPipelineFailed([
                'pipeline_id' => $frontendPipelineId->value(),
            ]),
        );

        $this->assertArrayHasKey(16, $dispatchedEvents);
        $event = $dispatchedEvents[16]->event;
        $this->assertInstanceOf(PipelineRetried::class, $event);
        $this->assertObjectEquals($frontendProjectId, $event->projectId);
        $this->assertObjectEquals($frontendPipelineId, $event->pipelineId);
        $this->assertObjectEquals(Pipeline\Status::Pending, $event->status);

        $this->assertArrayHasKey(17, $dispatchedEvents);
        $this->assertHotfixPublicationStatusChanged(
            event: $dispatchedEvents[17]->event,
            expectedPreviousStatus: new StatusFrontendProductionReleaseBranchPipelineFailed([
                'pipeline_id' => $frontendPipelineId->value(),
            ]),
            expectedStatus: new StatusFrontendProductionReleaseBranchPipelinePending([
                'retry_counter' => 1,
                'pipeline_id' => $frontendPipelineId->value(),
            ]),
        );

        $this->assertArrayHasKey(18, $dispatchedEvents);
        $event = $dispatchedEvents[18]->event;
        $this->assertInstanceOf(LatestPipelineStatusChanged::class, $event);
        $this->assertObjectEquals($frontendProjectId, $event->projectId);
        $this->assertNull($event->previousStatus);
        $this->assertObjectEquals(Pipeline\Status::Pending, $event->status);

        $this->assertArrayHasKey(19, $dispatchedEvents);
        $event = $dispatchedEvents[19]->event;
        $this->assertInstanceOf(LatestPipelineAwaitingTick::class, $event);
        $this->assertObjectEquals($frontendProjectId, $event->projectId);
        $this->assertObjectEquals($frontendPipelineId, $event->pipelineId);
        $this->assertObjectEquals(Pipeline\Status::Pending, $event->status);

        $this->assertArrayHasKey(20, $dispatchedEvents);
        $event = $dispatchedEvents[20]->event;
        $this->assertInstanceOf(LatestPipelineStatusChanged::class, $event);
        $this->assertObjectEquals($frontendProjectId, $event->projectId);
        $this->assertObjectEquals(Pipeline\Status::Pending, $event->previousStatus);
        $this->assertObjectEquals(Pipeline\Status::Running, $event->status);

        $this->assertArrayHasKey(21, $dispatchedEvents);
        $event = $dispatchedEvents[21]->event;
        $this->assertInstanceOf(LatestPipelineAwaitingTick::class, $event);
        $this->assertObjectEquals($frontendProjectId, $event->projectId);
        $this->assertObjectEquals($frontendPipelineId, $event->pipelineId);
        $this->assertObjectEquals(Pipeline\Status::Running, $event->status);

        $this->assertArrayHasKey(22, $dispatchedEvents);
        $event = $dispatchedEvents[22]->event;
        $this->assertInstanceOf(LatestPipelineStatusChanged::class, $event);
        $this->assertObjectEquals($frontendProjectId, $event->projectId);
        $this->assertObjectEquals(Pipeline\Status::Running, $event->previousStatus);
        $this->assertObjectEquals(Pipeline\Status::Success, $event->status);

        $this->assertArrayHasKey(23, $dispatchedEvents);
        $this->assertHotfixPublicationStatusChanged(
            event: $dispatchedEvents[23]->event,
            expectedPreviousStatus: new StatusFrontendProductionReleaseBranchPipelinePending([
                'retry_counter' => 1,
                'pipeline_id' => $frontendPipelineId->value(),
            ]),
            expectedStatus: new StatusFrontendProductionReleaseBranchPipelineSuccess([
                'retry_counter' => 1,
                'pipeline_id' => $frontendPipelineId->value(),
            ]),
        );

        $this->assertArrayHasKey(24, $dispatchedEvents);
        $event = $dispatchedEvents[24]->event;
        $this->assertInstanceOf(TagCreated::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals($tagName, $event->name);
        $this->assertObjectEquals($tagMessage, $event->message);

        $this->assertArrayHasKey(25, $dispatchedEvents);
        $this->assertHotfixPublicationStatusChanged(
            event: $dispatchedEvents[25]->event,
            expectedPreviousStatus: new StatusFrontendProductionReleaseBranchPipelineSuccess([
                'retry_counter' => 1,
                'pipeline_id' => $frontendPipelineId->value(),
            ]),
            expectedStatus: new StatusTagCreated(),
        );

        $this->assertArrayHasKey(26, $dispatchedEvents);
        $event = $dispatchedEvents[26]->event;
        $this->assertInstanceOf(LatestPipelineStatusChanged::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertNull($event->previousStatus);
        $this->assertObjectEquals(Pipeline\Status::Created, $event->status);

        $this->assertArrayHasKey(27, $dispatchedEvents);
        $event = $dispatchedEvents[27]->event;
        $this->assertInstanceOf(LatestPipelineAwaitingTick::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals($tagPipelineId, $event->pipelineId);
        $this->assertObjectEquals(Pipeline\Status::Created, $event->status);

        $this->assertArrayHasKey(28, $dispatchedEvents);
        $event = $dispatchedEvents[28]->event;
        $this->assertInstanceOf(LatestPipelineStatusChanged::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals(Pipeline\Status::Created, $event->previousStatus);
        $this->assertObjectEquals(Pipeline\Status::WaitingForResource, $event->status);

        $this->assertArrayHasKey(29, $dispatchedEvents);
        $event = $dispatchedEvents[29]->event;
        $this->assertInstanceOf(LatestPipelineAwaitingTick::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals($tagPipelineId, $event->pipelineId);
        $this->assertObjectEquals(Pipeline\Status::WaitingForResource, $event->status);

        $this->assertArrayHasKey(30, $dispatchedEvents);
        $event = $dispatchedEvents[30]->event;
        $this->assertInstanceOf(LatestPipelineStatusChanged::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals(Pipeline\Status::WaitingForResource, $event->previousStatus);
        $this->assertObjectEquals(Pipeline\Status::Preparing, $event->status);

        $this->assertArrayHasKey(31, $dispatchedEvents);
        $event = $dispatchedEvents[31]->event;
        $this->assertInstanceOf(LatestPipelineAwaitingTick::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals($tagPipelineId, $event->pipelineId);
        $this->assertObjectEquals(Pipeline\Status::Preparing, $event->status);

        $this->assertArrayHasKey(32, $dispatchedEvents);
        $event = $dispatchedEvents[32]->event;
        $this->assertInstanceOf(LatestPipelineStatusChanged::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals(Pipeline\Status::Preparing, $event->previousStatus);
        $this->assertObjectEquals(Pipeline\Status::Pending, $event->status);

        $this->assertArrayHasKey(33, $dispatchedEvents);
        $event = $dispatchedEvents[33]->event;
        $this->assertInstanceOf(LatestPipelineAwaitingTick::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals($tagPipelineId, $event->pipelineId);
        $this->assertObjectEquals(Pipeline\Status::Pending, $event->status);

        $this->assertArrayHasKey(34, $dispatchedEvents);
        $event = $dispatchedEvents[34]->event;
        $this->assertInstanceOf(LatestPipelineStatusChanged::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals(Pipeline\Status::Pending, $event->previousStatus);
        $this->assertObjectEquals(Pipeline\Status::Running, $event->status);

        $this->assertArrayHasKey(35, $dispatchedEvents);
        $event = $dispatchedEvents[35]->event;
        $this->assertInstanceOf(LatestPipelineAwaitingTick::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals($tagPipelineId, $event->pipelineId);
        $this->assertObjectEquals(Pipeline\Status::Running, $event->status);

        $this->assertArrayHasKey(36, $dispatchedEvents);
        $event = $dispatchedEvents[36]->event;
        $this->assertInstanceOf(LatestPipelineStatusChanged::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals(Pipeline\Status::Running, $event->previousStatus);
        $this->assertObjectEquals(Pipeline\Status::Failed, $event->status);

        $this->assertArrayHasKey(37, $dispatchedEvents);
        $this->assertHotfixPublicationStatusChanged(
            event: $dispatchedEvents[37]->event,
            expectedPreviousStatus: new StatusTagCreated(),
            expectedStatus: new StatusTagPipelineFailed([
                'pipeline_id' => $tagPipelineId->value(),
            ]),
        );

        $this->assertArrayHasKey(38, $dispatchedEvents);
        $event = $dispatchedEvents[38]->event;
        $this->assertInstanceOf(PipelineRetried::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals($tagPipelineId, $event->pipelineId);
        $this->assertObjectEquals(Pipeline\Status::Pending, $event->status);

        $this->assertArrayHasKey(39, $dispatchedEvents);
        $this->assertHotfixPublicationStatusChanged(
            event: $dispatchedEvents[39]->event,
            expectedPreviousStatus: new StatusTagPipelineFailed([
                'pipeline_id' => $tagPipelineId->value(),
            ]),
            expectedStatus: new StatusTagPipelinePending([
                'retry_counter' => 1,
                'pipeline_id' => $tagPipelineId->value(),
            ]),
        );

        $this->assertArrayHasKey(40, $dispatchedEvents);
        $event = $dispatchedEvents[40]->event;
        $this->assertInstanceOf(LatestPipelineStatusChanged::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertNull($event->previousStatus);
        $this->assertObjectEquals(Pipeline\Status::Pending, $event->status);

        $this->assertArrayHasKey(41, $dispatchedEvents);
        $event = $dispatchedEvents[41]->event;
        $this->assertInstanceOf(LatestPipelineAwaitingTick::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals($tagPipelineId, $event->pipelineId);
        $this->assertObjectEquals(Pipeline\Status::Pending, $event->status);

        $this->assertArrayHasKey(42, $dispatchedEvents);
        $event = $dispatchedEvents[42]->event;
        $this->assertInstanceOf(LatestPipelineStatusChanged::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals(Pipeline\Status::Pending, $event->previousStatus);
        $this->assertObjectEquals(Pipeline\Status::Running, $event->status);

        $this->assertArrayHasKey(43, $dispatchedEvents);
        $event = $dispatchedEvents[43]->event;
        $this->assertInstanceOf(LatestPipelineAwaitingTick::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals($tagPipelineId, $event->pipelineId);
        $this->assertObjectEquals(Pipeline\Status::Running, $event->status);

        $this->assertArrayHasKey(44, $dispatchedEvents);
        $event = $dispatchedEvents[44]->event;
        $this->assertInstanceOf(LatestPipelineStatusChanged::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals(Pipeline\Status::Running, $event->previousStatus);
        $this->assertObjectEquals(Pipeline\Status::Success, $event->status);

        $this->assertArrayHasKey(45, $dispatchedEvents);
        $this->assertHotfixPublicationStatusChanged(
            event: $dispatchedEvents[45]->event,
            expectedPreviousStatus: new StatusTagPipelinePending([
                'retry_counter' => 1,
                'pipeline_id' => $tagPipelineId->value(),
            ]),
            expectedStatus: new StatusTagPipelineSuccess([
                'retry_counter' => 1,
                'pipeline_id' => $tagPipelineId->value(),
            ]),
        );

        $this->assertArrayHasKey(46, $dispatchedEvents);
        $event = $dispatchedEvents[46]->event;
        $this->assertInstanceOf(JobRan::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals($deploymentJobId, $event->jobId);
        $this->assertObjectEquals($deploymentJobName, $event->name);

        $this->assertArrayHasKey(47, $dispatchedEvents);
        $this->assertHotfixPublicationStatusChanged(
            event: $dispatchedEvents[47]->event,
            expectedPreviousStatus: new StatusTagPipelineSuccess([
                'retry_counter' => 1,
                'pipeline_id' => $tagPipelineId->value(),
            ]),
            expectedStatus: new StatusDeploymentJobInited([
                'job_id' => $deploymentJobId->value(),
            ]),
        );

        $this->assertArrayHasKey(48, $dispatchedEvents);
        $event = $dispatchedEvents[48]->event;
        $this->assertInstanceOf(JobStatusChanged::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertNull($event->previousStatus);
        $this->assertObjectEquals(new Job\Status\StatusWaitingForResource(), $event->status);

        $this->assertArrayHasKey(49, $dispatchedEvents);
        $event = $dispatchedEvents[49]->event;
        $this->assertInstanceOf(JobAwaitingTick::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals($tagPipelineId, $event->pipelineId);
        $this->assertObjectEquals(new Job\Status\StatusWaitingForResource(), $event->status);

        $this->assertArrayHasKey(50, $dispatchedEvents);
        $event = $dispatchedEvents[50]->event;
        $this->assertInstanceOf(JobStatusChanged::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals(new Job\Status\StatusWaitingForResource(), $event->previousStatus);
        $this->assertObjectEquals(new Job\Status\StatusPreparing(), $event->status);

        $this->assertArrayHasKey(51, $dispatchedEvents);
        $event = $dispatchedEvents[51]->event;
        $this->assertInstanceOf(JobAwaitingTick::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals($tagPipelineId, $event->pipelineId);
        $this->assertObjectEquals(new Job\Status\StatusPreparing(), $event->status);

        $this->assertArrayHasKey(52, $dispatchedEvents);
        $event = $dispatchedEvents[52]->event;
        $this->assertInstanceOf(JobStatusChanged::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals(new Job\Status\StatusPreparing(), $event->previousStatus);
        $this->assertObjectEquals(new Job\Status\StatusPending(), $event->status);

        $this->assertArrayHasKey(53, $dispatchedEvents);
        $event = $dispatchedEvents[53]->event;
        $this->assertInstanceOf(JobAwaitingTick::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals($tagPipelineId, $event->pipelineId);
        $this->assertObjectEquals(new Job\Status\StatusPending(), $event->status);

        $this->assertArrayHasKey(54, $dispatchedEvents);
        $event = $dispatchedEvents[54]->event;
        $this->assertInstanceOf(JobStatusChanged::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals(new Job\Status\StatusPending(), $event->previousStatus);
        $this->assertObjectEquals(new Job\Status\StatusRunning(), $event->status);

        $this->assertArrayHasKey(55, $dispatchedEvents);
        $event = $dispatchedEvents[55]->event;
        $this->assertInstanceOf(JobAwaitingTick::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals($tagPipelineId, $event->pipelineId);
        $this->assertObjectEquals(new Job\Status\StatusRunning(), $event->status);

        $this->assertArrayHasKey(56, $dispatchedEvents);
        $event = $dispatchedEvents[56]->event;
        $this->assertInstanceOf(JobStatusChanged::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals(new Job\Status\StatusRunning(), $event->previousStatus);
        $this->assertObjectEquals(new Job\Status\StatusFailed(), $event->status);

        $this->assertArrayHasKey(57, $dispatchedEvents);
        $this->assertHotfixPublicationStatusChanged(
            event: $dispatchedEvents[57]->event,
            expectedPreviousStatus: new StatusDeploymentJobInited([
                'job_id' => $deploymentJobId->value(),
            ]),
            expectedStatus: new StatusDeploymentJobFailed([
                'job_id' => $deploymentJobId->value(),
            ]),
        );

        $this->assertArrayHasKey(58, $dispatchedEvents);
        $event = $dispatchedEvents[58]->event;
        $this->assertInstanceOf(JobRetried::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals($deploymentJobId, $event->jobId);
        $this->assertObjectEquals($deploymentJobName, $event->name);
        $this->assertObjectEquals(new Job\Status\StatusPending(), $event->status);

        $this->assertArrayHasKey(59, $dispatchedEvents);
        $this->assertHotfixPublicationStatusChanged(
            event: $dispatchedEvents[59]->event,
            expectedPreviousStatus: new StatusDeploymentJobFailed([
                'job_id' => $deploymentJobId->value(),
            ]),
            expectedStatus: new StatusDeploymentJobPending([
                'retry_counter' => 1,
                'job_id' => $deploymentJobId->value(),
            ]),
        );

        $this->assertArrayHasKey(60, $dispatchedEvents);
        $event = $dispatchedEvents[60]->event;
        $this->assertInstanceOf(JobStatusChanged::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertNull($event->previousStatus);
        $this->assertObjectEquals(new Job\Status\StatusPending(), $event->status);

        $this->assertArrayHasKey(61, $dispatchedEvents);
        $event = $dispatchedEvents[61]->event;
        $this->assertInstanceOf(JobAwaitingTick::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals($tagPipelineId, $event->pipelineId);
        $this->assertObjectEquals(new Job\Status\StatusPending(), $event->status);

        $this->assertArrayHasKey(62, $dispatchedEvents);
        $event = $dispatchedEvents[62]->event;
        $this->assertInstanceOf(JobStatusChanged::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals(new Job\Status\StatusPending(), $event->previousStatus);
        $this->assertObjectEquals(new Job\Status\StatusRunning(), $event->status);

        $this->assertArrayHasKey(63, $dispatchedEvents);
        $event = $dispatchedEvents[63]->event;
        $this->assertInstanceOf(JobAwaitingTick::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals($tagPipelineId, $event->pipelineId);
        $this->assertObjectEquals(new Job\Status\StatusRunning(), $event->status);

        $this->assertArrayHasKey(64, $dispatchedEvents);
        $event = $dispatchedEvents[64]->event;
        $this->assertInstanceOf(JobStatusChanged::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals(new Job\Status\StatusRunning(), $event->previousStatus);
        $this->assertObjectEquals(new Job\Status\StatusSuccess(), $event->status);

        $this->assertArrayHasKey(65, $dispatchedEvents);
        $this->assertHotfixPublicationStatusChanged(
            event: $dispatchedEvents[65]->event,
            expectedPreviousStatus: new StatusDeploymentJobPending([
                'retry_counter' => 1,
                'job_id' => $deploymentJobId->value(),
            ]),
            expectedStatus: new StatusDeploymentJobSuccess([
                'retry_counter' => 1,
                'job_id' => $deploymentJobId->value(),
            ]),
        );

        $this->assertArrayHasKey(66, $dispatchedEvents);
        $event = $dispatchedEvents[66]->event;
        $this->assertInstanceOf(HotfixTransitionedToDone::class, $event);
        $this->assertObjectEquals($container->get(Project\Key::class), $event->projectKey);
        $this->assertObjectEquals($hotfixesArray[0]->key, $event->key);

        $this->assertArrayHasKey(67, $dispatchedEvents);
        $this->assertHotfixPublicationStatusChanged(
            event: $dispatchedEvents[67]->event,
            expectedPreviousStatus: new StatusDeploymentJobSuccess([
                'retry_counter' => 1,
                'job_id' => $deploymentJobId->value(),
            ]),
            expectedStatus: new StatusHotfixesTransitionedToDone(),
        );

        $this->assertArrayHasKey(68, $dispatchedEvents);
        $event = $dispatchedEvents[68]->event;
        $this->assertInstanceOf(MergeRequestCreated::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals($backendMrToMerge->title, $event->title);
        $this->assertObjectEquals($backendMrToMerge->sourceBranchName, $event->sourceBranchName);
        $this->assertObjectEquals($developmentBranchName, $event->targetBranchName);
        $this->assertObjectEquals(new MergeRequest\Details\Status\StatusNotOpen(), $event->details->status);

        $this->assertArrayHasKey(69, $dispatchedEvents);
        $event = $dispatchedEvents[69]->event;
        $this->assertInstanceOf(MergeRequestCreated::class, $event);
        $this->assertObjectEquals($frontendProjectId, $event->projectId);
        $this->assertObjectEquals($frontendMrToMerge->title, $event->title);
        $this->assertObjectEquals($frontendMrToMerge->sourceBranchName, $event->sourceBranchName);
        $this->assertObjectEquals($developmentBranchName, $event->targetBranchName);
        $this->assertObjectEquals(new MergeRequest\Details\Status\StatusNotOpen(), $event->details->status);

        $this->assertArrayHasKey(70, $dispatchedEvents);
        $this->assertHotfixPublicationStatusChanged(
            event: $dispatchedEvents[70]->event,
            expectedPreviousStatus: new StatusHotfixesTransitionedToDone(),
            expectedStatus: new StatusMergeRequestsIntoDevelopmentBranchCreated(),
        );

        $this->assertArrayHasKey(71, $dispatchedEvents);
        $event = $dispatchedEvents[71]->event;
        $this->assertInstanceOf(MergeRequestAwaitingTick::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals($expectedMrsToMerge[2]->iid, $event->mergeRequestIid);
        $this->assertObjectEquals($expectedMrsToMerge[2]->title, $event->title);
        $this->assertObjectEquals($expectedMrsToMerge[2]->sourceBranchName, $event->sourceBranchName);
        $this->assertObjectEquals($expectedMrsToMerge[2]->targetBranchName, $event->targetBranchName);
        $this->assertObjectEquals($expectedMrsToMerge[2]->details->withStatus(new MergeRequest\Details\Status\StatusNotOpen()), $event->details);

        $this->assertArrayHasKey(72, $dispatchedEvents);
        $event = $dispatchedEvents[72]->event;
        $this->assertInstanceOf(MergeRequestStatusChanged::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals($expectedMrsToMerge[2]->iid, $event->mergeRequestIid);
        $this->assertObjectEquals($expectedMrsToMerge[2]->title, $event->title);
        $this->assertObjectEquals($expectedMrsToMerge[2]->sourceBranchName, $event->sourceBranchName);
        $this->assertObjectEquals($expectedMrsToMerge[2]->targetBranchName, $event->targetBranchName);
        $this->assertObjectEquals($expectedMrsToMerge[2]->details->withStatus(new MergeRequest\Details\Status\StatusPreparing()), $event->details);

        $this->assertArrayHasKey(73, $dispatchedEvents);
        $event = $dispatchedEvents[73]->event;
        $this->assertInstanceOf(MergeRequestAwaitingTick::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals($expectedMrsToMerge[2]->iid, $event->mergeRequestIid);
        $this->assertObjectEquals($expectedMrsToMerge[2]->title, $event->title);
        $this->assertObjectEquals($expectedMrsToMerge[2]->sourceBranchName, $event->sourceBranchName);
        $this->assertObjectEquals($expectedMrsToMerge[2]->targetBranchName, $event->targetBranchName);
        $this->assertObjectEquals($expectedMrsToMerge[2]->details->withStatus(new MergeRequest\Details\Status\StatusPreparing()), $event->details);

        $this->assertArrayHasKey(74, $dispatchedEvents);
        $event = $dispatchedEvents[74]->event;
        $this->assertInstanceOf(MergeRequestStatusChanged::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals($expectedMrsToMerge[2]->iid, $event->mergeRequestIid);
        $this->assertObjectEquals($expectedMrsToMerge[2]->title, $event->title);
        $this->assertObjectEquals($expectedMrsToMerge[2]->sourceBranchName, $event->sourceBranchName);
        $this->assertObjectEquals($expectedMrsToMerge[2]->targetBranchName, $event->targetBranchName);
        $this->assertObjectEquals($expectedMrsToMerge[2]->details->withStatus(new MergeRequest\Details\Status\StatusCiStillRunning()), $event->details);

        $this->assertArrayHasKey(75, $dispatchedEvents);
        $event = $dispatchedEvents[75]->event;
        $this->assertInstanceOf(MergeRequestAwaitingTick::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals($expectedMrsToMerge[2]->iid, $event->mergeRequestIid);
        $this->assertObjectEquals($expectedMrsToMerge[2]->title, $event->title);
        $this->assertObjectEquals($expectedMrsToMerge[2]->sourceBranchName, $event->sourceBranchName);
        $this->assertObjectEquals($expectedMrsToMerge[2]->targetBranchName, $event->targetBranchName);
        $this->assertObjectEquals($expectedMrsToMerge[2]->details->withStatus(new MergeRequest\Details\Status\StatusCiStillRunning()), $event->details);

        $this->assertArrayHasKey(76, $dispatchedEvents);
        $event = $dispatchedEvents[76]->event;
        $this->assertInstanceOf(MergeRequestStatusChanged::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals($expectedMrsToMerge[2]->iid, $event->mergeRequestIid);
        $this->assertObjectEquals($expectedMrsToMerge[2]->title, $event->title);
        $this->assertObjectEquals($expectedMrsToMerge[2]->sourceBranchName, $event->sourceBranchName);
        $this->assertObjectEquals($expectedMrsToMerge[2]->targetBranchName, $event->targetBranchName);
        $this->assertObjectEquals($expectedMrsToMerge[2]->details->withStatus(new MergeRequest\Details\Status\StatusMergeable()), $event->details);

        $this->assertArrayHasKey(77, $dispatchedEvents);
        $event = $dispatchedEvents[77]->event;
        $this->assertInstanceOf(MergeRequestMerged::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals($expectedMrsToMerge[2]->iid, $event->mergeRequestIid);
        $this->assertObjectEquals($expectedMrsToMerge[2]->title, $event->title);
        $this->assertObjectEquals($expectedMrsToMerge[2]->sourceBranchName, $event->sourceBranchName);
        $this->assertObjectEquals($expectedMrsToMerge[2]->targetBranchName, $event->targetBranchName);
        $this->assertObjectEquals($expectedMrsToMerge[2]->details, $event->details);

        $this->assertArrayHasKey(78, $dispatchedEvents);
        $event = $dispatchedEvents[78]->event;
        $this->assertInstanceOf(MergeRequestAwaitingTick::class, $event);
        $this->assertObjectEquals($frontendProjectId, $event->projectId);
        $this->assertObjectEquals($expectedMrsToMerge[2]->iid, $event->mergeRequestIid);
        $this->assertObjectEquals($expectedMrsToMerge[2]->title, $event->title);
        $this->assertObjectEquals($expectedMrsToMerge[2]->sourceBranchName, $event->sourceBranchName);
        $this->assertObjectEquals($expectedMrsToMerge[2]->targetBranchName, $event->targetBranchName);
        $this->assertObjectEquals($expectedMrsToMerge[2]->details->withStatus(new MergeRequest\Details\Status\StatusNotOpen()), $event->details);

        $this->assertArrayHasKey(79, $dispatchedEvents);
        $event = $dispatchedEvents[79]->event;
        $this->assertInstanceOf(MergeRequestStatusChanged::class, $event);
        $this->assertObjectEquals($frontendProjectId, $event->projectId);
        $this->assertObjectEquals($expectedMrsToMerge[2]->iid, $event->mergeRequestIid);
        $this->assertObjectEquals($expectedMrsToMerge[2]->title, $event->title);
        $this->assertObjectEquals($expectedMrsToMerge[2]->sourceBranchName, $event->sourceBranchName);
        $this->assertObjectEquals($expectedMrsToMerge[2]->targetBranchName, $event->targetBranchName);
        $this->assertObjectEquals($expectedMrsToMerge[2]->details->withStatus(new MergeRequest\Details\Status\StatusPreparing()), $event->details);

        $this->assertArrayHasKey(80, $dispatchedEvents);
        $event = $dispatchedEvents[80]->event;
        $this->assertInstanceOf(MergeRequestAwaitingTick::class, $event);
        $this->assertObjectEquals($frontendProjectId, $event->projectId);
        $this->assertObjectEquals($expectedMrsToMerge[2]->iid, $event->mergeRequestIid);
        $this->assertObjectEquals($expectedMrsToMerge[2]->title, $event->title);
        $this->assertObjectEquals($expectedMrsToMerge[2]->sourceBranchName, $event->sourceBranchName);
        $this->assertObjectEquals($expectedMrsToMerge[2]->targetBranchName, $event->targetBranchName);
        $this->assertObjectEquals($expectedMrsToMerge[2]->details->withStatus(new MergeRequest\Details\Status\StatusPreparing()), $event->details);

        $this->assertArrayHasKey(81, $dispatchedEvents);
        $event = $dispatchedEvents[81]->event;
        $this->assertInstanceOf(MergeRequestStatusChanged::class, $event);
        $this->assertObjectEquals($frontendProjectId, $event->projectId);
        $this->assertObjectEquals($expectedMrsToMerge[2]->iid, $event->mergeRequestIid);
        $this->assertObjectEquals($expectedMrsToMerge[2]->title, $event->title);
        $this->assertObjectEquals($expectedMrsToMerge[2]->sourceBranchName, $event->sourceBranchName);
        $this->assertObjectEquals($expectedMrsToMerge[2]->targetBranchName, $event->targetBranchName);
        $this->assertObjectEquals($expectedMrsToMerge[2]->details->withStatus(new MergeRequest\Details\Status\StatusMergeable()), $event->details);

        $this->assertArrayHasKey(82, $dispatchedEvents);
        $event = $dispatchedEvents[82]->event;
        $this->assertInstanceOf(MergeRequestMerged::class, $event);
        $this->assertObjectEquals($frontendProjectId, $event->projectId);
        $this->assertObjectEquals($expectedMrsToMerge[2]->iid, $event->mergeRequestIid);
        $this->assertObjectEquals($expectedMrsToMerge[2]->title, $event->title);
        $this->assertObjectEquals($expectedMrsToMerge[2]->sourceBranchName, $event->sourceBranchName);
        $this->assertObjectEquals($expectedMrsToMerge[2]->targetBranchName, $event->targetBranchName);
        $this->assertObjectEquals($expectedMrsToMerge[2]->details, $event->details);

        $this->assertArrayHasKey(83, $dispatchedEvents);
        $this->assertHotfixPublicationStatusChanged(
            event: $dispatchedEvents[83]->event,
            expectedPreviousStatus: new StatusMergeRequestsIntoDevelopmentBranchCreated(),
            expectedStatus: new StatusDevelopmentBranchSynchronized(),
        );

        $this->assertArrayHasKey(84, $dispatchedEvents);
        $event = $dispatchedEvents[84]->event;
        $this->assertInstanceOf(MergeRequestCreated::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals($backendMrToMerge->title, $event->title);
        $this->assertObjectEquals($backendMrToMerge->sourceBranchName, $event->sourceBranchName);
        $this->assertObjectEquals(Branch\Name::fromString((string) $latestReleaseVersionName), $event->targetBranchName);
        $this->assertObjectEquals(new MergeRequest\Details\Status\StatusNotOpen(), $event->details->status);

        $this->assertArrayHasKey(85, $dispatchedEvents);
        $event = $dispatchedEvents[85]->event;
        $this->assertInstanceOf(MergeRequestCreated::class, $event);
        $this->assertObjectEquals($frontendProjectId, $event->projectId);
        $this->assertObjectEquals($frontendMrToMerge->title, $event->title);
        $this->assertObjectEquals($frontendMrToMerge->sourceBranchName, $event->sourceBranchName);
        $this->assertObjectEquals(Branch\Name::fromString((string) $latestReleaseVersionName), $event->targetBranchName);
        $this->assertObjectEquals(new MergeRequest\Details\Status\StatusNotOpen(), $event->details->status);

        $this->assertArrayHasKey(86, $dispatchedEvents);
        $this->assertHotfixPublicationStatusChanged(
            event: $dispatchedEvents[86]->event,
            expectedPreviousStatus: new StatusDevelopmentBranchSynchronized(),
            expectedStatus: new StatusMergeRequestsIntoReleaseBranchCreated(),
        );

        $this->assertArrayHasKey(87, $dispatchedEvents);
        $event = $dispatchedEvents[87]->event;
        $this->assertInstanceOf(MergeRequestAwaitingTick::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals($expectedMrsToMerge[4]->iid, $event->mergeRequestIid);
        $this->assertObjectEquals($expectedMrsToMerge[4]->title, $event->title);
        $this->assertObjectEquals($expectedMrsToMerge[4]->sourceBranchName, $event->sourceBranchName);
        $this->assertObjectEquals($expectedMrsToMerge[4]->targetBranchName, $event->targetBranchName);
        $this->assertObjectEquals($expectedMrsToMerge[4]->details->withStatus(new MergeRequest\Details\Status\StatusPreparing()), $event->details);

        $this->assertArrayHasKey(88, $dispatchedEvents);
        $event = $dispatchedEvents[88]->event;
        $this->assertInstanceOf(MergeRequestStatusChanged::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals($expectedMrsToMerge[4]->iid, $event->mergeRequestIid);
        $this->assertObjectEquals($expectedMrsToMerge[4]->title, $event->title);
        $this->assertObjectEquals($expectedMrsToMerge[4]->sourceBranchName, $event->sourceBranchName);
        $this->assertObjectEquals($expectedMrsToMerge[4]->targetBranchName, $event->targetBranchName);
        $this->assertObjectEquals($expectedMrsToMerge[4]->details->withStatus(new MergeRequest\Details\Status\StatusMergeable()), $event->details);

        $this->assertArrayHasKey(89, $dispatchedEvents);
        $event = $dispatchedEvents[89]->event;
        $this->assertInstanceOf(MergeRequestMerged::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals($expectedMrsToMerge[4]->iid, $event->mergeRequestIid);
        $this->assertObjectEquals($expectedMrsToMerge[4]->title, $event->title);
        $this->assertObjectEquals($expectedMrsToMerge[4]->sourceBranchName, $event->sourceBranchName);
        $this->assertObjectEquals($expectedMrsToMerge[4]->targetBranchName, $event->targetBranchName);
        $this->assertObjectEquals($expectedMrsToMerge[4]->details, $event->details);

        $this->assertArrayHasKey(90, $dispatchedEvents);
        $event = $dispatchedEvents[90]->event;
        $this->assertInstanceOf(MergeRequestAwaitingTick::class, $event);
        $this->assertObjectEquals($frontendProjectId, $event->projectId);
        $this->assertObjectEquals($expectedMrsToMerge[5]->iid, $event->mergeRequestIid);
        $this->assertObjectEquals($expectedMrsToMerge[5]->title, $event->title);
        $this->assertObjectEquals($expectedMrsToMerge[5]->sourceBranchName, $event->sourceBranchName);
        $this->assertObjectEquals($expectedMrsToMerge[5]->targetBranchName, $event->targetBranchName);
        $this->assertObjectEquals($expectedMrsToMerge[5]->details->withStatus(new MergeRequest\Details\Status\StatusPreparing()), $event->details);

        $this->assertArrayHasKey(91, $dispatchedEvents);
        $event = $dispatchedEvents[91]->event;
        $this->assertInstanceOf(MergeRequestStatusChanged::class, $event);
        $this->assertObjectEquals($frontendProjectId, $event->projectId);
        $this->assertObjectEquals($expectedMrsToMerge[5]->iid, $event->mergeRequestIid);
        $this->assertObjectEquals($expectedMrsToMerge[5]->title, $event->title);
        $this->assertObjectEquals($expectedMrsToMerge[5]->sourceBranchName, $event->sourceBranchName);
        $this->assertObjectEquals($expectedMrsToMerge[5]->targetBranchName, $event->targetBranchName);
        $this->assertObjectEquals($expectedMrsToMerge[5]->details->withStatus(new MergeRequest\Details\Status\StatusMergeable()), $event->details);

        $this->assertArrayHasKey(92, $dispatchedEvents);
        $event = $dispatchedEvents[92]->event;
        $this->assertInstanceOf(MergeRequestMerged::class, $event);
        $this->assertObjectEquals($frontendProjectId, $event->projectId);
        $this->assertObjectEquals($expectedMrsToMerge[5]->iid, $event->mergeRequestIid);
        $this->assertObjectEquals($expectedMrsToMerge[5]->title, $event->title);
        $this->assertObjectEquals($expectedMrsToMerge[5]->sourceBranchName, $event->sourceBranchName);
        $this->assertObjectEquals($expectedMrsToMerge[5]->targetBranchName, $event->targetBranchName);
        $this->assertObjectEquals($expectedMrsToMerge[5]->details, $event->details);

        $this->assertArrayHasKey(93, $dispatchedEvents);
        $this->assertHotfixPublicationStatusChanged(
            event: $dispatchedEvents[93]->event,
            expectedPreviousStatus: new StatusMergeRequestsIntoReleaseBranchCreated(),
            expectedStatus: new StatusReleaseBranchSynchronized(),
            expectedHotfixes: $expectedHotfixes,
        );

        $this->assertArrayHasKey(94, $dispatchedEvents);
        $event = $dispatchedEvents[94]->event;
        $this->assertInstanceOf(CommitCreated::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals($developmentBranchName, $event->branchName);
        $this->assertNull($event->startBranchName);
        $this->assertObjectEquals(
            Commit\Message::fromString("Change frontend application branch name to $developmentBranchName"),
            $event->message,
        );

        $this->assertArrayHasKey(95, $dispatchedEvents);
        $this->assertHotfixPublicationStatusChanged(
            event: $dispatchedEvents[95]->event,
            expectedPreviousStatus: new StatusReleaseBranchSynchronized(),
            expectedStatus: new StatusFrontendApplicationBranchSetToDevelopment(),
            expectedHotfixes: $expectedHotfixes,
        );

        $this->assertArrayHasKey(96, $dispatchedEvents);
        $event = $dispatchedEvents[96]->event;
        $this->assertInstanceOf(MergeRequestCreated::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals($updateExtraDeployBranchMrTitle, $event->title);
        $this->assertObjectEquals($updateExtraDeployBranchMrSourceBranchName, $event->sourceBranchName);
        $this->assertObjectEquals($updateExtraDeployBranchMrTargetBranchName, $event->targetBranchName);
        $this->assertObjectEquals(new MergeRequest\Details\Status\StatusNotOpen(), $event->details->status);

        $this->assertArrayHasKey(97, $dispatchedEvents);
        $this->assertHotfixPublicationStatusChanged(
            event: $dispatchedEvents[97]->event,
            expectedPreviousStatus: new StatusFrontendApplicationBranchSetToDevelopment(),
            expectedStatus: new StatusMergeRequestIntoExtraDeploymentBranchCreated([
                'project_id' => $backendProjectId->value(),
                'merge_request_iid' => $updateExtraDeployBranchMrIid->value(),
            ]),
            expectedHotfixes: $expectedHotfixes,
        );

        $this->assertArrayHasKey(98, $dispatchedEvents);
        $event = $dispatchedEvents[98]->event;
        $this->assertInstanceOf(MergeRequestAwaitingTick::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals($updateExtraDeployBranchMrIid, $event->mergeRequestIid);
        $this->assertObjectEquals($updateExtraDeployBranchMrTitle, $event->title);
        $this->assertObjectEquals($updateExtraDeployBranchMrSourceBranchName, $event->sourceBranchName);
        $this->assertObjectEquals($updateExtraDeployBranchMrTargetBranchName, $event->targetBranchName);
        $this->assertObjectEquals(new MergeRequest\Details\Details(new MergeRequest\Details\Status\StatusPreparing()), $event->details);

        $this->assertArrayHasKey(99, $dispatchedEvents);
        $event = $dispatchedEvents[99]->event;
        $this->assertInstanceOf(MergeRequestStatusChanged::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals($updateExtraDeployBranchMrIid, $event->mergeRequestIid);
        $this->assertObjectEquals($updateExtraDeployBranchMrTitle, $event->title);
        $this->assertObjectEquals($updateExtraDeployBranchMrSourceBranchName, $event->sourceBranchName);
        $this->assertObjectEquals($updateExtraDeployBranchMrTargetBranchName, $event->targetBranchName);
        $this->assertObjectEquals(new MergeRequest\Details\Details(new MergeRequest\Details\Status\StatusMergeable()), $event->details);

        $this->assertArrayHasKey(100, $dispatchedEvents);
        $event = $dispatchedEvents[100]->event;
        $this->assertInstanceOf(MergeRequestMerged::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals($updateExtraDeployBranchMrIid, $event->mergeRequestIid);
        $this->assertObjectEquals($updateExtraDeployBranchMrTitle, $event->title);
        $this->assertObjectEquals($updateExtraDeployBranchMrSourceBranchName, $event->sourceBranchName);
        $this->assertObjectEquals($updateExtraDeployBranchMrTargetBranchName, $event->targetBranchName);
        $this->assertObjectEquals(new MergeRequest\Details\Details(new MergeRequest\Details\Status\StatusNotOpen()), $event->details);

        $this->assertArrayHasKey(101, $dispatchedEvents);
        $this->assertHotfixPublicationStatusChanged(
            event: $dispatchedEvents[101]->event,
            expectedPreviousStatus: new StatusMergeRequestIntoExtraDeploymentBranchCreated([
                'project_id' => $backendProjectId->value(),
                'merge_request_iid' => $updateExtraDeployBranchMrIid->value(),
            ]),
            expectedStatus: new StatusDone(),
            expectedHotfixes: $expectedHotfixes,
        );
    }

    protected function assertHotfixPublicationStatusChanged(
        object $event,
        StatusInterface $expectedPreviousStatus,
        StatusInterface $expectedStatus,
        ?IssueList $expectedHotfixes = null,
    ): void {
        $this->assertInstanceOf(HotfixPublicationStatusChanged::class, $event);
        $this->assertObjectEquals($expectedPreviousStatus, $event->previousStatus);
        $this->assertObjectEquals($expectedStatus, $event->status);

        if (null !== $expectedHotfixes) {
            $this->assertObjectEquals($expectedHotfixes, $event->hotfixes);
        }
    }
}
