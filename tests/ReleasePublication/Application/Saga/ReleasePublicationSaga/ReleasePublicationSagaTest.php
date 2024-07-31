<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Tests\ReleasePublication\Application\Saga\ReleasePublicationSaga;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Invis1ble\Messenger\Command\CommandBusInterface;
use Invis1ble\Messenger\Event\TraceableEventBus;
use Invis1ble\ProjectManagement\ReleasePublication\Application\UseCase\Command\PublishRelease\PublishReleaseCommand;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Event\ReleasePublicationStatusChanged;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Event\ReleasePublicationTagSet;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Event\TaskTracker\ReleaseReleased;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\ReleasePublication;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\ReleasePublicationId;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\SourceCodeRepository\Branch as ReleaseBranch;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\Status\StatusBackendMergeRequestIntoProductionReleaseBranchCreated;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\Status\StatusBackendMergeRequestIntoProductionReleaseBranchMerged;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\Status\StatusDeploymentJobInited;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\Status\StatusDeploymentPipelineFailed;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\Status\StatusDeploymentPipelinePending;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\Status\StatusDeploymentPipelineSuccess;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\Status\StatusFrontendMergeRequestIntoProductionReleaseBranchCreated;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\Status\StatusFrontendMergeRequestIntoProductionReleaseBranchMerged;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\Status\StatusFrontendProductionReleaseBranchPipelineFailed;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\Status\StatusFrontendProductionReleaseBranchPipelinePending;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\Status\StatusFrontendProductionReleaseBranchPipelineSuccess;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\Status\StatusReleaseCandidateCreated;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\Status\StatusTagCreated;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\Status\StatusTagPipelineFailed;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\Status\StatusTagPipelinePending;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\Status\StatusTagPipelineSuccess;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\Status\StatusVersionReleased;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Repository\ReleasePublicationRepositoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Event\ContinuousIntegration\Job\JobRan;
use Invis1ble\ProjectManagement\Shared\Domain\Event\ContinuousIntegration\Pipeline\LatestPipelineAwaitingTick;
use Invis1ble\ProjectManagement\Shared\Domain\Event\ContinuousIntegration\Pipeline\LatestPipelineStatusChanged;
use Invis1ble\ProjectManagement\Shared\Domain\Event\DevelopmentCollaboration\MergeRequest\MergeRequestAwaitingTick;
use Invis1ble\ProjectManagement\Shared\Domain\Event\DevelopmentCollaboration\MergeRequest\MergeRequestCreated;
use Invis1ble\ProjectManagement\Shared\Domain\Event\DevelopmentCollaboration\MergeRequest\MergeRequestMerged;
use Invis1ble\ProjectManagement\Shared\Domain\Event\DevelopmentCollaboration\MergeRequest\MergeRequestStatusChanged;
use Invis1ble\ProjectManagement\Shared\Domain\Event\SourceCodeRepository\Tag\TagCreated;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Job;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Pipeline;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Branch;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Commit;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Ref;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Tag;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Version;
use Invis1ble\ProjectManagement\Tests\Shared\Application\Saga\PublicationTestCase;
use Invis1ble\ProjectManagement\Tests\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\fixture\CreateMergeRequestTrait;
use Invis1ble\ProjectManagement\Tests\Shared\Domain\Model\SourceCodeRepository\Branch\BranchResponseFixtureTrait;
use Invis1ble\ProjectManagement\Tests\Shared\Domain\Model\TaskTracker\Project\ProjectResponseFixtureTrait;
use Invis1ble\ProjectManagement\Tests\Shared\Domain\Model\TaskTracker\Version\VersionResponseFixtureTrait;
use Psr\Http\Message\UriFactoryInterface;

class ReleasePublicationSagaTest extends PublicationTestCase
{
    use BranchResponseFixtureTrait;
    use CreateMergeRequestTrait;
    use VersionResponseFixtureTrait;
    use ProjectResponseFixtureTrait;

    public function testReleasePublication(): void
    {
        self::bootKernel();

        $container = static::getContainer();

        /** @var ReleasePublicationRepositoryInterface $releasePublicationRepository */
        $releasePublicationRepository = $container->get(ReleasePublicationRepositoryInterface::class);

        /** @var UriFactoryInterface $uriFactory */
        $uriFactory = $container->get(UriFactoryInterface::class);

        /** @var CommandBusInterface $commandBus */
        $commandBus = $container->get(CommandBusInterface::class);

        /** @var TraceableEventBus $eventBus */
        $eventBus = $container->get(TraceableEventBus::class);

        $tasks = $this->createIssues(
            uriFactory: $uriFactory,
            mergeRequestTargetBranchName: 'develop',
        );

        $releaseBranchName = ReleaseBranch\Name::fromString('v-1-0-0');
        $productionReleaseBranchName = Branch\Name::fromString('master');

        $backendMrToMerge = $this->createMergeRequest(
            uriFactory: $uriFactory,
            iid: 321,
            projectId: 1,
            projectName: 'backend',
            title: "Merge $releaseBranchName into $productionReleaseBranchName.",
            sourceBranchName: (string) $releaseBranchName,
            targetBranchName: (string) $productionReleaseBranchName,
            jiraStatus: MergeRequest\Status::Open,
            gitlabStatus: MergeRequest\Details\Status\Dictionary::NotOpen,
        );
        $frontendMrToMerge = $this->createMergeRequest(
            uriFactory: $uriFactory,
            iid: 123,
            projectId: 2,
            projectName: 'frontend',
            title: "Merge $releaseBranchName into $productionReleaseBranchName.",
            sourceBranchName: (string) $releaseBranchName,
            targetBranchName: (string) $productionReleaseBranchName,
            jiraStatus: MergeRequest\Status::Open,
            gitlabStatus: MergeRequest\Details\Status\Dictionary::NotOpen,
        );
        $backendProjectId = $backendMrToMerge->projectId;
        $frontendProjectId = $frontendMrToMerge->projectId;
        $backendProjectName = $backendMrToMerge->projectName;
        $frontendProjectName = $frontendMrToMerge->projectName;

        $tagName = Tag\VersionName::create();
        $tagMessage = Tag\Message::fromString("Release $releaseBranchName");

        $now = new \DateTimeImmutable();
        $frontendPipelineCreatedAt = $now->add(new \DateInterval('PT5M'));
        $tagCreatedAt = $now->add(new \DateInterval('PT1M'));
        $setFrontendApplicationBranchNameCommitCreatedAt = $now->add(new \DateInterval('PT15M'));

        $latestReleaseVersionName = Version\Name::fromString('v-1-0-0');

        $tagFixture = $this->createTagResponseFixture(
            tagName: $tagName,
            tagMessage: $tagMessage,
            commitMessage: Commit\Message::fromString((string) $backendMrToMerge->title),
            commitCreatedAt: $frontendPipelineCreatedAt,
            tagCreatedAt: $tagCreatedAt,
        );

        $deployJobName = 'Production-AWS';
        $pipelineJobsFixture = $this->pipelineJobsResponseFixture(Job\Name::fromString($deployJobName));
        $deployJob = $pipelineJobsFixture[0];

        $frontendPipelineId = Pipeline\PipelineId::from(1);
        $tagPipelineId = Pipeline\PipelineId::from(2);

        $publication = new ReleasePublication(
            id: ReleasePublicationId::fromBranchName($releaseBranchName),
            branchName: $releaseBranchName,
            status: new StatusReleaseCandidateCreated(),
            tagName: null,
            tagMessage: null,
            readyToMergeTasks: $tasks,
            createdAt: $now,
        );

        $releasePublicationRepository->store($publication);

        $mock = new MockHandler([
            $this->createMergeRequestResponse(
                mergeRequestIid: $frontendMrToMerge->iid,
                projectId: $frontendMrToMerge->projectId,
                projectName: $frontendMrToMerge->projectName,
                title: $frontendMrToMerge->title,
                sourceBranchName: $frontendMrToMerge->sourceBranchName,
                targetBranchName: $frontendMrToMerge->targetBranchName,
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
                targetBranchName: $frontendMrToMerge->targetBranchName,
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
                targetBranchName: $frontendMrToMerge->targetBranchName,
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
                targetBranchName: $frontendMrToMerge->targetBranchName,
                status: MergeRequest\Status::Open,
                detailedStatus: MergeRequest\Details\Status\Dictionary::CiStillRunning,
                guiUrl: $frontendMrToMerge->guiUrl,
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
                    jobId: Job\JobId::from($deployJob['id']),
                    jobName: Job\Name::fromString($deployJobName),
                    ref: Ref::fromString($tagFixture['commit']['id']),
                    createdAt: $tagCreatedAt->add(new \DateInterval('PT10M')),
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
        ]);

        /** @var Client $httpClient */
        $httpClient = $container->get('eight_points_guzzle.client.gitlab');
        /** @var HandlerStack $handlerStack */
        $handlerStack = $httpClient->getConfig('handler');
        $handlerStack->setHandler($mock);

        $mock = new MockHandler([
            new Response(
                status: 200,
                body: json_encode($this->versionsResponseFixture(
                    latestVersionName: $latestReleaseVersionName,
                )),
            ),
            new Response(
                status: 200,
                body: json_encode($this->versionResponseFixture(
                    versionName: $latestReleaseVersionName,
                    released: true,
                )),
            ),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $container->set('eight_points_guzzle.client.jira', new Client(['handler' => $handlerStack]));

        $tagName = Tag\VersionName::create();
        $tagMessage = Tag\Message::fromString("Release $tagName.");

        $publishReleaseCommand = new PublishReleaseCommand(
            id: $publication->id(),
            tagName: $tagName,
            tagMessage: $tagMessage,
        );

        static::mockTime($now->sub(new \DateInterval('PT1M')));

        $commandBus->dispatch($publishReleaseCommand);

        $this->assertObjectEquals($tagName, $publication->tagName());
        $this->assertObjectEquals($tagMessage, $publication->tagMessage());
        $this->assertObjectEquals(
            expected: new StatusVersionReleased(),
            actual: $publication->status(),
        );

        $dispatchedEvents = $eventBus->getDispatchedEvents();

        $this->assertCount(76, $dispatchedEvents);

        $this->assertArrayHasKey(0, $dispatchedEvents);
        $event = $dispatchedEvents[0]->event;
        $this->assertInstanceOf(MergeRequestCreated::class, $event);
        $this->assertObjectEquals($frontendProjectId, $event->projectId);
        $this->assertObjectEquals($frontendMrToMerge->title, $event->title);
        $this->assertObjectEquals(Branch\Name::fromString((string) $releaseBranchName), $event->sourceBranchName);
        $this->assertObjectEquals($productionReleaseBranchName, $event->targetBranchName);
        $this->assertObjectEquals(new MergeRequest\Details\Status\StatusNotOpen(), $event->details->status);

        $this->assertArrayHasKey(1, $dispatchedEvents);
        $event = $dispatchedEvents[1]->event;
        $this->assertInstanceOf(ReleasePublicationTagSet::class, $event);
        $this->assertObjectEquals(new StatusReleaseCandidateCreated(), $event->status);
        $this->assertObjectEquals($tagName, $event->tagName);
        $this->assertObjectEquals($tagMessage, $event->tagMessage);

        $this->assertArrayHasKey(2, $dispatchedEvents);
        $event = $dispatchedEvents[2]->event;
        $this->assertInstanceOf(ReleasePublicationStatusChanged::class, $event);
        $this->assertObjectEquals(new StatusReleaseCandidateCreated(), $event->previousStatus);
        $this->assertObjectEquals(
            expected: new StatusFrontendMergeRequestIntoProductionReleaseBranchCreated([
                'project_id' => $frontendProjectId->value(),
                'merge_request_iid' => $frontendMrToMerge->iid->value(),
            ]),
            actual: $event->status,
        );

        $this->assertArrayHasKey(3, $dispatchedEvents);
        $event = $dispatchedEvents[3]->event;
        $this->assertInstanceOf(MergeRequestAwaitingTick::class, $event);
        $this->assertObjectEquals($frontendProjectId, $event->projectId);
        $this->assertObjectEquals($frontendMrToMerge->iid, $event->mergeRequestIid);
        $this->assertObjectEquals($frontendMrToMerge->title, $event->title);
        $this->assertObjectEquals($frontendMrToMerge->sourceBranchName, $event->sourceBranchName);
        $this->assertObjectEquals($frontendMrToMerge->targetBranchName, $event->targetBranchName);
        $this->assertObjectEquals($frontendMrToMerge->details->withStatus(new MergeRequest\Details\Status\StatusNotOpen()), $event->details);

        $this->assertArrayHasKey(4, $dispatchedEvents);
        $event = $dispatchedEvents[4]->event;
        $this->assertInstanceOf(MergeRequestStatusChanged::class, $event);
        $this->assertObjectEquals($frontendProjectId, $event->projectId);
        $this->assertObjectEquals($frontendMrToMerge->iid, $event->mergeRequestIid);
        $this->assertObjectEquals($frontendMrToMerge->title, $event->title);
        $this->assertObjectEquals($frontendMrToMerge->sourceBranchName, $event->sourceBranchName);
        $this->assertObjectEquals($frontendMrToMerge->targetBranchName, $event->targetBranchName);
        $this->assertObjectEquals($frontendMrToMerge->details->withStatus(new MergeRequest\Details\Status\StatusPreparing()), $event->details);

        $this->assertArrayHasKey(5, $dispatchedEvents);
        $event = $dispatchedEvents[5]->event;
        $this->assertInstanceOf(MergeRequestAwaitingTick::class, $event);
        $this->assertObjectEquals($frontendProjectId, $event->projectId);
        $this->assertObjectEquals($frontendMrToMerge->iid, $event->mergeRequestIid);
        $this->assertObjectEquals($frontendMrToMerge->title, $event->title);
        $this->assertObjectEquals($frontendMrToMerge->sourceBranchName, $event->sourceBranchName);
        $this->assertObjectEquals($frontendMrToMerge->targetBranchName, $event->targetBranchName);
        $this->assertObjectEquals($frontendMrToMerge->details->withStatus(new MergeRequest\Details\Status\StatusPreparing()), $event->details);

        $this->assertArrayHasKey(6, $dispatchedEvents);
        $event = $dispatchedEvents[6]->event;
        $this->assertInstanceOf(MergeRequestStatusChanged::class, $event);
        $this->assertObjectEquals($frontendProjectId, $event->projectId);
        $this->assertObjectEquals($frontendMrToMerge->iid, $event->mergeRequestIid);
        $this->assertObjectEquals($frontendMrToMerge->title, $event->title);
        $this->assertObjectEquals($frontendMrToMerge->sourceBranchName, $event->sourceBranchName);
        $this->assertObjectEquals($frontendMrToMerge->targetBranchName, $event->targetBranchName);
        $this->assertObjectEquals($frontendMrToMerge->details->withStatus(new MergeRequest\Details\Status\StatusCiStillRunning()), $event->details);

        $this->assertArrayHasKey(7, $dispatchedEvents);
        $event = $dispatchedEvents[7]->event;
        $this->assertInstanceOf(MergeRequestAwaitingTick::class, $event);
        $this->assertObjectEquals($frontendProjectId, $event->projectId);
        $this->assertObjectEquals($frontendMrToMerge->iid, $event->mergeRequestIid);
        $this->assertObjectEquals($frontendMrToMerge->title, $event->title);
        $this->assertObjectEquals($frontendMrToMerge->sourceBranchName, $event->sourceBranchName);
        $this->assertObjectEquals($frontendMrToMerge->targetBranchName, $event->targetBranchName);
        $this->assertObjectEquals($frontendMrToMerge->details->withStatus(new MergeRequest\Details\Status\StatusCiStillRunning()), $event->details);

        $this->assertArrayHasKey(8, $dispatchedEvents);
        $event = $dispatchedEvents[8]->event;
        $this->assertInstanceOf(MergeRequestStatusChanged::class, $event);
        $this->assertObjectEquals($frontendProjectId, $event->projectId);
        $this->assertObjectEquals($frontendMrToMerge->iid, $event->mergeRequestIid);
        $this->assertObjectEquals($frontendMrToMerge->title, $event->title);
        $this->assertObjectEquals($frontendMrToMerge->sourceBranchName, $event->sourceBranchName);
        $this->assertObjectEquals($frontendMrToMerge->targetBranchName, $event->targetBranchName);
        $this->assertObjectEquals($frontendMrToMerge->details->withStatus(new MergeRequest\Details\Status\StatusMergeable()), $event->details);

        $this->assertArrayHasKey(9, $dispatchedEvents);
        $event = $dispatchedEvents[9]->event;
        $this->assertInstanceOf(MergeRequestMerged::class, $event);
        $this->assertObjectEquals($frontendProjectId, $event->projectId);
        $this->assertObjectEquals($frontendMrToMerge->iid, $event->mergeRequestIid);
        $this->assertObjectEquals($frontendMrToMerge->title, $event->title);
        $this->assertObjectEquals($frontendMrToMerge->sourceBranchName, $event->sourceBranchName);
        $this->assertObjectEquals($frontendMrToMerge->targetBranchName, $event->targetBranchName);
        $this->assertObjectEquals($frontendMrToMerge->details, $event->details);

        $this->assertArrayHasKey(10, $dispatchedEvents);
        $event = $dispatchedEvents[10]->event;
        $this->assertInstanceOf(ReleasePublicationStatusChanged::class, $event);
        $this->assertObjectEquals(
            expected: new StatusFrontendMergeRequestIntoProductionReleaseBranchCreated([
                'project_id' => $frontendProjectId->value(),
                'merge_request_iid' => $frontendMrToMerge->iid->value(),
            ]),
            actual: $event->previousStatus,
        );
        $this->assertObjectEquals(new StatusFrontendMergeRequestIntoProductionReleaseBranchMerged(), $event->status);

        $this->assertArrayHasKey(11, $dispatchedEvents);
        $event = $dispatchedEvents[11]->event;
        $this->assertInstanceOf(LatestPipelineStatusChanged::class, $event);
        $this->assertObjectEquals($frontendProjectId, $event->projectId);
        $this->assertNull($event->previousStatus);
        $this->assertObjectEquals(Pipeline\Status::Created, $event->status);
        $pipelineId = $event->pipelineId;

        $this->assertArrayHasKey(12, $dispatchedEvents);
        $event = $dispatchedEvents[12]->event;
        $this->assertInstanceOf(LatestPipelineAwaitingTick::class, $event);
        $this->assertObjectEquals($frontendProjectId, $event->projectId);
        $this->assertObjectEquals($pipelineId, $event->pipelineId);
        $this->assertObjectEquals(Pipeline\Status::Created, $event->status);

        $this->assertArrayHasKey(13, $dispatchedEvents);
        $event = $dispatchedEvents[13]->event;
        $this->assertInstanceOf(LatestPipelineStatusChanged::class, $event);
        $this->assertObjectEquals($frontendProjectId, $event->projectId);
        $this->assertObjectEquals(Pipeline\Status::Created, $event->previousStatus);
        $this->assertObjectEquals(Pipeline\Status::WaitingForResource, $event->status);

        $this->assertArrayHasKey(14, $dispatchedEvents);
        $event = $dispatchedEvents[14]->event;
        $this->assertInstanceOf(LatestPipelineAwaitingTick::class, $event);
        $this->assertObjectEquals($frontendProjectId, $event->projectId);
        $this->assertObjectEquals($pipelineId, $event->pipelineId);
        $this->assertObjectEquals(Pipeline\Status::WaitingForResource, $event->status);

        $this->assertArrayHasKey(15, $dispatchedEvents);
        $event = $dispatchedEvents[15]->event;
        $this->assertInstanceOf(LatestPipelineStatusChanged::class, $event);
        $this->assertObjectEquals($frontendProjectId, $event->projectId);
        $this->assertObjectEquals(Pipeline\Status::WaitingForResource, $event->previousStatus);
        $this->assertObjectEquals(Pipeline\Status::Preparing, $event->status);

        $this->assertArrayHasKey(16, $dispatchedEvents);
        $event = $dispatchedEvents[16]->event;
        $this->assertInstanceOf(LatestPipelineAwaitingTick::class, $event);
        $this->assertObjectEquals($frontendProjectId, $event->projectId);
        $this->assertObjectEquals($pipelineId, $event->pipelineId);
        $this->assertObjectEquals(Pipeline\Status::Preparing, $event->status);

        $this->assertArrayHasKey(17, $dispatchedEvents);
        $event = $dispatchedEvents[17]->event;
        $this->assertInstanceOf(LatestPipelineStatusChanged::class, $event);
        $this->assertObjectEquals($frontendProjectId, $event->projectId);
        $this->assertObjectEquals(Pipeline\Status::Preparing, $event->previousStatus);
        $this->assertObjectEquals(Pipeline\Status::Pending, $event->status);

        $this->assertArrayHasKey(18, $dispatchedEvents);
        $event = $dispatchedEvents[18]->event;
        $this->assertInstanceOf(LatestPipelineAwaitingTick::class, $event);
        $this->assertObjectEquals($frontendProjectId, $event->projectId);
        $this->assertObjectEquals($pipelineId, $event->pipelineId);
        $this->assertObjectEquals(Pipeline\Status::Pending, $event->status);

        $this->assertArrayHasKey(19, $dispatchedEvents);
        $event = $dispatchedEvents[19]->event;
        $this->assertInstanceOf(LatestPipelineStatusChanged::class, $event);
        $this->assertObjectEquals($frontendProjectId, $event->projectId);
        $this->assertObjectEquals(Pipeline\Status::Pending, $event->previousStatus);
        $this->assertObjectEquals(Pipeline\Status::Running, $event->status);

        $this->assertArrayHasKey(20, $dispatchedEvents);
        $event = $dispatchedEvents[20]->event;
        $this->assertInstanceOf(LatestPipelineAwaitingTick::class, $event);
        $this->assertObjectEquals($frontendProjectId, $event->projectId);
        $this->assertObjectEquals($pipelineId, $event->pipelineId);
        $this->assertObjectEquals(Pipeline\Status::Running, $event->status);

        $this->assertArrayHasKey(21, $dispatchedEvents);
        $event = $dispatchedEvents[21]->event;
        $this->assertInstanceOf(LatestPipelineStatusChanged::class, $event);
        $this->assertObjectEquals($frontendProjectId, $event->projectId);
        $this->assertObjectEquals(Pipeline\Status::Running, $event->previousStatus);
        $this->assertObjectEquals(Pipeline\Status::Failed, $event->status);

        $this->assertArrayHasKey(22, $dispatchedEvents);
        $event = $dispatchedEvents[22]->event;
        $this->assertInstanceOf(ReleasePublicationStatusChanged::class, $event);
        $this->assertObjectEquals(new StatusFrontendMergeRequestIntoProductionReleaseBranchMerged(), $event->previousStatus);
        $this->assertObjectEquals(
            expected: new StatusFrontendProductionReleaseBranchPipelineFailed(['pipeline_id' => $frontendPipelineId->value()]),
            actual: $event->status,
        );

        $this->assertArrayHasKey(23, $dispatchedEvents);
        $event = $dispatchedEvents[23]->event;
        $this->assertInstanceOf(ReleasePublicationStatusChanged::class, $event);
        $this->assertObjectEquals(
            expected: new StatusFrontendProductionReleaseBranchPipelineFailed(['pipeline_id' => $frontendPipelineId->value()]),
            actual: $event->previousStatus,
        );
        $this->assertObjectEquals(
            expected: new StatusFrontendProductionReleaseBranchPipelinePending([
                'retry_counter' => 1,
                'pipeline_id' => $frontendPipelineId->value(),
            ]),
            actual: $event->status,
        );

        $this->assertArrayHasKey(24, $dispatchedEvents);
        $event = $dispatchedEvents[24]->event;
        $this->assertInstanceOf(LatestPipelineStatusChanged::class, $event);
        $this->assertObjectEquals($frontendProjectId, $event->projectId);
        $this->assertNull($event->previousStatus);
        $this->assertObjectEquals(Pipeline\Status::Pending, $event->status);

        $this->assertArrayHasKey(25, $dispatchedEvents);
        $event = $dispatchedEvents[25]->event;
        $this->assertInstanceOf(LatestPipelineAwaitingTick::class, $event);
        $this->assertObjectEquals($frontendProjectId, $event->projectId);
        $this->assertObjectEquals($pipelineId, $event->pipelineId);
        $this->assertObjectEquals(Pipeline\Status::Pending, $event->status);

        $this->assertArrayHasKey(26, $dispatchedEvents);
        $event = $dispatchedEvents[26]->event;
        $this->assertInstanceOf(LatestPipelineStatusChanged::class, $event);
        $this->assertObjectEquals($frontendProjectId, $event->projectId);
        $this->assertObjectEquals(Pipeline\Status::Pending, $event->previousStatus);
        $this->assertObjectEquals(Pipeline\Status::Running, $event->status);

        $this->assertArrayHasKey(27, $dispatchedEvents);
        $event = $dispatchedEvents[27]->event;
        $this->assertInstanceOf(LatestPipelineAwaitingTick::class, $event);
        $this->assertObjectEquals($frontendProjectId, $event->projectId);
        $this->assertObjectEquals($pipelineId, $event->pipelineId);
        $this->assertObjectEquals(Pipeline\Status::Running, $event->status);

        $this->assertArrayHasKey(28, $dispatchedEvents);
        $event = $dispatchedEvents[28]->event;
        $this->assertInstanceOf(LatestPipelineStatusChanged::class, $event);
        $this->assertObjectEquals($frontendProjectId, $event->projectId);
        $this->assertObjectEquals(Pipeline\Status::Running, $event->previousStatus);
        $this->assertObjectEquals(Pipeline\Status::Success, $event->status);

        $this->assertArrayHasKey(29, $dispatchedEvents);
        $event = $dispatchedEvents[29]->event;
        $this->assertInstanceOf(ReleasePublicationStatusChanged::class, $event);
        $this->assertObjectEquals(
            expected: new StatusFrontendProductionReleaseBranchPipelinePending([
                'retry_counter' => 1,
                'pipeline_id' => $frontendPipelineId->value(),
            ]),
            actual: $event->previousStatus,
        );
        $this->assertObjectEquals(
            expected: new StatusFrontendProductionReleaseBranchPipelineSuccess([
                'retry_counter' => 1,
                'pipeline_id' => $frontendPipelineId->value(),
            ]),
            actual: $event->status,
        );

        $this->assertArrayHasKey(30, $dispatchedEvents);
        $event = $dispatchedEvents[30]->event;
        $this->assertInstanceOf(MergeRequestCreated::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals($backendMrToMerge->title, $event->title);
        $this->assertObjectEquals(Branch\Name::fromString((string) $releaseBranchName), $event->sourceBranchName);
        $this->assertObjectEquals($productionReleaseBranchName, $event->targetBranchName);
        $this->assertObjectEquals(new MergeRequest\Details\Status\StatusMergeable(), $event->details->status);

        $this->assertArrayHasKey(31, $dispatchedEvents);
        $event = $dispatchedEvents[31]->event;
        $this->assertInstanceOf(ReleasePublicationStatusChanged::class, $event);
        $this->assertObjectEquals(
            expected: new StatusFrontendProductionReleaseBranchPipelineSuccess([
                'retry_counter' => 1,
                'pipeline_id' => $frontendPipelineId->value(),
            ]),
            actual: $event->previousStatus,
        );
        $this->assertObjectEquals(
            expected: new StatusBackendMergeRequestIntoProductionReleaseBranchCreated([
                'project_id' => $backendProjectId->value(),
                'merge_request_iid' => $backendMrToMerge->iid->value(),
            ]),
            actual: $event->status,
        );

        $this->assertArrayHasKey(32, $dispatchedEvents);
        $event = $dispatchedEvents[32]->event;
        $this->assertInstanceOf(MergeRequestMerged::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals($backendMrToMerge->iid, $event->mergeRequestIid);
        $this->assertObjectEquals($backendMrToMerge->title, $event->title);
        $this->assertObjectEquals($backendMrToMerge->sourceBranchName, $event->sourceBranchName);
        $this->assertObjectEquals($backendMrToMerge->targetBranchName, $event->targetBranchName);
        $this->assertObjectEquals($backendMrToMerge->details, $event->details);

        $this->assertArrayHasKey(33, $dispatchedEvents);
        $event = $dispatchedEvents[33]->event;
        $this->assertInstanceOf(ReleasePublicationStatusChanged::class, $event);
        $this->assertObjectEquals(
            expected: new StatusBackendMergeRequestIntoProductionReleaseBranchCreated([
                'project_id' => $backendProjectId->value(),
                'merge_request_iid' => $backendMrToMerge->iid->value(),
            ]),
            actual: $event->previousStatus,
        );
        $this->assertObjectEquals(new StatusBackendMergeRequestIntoProductionReleaseBranchMerged(), $event->status);

        $this->assertArrayHasKey(34, $dispatchedEvents);
        $event = $dispatchedEvents[34]->event;
        $this->assertInstanceOf(TagCreated::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals($tagName, $event->name);
        $this->assertObjectEquals($tagMessage, $event->message);

        $this->assertArrayHasKey(35, $dispatchedEvents);
        $event = $dispatchedEvents[35]->event;
        $this->assertInstanceOf(ReleasePublicationStatusChanged::class, $event);
        $this->assertObjectEquals(new StatusBackendMergeRequestIntoProductionReleaseBranchMerged(), $event->previousStatus);
        $this->assertObjectEquals(new StatusTagCreated(), $event->status);

        $this->assertArrayHasKey(36, $dispatchedEvents);
        $event = $dispatchedEvents[36]->event;
        $this->assertInstanceOf(LatestPipelineStatusChanged::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertNull($event->previousStatus);
        $this->assertObjectEquals(Pipeline\Status::Created, $event->status);
        $pipelineId = $event->pipelineId;

        $this->assertArrayHasKey(37, $dispatchedEvents);
        $event = $dispatchedEvents[37]->event;
        $this->assertInstanceOf(LatestPipelineAwaitingTick::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals($pipelineId, $event->pipelineId);
        $this->assertObjectEquals(Pipeline\Status::Created, $event->status);

        $this->assertArrayHasKey(38, $dispatchedEvents);
        $event = $dispatchedEvents[38]->event;
        $this->assertInstanceOf(LatestPipelineStatusChanged::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals(Pipeline\Status::Created, $event->previousStatus);
        $this->assertObjectEquals(Pipeline\Status::WaitingForResource, $event->status);

        $this->assertArrayHasKey(39, $dispatchedEvents);
        $event = $dispatchedEvents[39]->event;
        $this->assertInstanceOf(LatestPipelineAwaitingTick::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals($pipelineId, $event->pipelineId);
        $this->assertObjectEquals(Pipeline\Status::WaitingForResource, $event->status);

        $this->assertArrayHasKey(40, $dispatchedEvents);
        $event = $dispatchedEvents[40]->event;
        $this->assertInstanceOf(LatestPipelineStatusChanged::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals(Pipeline\Status::WaitingForResource, $event->previousStatus);
        $this->assertObjectEquals(Pipeline\Status::Preparing, $event->status);

        $this->assertArrayHasKey(41, $dispatchedEvents);
        $event = $dispatchedEvents[41]->event;
        $this->assertInstanceOf(LatestPipelineAwaitingTick::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals($pipelineId, $event->pipelineId);
        $this->assertObjectEquals(Pipeline\Status::Preparing, $event->status);

        $this->assertArrayHasKey(42, $dispatchedEvents);
        $event = $dispatchedEvents[42]->event;
        $this->assertInstanceOf(LatestPipelineStatusChanged::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals(Pipeline\Status::Preparing, $event->previousStatus);
        $this->assertObjectEquals(Pipeline\Status::Pending, $event->status);

        $this->assertArrayHasKey(43, $dispatchedEvents);
        $event = $dispatchedEvents[43]->event;
        $this->assertInstanceOf(LatestPipelineAwaitingTick::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals($pipelineId, $event->pipelineId);
        $this->assertObjectEquals(Pipeline\Status::Pending, $event->status);

        $this->assertArrayHasKey(44, $dispatchedEvents);
        $event = $dispatchedEvents[44]->event;
        $this->assertInstanceOf(LatestPipelineStatusChanged::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals(Pipeline\Status::Pending, $event->previousStatus);
        $this->assertObjectEquals(Pipeline\Status::Running, $event->status);

        $this->assertArrayHasKey(45, $dispatchedEvents);
        $event = $dispatchedEvents[45]->event;
        $this->assertInstanceOf(LatestPipelineAwaitingTick::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals($pipelineId, $event->pipelineId);
        $this->assertObjectEquals(Pipeline\Status::Running, $event->status);

        $this->assertArrayHasKey(46, $dispatchedEvents);
        $event = $dispatchedEvents[46]->event;
        $this->assertInstanceOf(LatestPipelineStatusChanged::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals(Pipeline\Status::Running, $event->previousStatus);
        $this->assertObjectEquals(Pipeline\Status::Failed, $event->status);

        $this->assertArrayHasKey(47, $dispatchedEvents);
        $event = $dispatchedEvents[47]->event;
        $this->assertInstanceOf(ReleasePublicationStatusChanged::class, $event);
        $this->assertObjectEquals(new StatusTagCreated(), $event->previousStatus);
        $this->assertObjectEquals(
            expected: new StatusTagPipelineFailed(['pipeline_id' => $tagPipelineId->value()]),
            actual: $event->status,
        );

        $this->assertArrayHasKey(48, $dispatchedEvents);
        $event = $dispatchedEvents[48]->event;
        $this->assertInstanceOf(ReleasePublicationStatusChanged::class, $event);
        $this->assertObjectEquals(
            expected: new StatusTagPipelineFailed(['pipeline_id' => $tagPipelineId->value()]),
            actual: $event->previousStatus,
        );
        $this->assertObjectEquals(
            expected: new StatusTagPipelinePending([
                'retry_counter' => 1,
                'pipeline_id' => $tagPipelineId->value(),
            ]),
            actual: $event->status,
        );

        $this->assertArrayHasKey(49, $dispatchedEvents);
        $event = $dispatchedEvents[49]->event;
        $this->assertInstanceOf(LatestPipelineStatusChanged::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertNull($event->previousStatus);
        $this->assertObjectEquals(Pipeline\Status::Pending, $event->status);

        $this->assertArrayHasKey(50, $dispatchedEvents);
        $event = $dispatchedEvents[50]->event;
        $this->assertInstanceOf(LatestPipelineAwaitingTick::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals($pipelineId, $event->pipelineId);
        $this->assertObjectEquals(Pipeline\Status::Pending, $event->status);

        $this->assertArrayHasKey(51, $dispatchedEvents);
        $event = $dispatchedEvents[51]->event;
        $this->assertInstanceOf(LatestPipelineStatusChanged::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals(Pipeline\Status::Pending, $event->previousStatus);
        $this->assertObjectEquals(Pipeline\Status::Running, $event->status);

        $this->assertArrayHasKey(52, $dispatchedEvents);
        $event = $dispatchedEvents[52]->event;
        $this->assertInstanceOf(LatestPipelineAwaitingTick::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals($pipelineId, $event->pipelineId);
        $this->assertObjectEquals(Pipeline\Status::Running, $event->status);

        $this->assertArrayHasKey(53, $dispatchedEvents);
        $event = $dispatchedEvents[53]->event;
        $this->assertInstanceOf(LatestPipelineStatusChanged::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals(Pipeline\Status::Running, $event->previousStatus);
        $this->assertObjectEquals(Pipeline\Status::Success, $event->status);

        $this->assertArrayHasKey(54, $dispatchedEvents);
        $event = $dispatchedEvents[54]->event;
        $this->assertInstanceOf(ReleasePublicationStatusChanged::class, $event);
        $this->assertObjectEquals(
            expected: new StatusTagPipelinePending([
                'retry_counter' => 1,
                'pipeline_id' => $tagPipelineId->value(),
            ]),
            actual: $event->previousStatus,
        );
        $this->assertObjectEquals(
            expected: new StatusTagPipelineSuccess([
                'retry_counter' => 1,
                'pipeline_id' => $tagPipelineId->value(),
            ]),
            actual: $event->status,
        );

        $this->assertArrayHasKey(55, $dispatchedEvents);
        $event = $dispatchedEvents[55]->event;
        $this->assertInstanceOf(JobRan::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals($pipelineId, $event->pipelineId);
        $this->assertObjectEquals(Job\JobId::from($deployJob['id']), $event->jobId);
        $this->assertObjectEquals(Job\Name::fromString($deployJobName), $event->name);

        $this->assertArrayHasKey(56, $dispatchedEvents);
        $event = $dispatchedEvents[56]->event;
        $this->assertInstanceOf(ReleasePublicationStatusChanged::class, $event);
        $this->assertObjectEquals(
            expected: new StatusTagPipelineSuccess([
                'retry_counter' => 1,
                'pipeline_id' => $tagPipelineId->value(),
            ]),
            actual: $event->previousStatus,
        );
        $this->assertObjectEquals(new StatusDeploymentJobInited(), $event->status);

        $this->assertArrayHasKey(57, $dispatchedEvents);
        $event = $dispatchedEvents[57]->event;
        $this->assertInstanceOf(LatestPipelineStatusChanged::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertNull($event->previousStatus);
        $this->assertObjectEquals(Pipeline\Status::WaitingForResource, $event->status);

        $this->assertArrayHasKey(58, $dispatchedEvents);
        $event = $dispatchedEvents[58]->event;
        $this->assertInstanceOf(LatestPipelineAwaitingTick::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals($pipelineId, $event->pipelineId);
        $this->assertObjectEquals(Pipeline\Status::WaitingForResource, $event->status);

        $this->assertArrayHasKey(59, $dispatchedEvents);
        $event = $dispatchedEvents[59]->event;
        $this->assertInstanceOf(LatestPipelineStatusChanged::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals(Pipeline\Status::WaitingForResource, $event->previousStatus);
        $this->assertObjectEquals(Pipeline\Status::Preparing, $event->status);

        $this->assertArrayHasKey(60, $dispatchedEvents);
        $event = $dispatchedEvents[60]->event;
        $this->assertInstanceOf(LatestPipelineAwaitingTick::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals($pipelineId, $event->pipelineId);
        $this->assertObjectEquals(Pipeline\Status::Preparing, $event->status);

        $this->assertArrayHasKey(61, $dispatchedEvents);
        $event = $dispatchedEvents[61]->event;
        $this->assertInstanceOf(LatestPipelineStatusChanged::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals(Pipeline\Status::Preparing, $event->previousStatus);
        $this->assertObjectEquals(Pipeline\Status::Pending, $event->status);

        $this->assertArrayHasKey(62, $dispatchedEvents);
        $event = $dispatchedEvents[62]->event;
        $this->assertInstanceOf(LatestPipelineAwaitingTick::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals($pipelineId, $event->pipelineId);
        $this->assertObjectEquals(Pipeline\Status::Pending, $event->status);

        $this->assertArrayHasKey(63, $dispatchedEvents);
        $event = $dispatchedEvents[63]->event;
        $this->assertInstanceOf(LatestPipelineStatusChanged::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals(Pipeline\Status::Pending, $event->previousStatus);
        $this->assertObjectEquals(Pipeline\Status::Running, $event->status);

        $this->assertArrayHasKey(64, $dispatchedEvents);
        $event = $dispatchedEvents[64]->event;
        $this->assertInstanceOf(LatestPipelineAwaitingTick::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals($pipelineId, $event->pipelineId);
        $this->assertObjectEquals(Pipeline\Status::Running, $event->status);

        $this->assertArrayHasKey(65, $dispatchedEvents);
        $event = $dispatchedEvents[65]->event;
        $this->assertInstanceOf(LatestPipelineStatusChanged::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals(Pipeline\Status::Running, $event->previousStatus);
        $this->assertObjectEquals(Pipeline\Status::Failed, $event->status);

        $this->assertArrayHasKey(66, $dispatchedEvents);
        $event = $dispatchedEvents[66]->event;
        $this->assertInstanceOf(ReleasePublicationStatusChanged::class, $event);
        $this->assertObjectEquals(new StatusDeploymentJobInited(), $event->previousStatus);
        $this->assertObjectEquals(
            expected: new StatusDeploymentPipelineFailed(['pipeline_id' => $tagPipelineId->value()]),
            actual: $event->status,
        );

        $this->assertArrayHasKey(67, $dispatchedEvents);
        $event = $dispatchedEvents[67]->event;
        $this->assertInstanceOf(ReleasePublicationStatusChanged::class, $event);
        $this->assertObjectEquals(
            expected: new StatusDeploymentPipelineFailed(['pipeline_id' => $tagPipelineId->value()]),
            actual: $event->previousStatus,
        );
        $this->assertObjectEquals(
            expected: new StatusDeploymentPipelinePending([
                'retry_counter' => 1,
                'pipeline_id' => $tagPipelineId->value(),
            ]),
            actual: $event->status,
        );

        $this->assertArrayHasKey(68, $dispatchedEvents);
        $event = $dispatchedEvents[68]->event;
        $this->assertInstanceOf(LatestPipelineStatusChanged::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertNull($event->previousStatus);
        $this->assertObjectEquals(Pipeline\Status::Pending, $event->status);

        $this->assertArrayHasKey(69, $dispatchedEvents);
        $event = $dispatchedEvents[69]->event;
        $this->assertInstanceOf(LatestPipelineAwaitingTick::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals($pipelineId, $event->pipelineId);
        $this->assertObjectEquals(Pipeline\Status::Pending, $event->status);

        $this->assertArrayHasKey(70, $dispatchedEvents);
        $event = $dispatchedEvents[70]->event;
        $this->assertInstanceOf(LatestPipelineStatusChanged::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals(Pipeline\Status::Pending, $event->previousStatus);
        $this->assertObjectEquals(Pipeline\Status::Running, $event->status);

        $this->assertArrayHasKey(71, $dispatchedEvents);
        $event = $dispatchedEvents[71]->event;
        $this->assertInstanceOf(LatestPipelineAwaitingTick::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals($pipelineId, $event->pipelineId);
        $this->assertObjectEquals(Pipeline\Status::Running, $event->status);

        $this->assertArrayHasKey(72, $dispatchedEvents);
        $event = $dispatchedEvents[72]->event;
        $this->assertInstanceOf(LatestPipelineStatusChanged::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals(Pipeline\Status::Running, $event->previousStatus);
        $this->assertObjectEquals(Pipeline\Status::Success, $event->status);

        $this->assertArrayHasKey(73, $dispatchedEvents);
        $event = $dispatchedEvents[73]->event;
        $this->assertInstanceOf(ReleasePublicationStatusChanged::class, $event);
        $this->assertObjectEquals(
            expected: new StatusDeploymentPipelinePending([
                'retry_counter' => 1,
                'pipeline_id' => $tagPipelineId->value(),
            ]),
            actual: $event->previousStatus,
        );
        $this->assertObjectEquals(
            expected: new StatusDeploymentPipelineSuccess([
                'retry_counter' => 1,
                'pipeline_id' => $tagPipelineId->value(),
            ]),
            actual: $event->status,
        );

        $this->assertArrayHasKey(74, $dispatchedEvents);
        $event = $dispatchedEvents[74]->event;
        $this->assertInstanceOf(ReleaseReleased::class, $event);
        $this->assertObjectEquals(Version\Name::fromString((string) $releaseBranchName), $event->name);
        $this->assertTrue($event->released);

        $this->assertArrayHasKey(75, $dispatchedEvents);
        $event = $dispatchedEvents[75]->event;
        $this->assertInstanceOf(ReleasePublicationStatusChanged::class, $event);
        $this->assertObjectEquals(
            expected: new StatusDeploymentPipelineSuccess([
                'retry_counter' => 1,
                'pipeline_id' => $tagPipelineId->value(),
            ]),
            actual: $event->previousStatus,
        );
        $this->assertObjectEquals(new StatusVersionReleased(), $event->status);
    }
}
