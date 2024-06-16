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
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\Status\StatusDeploymentJobInited;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\Status\StatusDeploymentPipelineSuccess;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\Status\StatusDevelopBranchSynchronized;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\Status\StatusDone;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\Status\StatusFrontendApplicationBranchSet;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\Status\StatusFrontendPipelineSuccess;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\Status\StatusHotfixesTransitionedToDone;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\Status\StatusMergeRequestsIntoDevelopCreated;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\Status\StatusMergeRequestsIntoReleaseCreated;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\Status\StatusMergeRequestsMerged;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\Status\StatusReleaseBranchSynchronized;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\Status\StatusTagCreated;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\Status\StatusTagPipelineSuccess;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\Status\StatusUpdateExtraDeployBranchMergeRequestCreated;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Repository\HotfixPublicationRepositoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Event\ContinuousIntegration\Job\JobRan;
use Invis1ble\ProjectManagement\Shared\Domain\Event\ContinuousIntegration\Pipeline\LatestPipelineAwaitingTick;
use Invis1ble\ProjectManagement\Shared\Domain\Event\ContinuousIntegration\Pipeline\LatestPipelineStatusChanged;
use Invis1ble\ProjectManagement\Shared\Domain\Event\DevelopmentCollaboration\MergeRequest\MergeRequestCreated;
use Invis1ble\ProjectManagement\Shared\Domain\Event\DevelopmentCollaboration\MergeRequest\MergeRequestMerged;
use Invis1ble\ProjectManagement\Shared\Domain\Event\SourceCodeRepository\Commit\CommitCreated;
use Invis1ble\ProjectManagement\Shared\Domain\Event\SourceCodeRepository\Tag\TagCreated;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Job;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Pipeline\Status;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Branch;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Commit;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Tag;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Board\BoardId;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Project;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Sprint\State;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Version;
use Invis1ble\ProjectManagement\Tests\Shared\Infrastructure\Domain\Model\TaskTracker\Issue\CreateIssuesTrait;
use Invis1ble\ProjectManagement\Tests\Shared\Infrastructure\Domain\Model\TaskTracker\Issue\MapMergeRequestsToMergeToMergedTrait;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Clock\Test\ClockSensitiveTrait;

class HotfixPublicationSagaTest extends KernelTestCase
{
    use ClockSensitiveTrait;
    use CreateIssuesTrait;
    use MapMergeRequestsToMergeToMergedTrait;

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

        $tag = file_get_contents(__DIR__ . '/fixture/tag/response/create_tag.200.json');
        $tag = json_decode($tag, true);

        $pipelineJobs = file_get_contents(__DIR__ . '/fixture/job/response/pipeline_jobs.200.json');
        $pipelineJobs = json_decode($pipelineJobs, true);

        $deployJobName = 'Production-AWS';

        $deployJob = $pipelineJobs[0];
        $pipelineJobs[0]['name'] = $deployJobName;

        $playProductionJob = file_get_contents(__DIR__ . '/fixture/job/response/play_job.200.json');
        $playProductionJob = json_decode($playProductionJob, true);

        $issues = file_get_contents(__DIR__ . '/fixture/issue/response/issues.200.json');
        $issues = json_decode($issues, true);

        $sprintFieldId = $container->getParameter('invis1ble_project_management.jira.sprint_filed_id');

        $issues['issues'][0]['key'] = (string) $hotfixesArray[0]->key;
        $issues['issues'][0]['fields'] = [
            'summary' => (string) $hotfixesArray[0]->summary,
            "customfield_$sprintFieldId" => [
                [
                    'boardId' => $container->get(BoardId::class)->value(),
                    'name' => 'June 2024 1-2',
                    'state' => State::Active->value,
                ],
            ],
        ] + $issues['issues'][0]['fields'];

        $issueTransitions = file_get_contents(__DIR__ . '/fixture/issue/response/issue_transitions.200.json');
        $issueTransitions = json_decode($issueTransitions, true);

        $transitionToDone = $container->getParameter('invis1ble_project_management.jira.transition_to_done');
        $issueTransitions['transitions'][0]['name'] = $transitionToDone;

        $versions = file_get_contents(__DIR__ . '/fixture/version/response/version.200.json');
        $versions = json_decode($versions, true);

        $latestReleaseVersionName = Version\Name::fromString('v-1-0-0');
        $versions['values'][0]['name'] = (string) $latestReleaseVersionName;
        $versions['values'][0]['released'] = false;

        $commit = file_get_contents(__DIR__ . '/fixture/commit/response/create_commit.200.json');
        $commit = json_decode($commit, true);

        $configFile = file_get_contents(__DIR__ . '/fixture/file/response/file.200.json');
        $configFile = json_decode($configFile, true);
        $configFileContent = <<<CONFIG
Deploy_react:
    host:
        _default: "v-1-0-0"
CONFIG;

        $updateExtraDeployBranchMrId = MergeRequest\MergeRequestId::from(12345);
        $updateExtraDeployBranchMrTitle = MergeRequest\Title::fromString('Update from develop');
        $updateExtraDeployBranchMrSourceBranchName = Branch\Name::fromString('develop');
        $updateExtraDeployBranchMrTargetBranchName = $container->get('invis1ble_project_management.extra_deploy_branch_name');

        $now = new \DateTimeImmutable();
        $frontendPipelineCreatedAt = $now;
        $tagCreatedAt = $now->add(new \DateInterval('PT1M'));
        $setFrontendApplicationBranchNameCommitCreatedAt = $now->add(new \DateInterval('PT15M'));

        $mock = new MockHandler([
            $this->createMergeMergeRequestResponse(
                mergeRequestId: $backendMrToMerge->id,
                projectId: $backendMrToMerge->projectId,
                projectName: $backendMrToMerge->projectName,
                title: $backendMrToMerge->title,
                sourceBranchName: $backendMrToMerge->sourceBranchName,
                targetBranchName: $backendMrToMerge->targetBranchName,
                guiUrl: $backendMrToMerge->guiUrl,
            ),
            $this->createMergeMergeRequestResponse(
                mergeRequestId: $frontendMrToMerge->id,
                projectId: $frontendMrToMerge->projectId,
                projectName: $frontendMrToMerge->projectName,
                title: $frontendMrToMerge->title,
                sourceBranchName: $frontendMrToMerge->sourceBranchName,
                targetBranchName: $frontendMrToMerge->targetBranchName,
                guiUrl: $frontendMrToMerge->guiUrl,
            ),
            $this->createPipelineResponse(
                projectId: $frontendProjectId,
                projectName: $frontendProjectName,
                status: Status::Created,
                createdAt: $frontendPipelineCreatedAt,
            ),
            $this->createPipelineResponse(
                projectId: $frontendProjectId,
                projectName: $frontendProjectName,
                status: Status::WaitingForResource,
                createdAt: $frontendPipelineCreatedAt,
            ),
            $this->createPipelineResponse(
                projectId: $frontendProjectId,
                projectName: $frontendProjectName,
                status: Status::Preparing,
                createdAt: $frontendPipelineCreatedAt,
            ),
            $this->createPipelineResponse(
                projectId: $frontendProjectId,
                projectName: $frontendProjectName,
                status: Status::Pending,
                createdAt: $frontendPipelineCreatedAt,
            ),
            $this->createPipelineResponse(
                projectId: $frontendProjectId,
                projectName: $frontendProjectName,
                status: Status::Running,
                createdAt: $frontendPipelineCreatedAt,
            ),
            $this->createPipelineResponse(
                projectId: $frontendProjectId,
                projectName: $frontendProjectName,
                status: Status::Success,
                createdAt: $frontendPipelineCreatedAt,
            ),
            new Response(
                status: 200,
                body: json_encode([
                    'name' => (string) $tagName,
                    'commit' => [
                        'message' => (string) $backendMrToMerge->title,
                        'created_at' => $frontendPipelineCreatedAt->format(DATE_RFC3339_EXTENDED),
                    ] + $tag['commit'],
                    'message' => (string) $tagMessage,
                    'created_at' => $tagCreatedAt->format(DATE_RFC3339_EXTENDED),
                ] + $tag),
            ),
            $this->createPipelineResponse(
                projectId: $backendProjectId,
                projectName: $backendProjectName,
                status: Status::Created,
                createdAt: $tagCreatedAt,
            ),
            $this->createPipelineResponse(
                projectId: $backendProjectId,
                projectName: $backendProjectName,
                status: Status::WaitingForResource,
                createdAt: $tagCreatedAt,
            ),
            $this->createPipelineResponse(
                projectId: $backendProjectId,
                projectName: $backendProjectName,
                status: Status::Preparing,
                createdAt: $tagCreatedAt,
            ),
            $this->createPipelineResponse(
                projectId: $backendProjectId,
                projectName: $backendProjectName,
                status: Status::Pending,
                createdAt: $tagCreatedAt,
            ),
            $this->createPipelineResponse(
                projectId: $backendProjectId,
                projectName: $backendProjectName,
                status: Status::Running,
                createdAt: $tagCreatedAt,
            ),
            $this->createPipelineResponse(
                projectId: $backendProjectId,
                projectName: $backendProjectName,
                status: Status::Success,
                createdAt: $tagCreatedAt,
            ),
            $this->createPipelineResponse(
                projectId: $backendProjectId,
                projectName: $backendProjectName,
                status: Status::Success,
                createdAt: $tagCreatedAt,
            ),
            new Response(
                status: 200,
                body: json_encode($pipelineJobs),
            ),
            new Response(
                status: 200,
                body: json_encode([
                    'id' => $deployJob['id'],
                    'name' => $deployJobName,
                    'ref' => $tag['commit']['id'],
                    'created_at' => $tagCreatedAt->add(new \DateInterval('PT10M'))->format(DATE_RFC3339_EXTENDED),
                ] + $playProductionJob),
            ),
            $this->createPipelineResponse(
                projectId: $backendProjectId,
                projectName: $backendProjectName,
                status: Status::WaitingForResource,
                createdAt: $tagCreatedAt,
            ),
            $this->createPipelineResponse(
                projectId: $backendProjectId,
                projectName: $backendProjectName,
                status: Status::Preparing,
                createdAt: $tagCreatedAt,
            ),
            $this->createPipelineResponse(
                projectId: $backendProjectId,
                projectName: $backendProjectName,
                status: Status::Pending,
                createdAt: $tagCreatedAt,
            ),
            $this->createPipelineResponse(
                projectId: $backendProjectId,
                projectName: $backendProjectName,
                status: Status::Running,
                createdAt: $tagCreatedAt,
            ),
            $this->createPipelineResponse(
                projectId: $backendProjectId,
                projectName: $backendProjectName,
                status: Status::Success,
                createdAt: $tagCreatedAt,
            ),
            $this->createCreateMergeRequestResponse(
                mergeRequestId: $backendMrToMerge->id,
                projectId: $backendMrToMerge->projectId,
                projectName: $backendMrToMerge->projectName,
                title: $backendMrToMerge->title,
                sourceBranchName: $backendMrToMerge->sourceBranchName,
                targetBranchName: Branch\Name::fromString('develop'),
                guiUrl: $backendMrToMerge->guiUrl,
            ),
            $this->createCreateMergeRequestResponse(
                mergeRequestId: $frontendMrToMerge->id,
                projectId: $frontendMrToMerge->projectId,
                projectName: $frontendMrToMerge->projectName,
                title: $frontendMrToMerge->title,
                sourceBranchName: $frontendMrToMerge->sourceBranchName,
                targetBranchName: Branch\Name::fromString('develop'),
                guiUrl: $frontendMrToMerge->guiUrl,
            ),
            $this->createMergeMergeRequestResponse(
                mergeRequestId: $backendMrToMerge->id,
                projectId: $backendMrToMerge->projectId,
                projectName: $backendMrToMerge->projectName,
                title: $backendMrToMerge->title,
                sourceBranchName: $backendMrToMerge->sourceBranchName,
                targetBranchName: Branch\Name::fromString('develop'),
                guiUrl: $backendMrToMerge->guiUrl,
            ),
            $this->createMergeMergeRequestResponse(
                mergeRequestId: $frontendMrToMerge->id,
                projectId: $frontendMrToMerge->projectId,
                projectName: $frontendMrToMerge->projectName,
                title: $frontendMrToMerge->title,
                sourceBranchName: $frontendMrToMerge->sourceBranchName,
                targetBranchName: Branch\Name::fromString('develop'),
                guiUrl: $frontendMrToMerge->guiUrl,
            ),
            $this->createCreateMergeRequestResponse(
                mergeRequestId: $backendMrToMerge->id,
                projectId: $backendMrToMerge->projectId,
                projectName: $backendMrToMerge->projectName,
                title: $backendMrToMerge->title,
                sourceBranchName: $backendMrToMerge->sourceBranchName,
                targetBranchName: Branch\Name::fromString((string) $latestReleaseVersionName),
                guiUrl: $backendMrToMerge->guiUrl,
            ),
            $this->createCreateMergeRequestResponse(
                mergeRequestId: $frontendMrToMerge->id,
                projectId: $frontendMrToMerge->projectId,
                projectName: $frontendMrToMerge->projectName,
                title: $frontendMrToMerge->title,
                sourceBranchName: $frontendMrToMerge->sourceBranchName,
                targetBranchName: Branch\Name::fromString((string) $latestReleaseVersionName),
                guiUrl: $frontendMrToMerge->guiUrl,
            ),
            $this->createMergeMergeRequestResponse(
                mergeRequestId: $backendMrToMerge->id,
                projectId: $backendMrToMerge->projectId,
                projectName: $backendMrToMerge->projectName,
                title: $backendMrToMerge->title,
                sourceBranchName: $backendMrToMerge->sourceBranchName,
                targetBranchName: Branch\Name::fromString((string) $latestReleaseVersionName),
                guiUrl: $backendMrToMerge->guiUrl,
            ),
            $this->createMergeMergeRequestResponse(
                mergeRequestId: $frontendMrToMerge->id,
                projectId: $frontendMrToMerge->projectId,
                projectName: $frontendMrToMerge->projectName,
                title: $frontendMrToMerge->title,
                sourceBranchName: $frontendMrToMerge->sourceBranchName,
                targetBranchName: Branch\Name::fromString((string) $latestReleaseVersionName),
                guiUrl: $frontendMrToMerge->guiUrl,
            ),
            new Response(
                status: 200,
                body: json_encode([
                    'content' => base64_encode($configFileContent),
                    'size' => strlen($configFileContent),
                ] + $configFile),
            ),
            new Response(
                status: 200,
                body: json_encode([
                    'message' => 'Change frontend application branch name to develop',
                    'created_at' => $setFrontendApplicationBranchNameCommitCreatedAt->format(DATE_RFC3339_EXTENDED),
                ] + $commit),
            ),
            $this->createCreateMergeRequestResponse(
                mergeRequestId: $updateExtraDeployBranchMrId,
                projectId: $backendProjectId,
                projectName: $backendProjectName,
                title: $updateExtraDeployBranchMrTitle,
                sourceBranchName: $updateExtraDeployBranchMrSourceBranchName,
                targetBranchName: $updateExtraDeployBranchMrTargetBranchName,
                guiUrl: $backendMrToMerge->guiUrl,
            ),
            $this->createMergeMergeRequestResponse(
                mergeRequestId: $updateExtraDeployBranchMrId,
                projectId: $backendProjectId,
                projectName: $backendProjectName,
                title: $updateExtraDeployBranchMrTitle,
                sourceBranchName: $updateExtraDeployBranchMrSourceBranchName,
                targetBranchName: $updateExtraDeployBranchMrTargetBranchName,
                guiUrl: $backendMrToMerge->guiUrl,
            ),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $container->set('eight_points_guzzle.client.gitlab', new Client(['handler' => $handlerStack]));

        $mock = new MockHandler([
            new Response(
                status: 200,
                body: json_encode($issues),
            ),
            new Response(
                status: 200,
                body: json_encode($issueTransitions),
            ),
            new Response(
                status: 204,
            ),
            new Response(
                status: 200,
                body: json_encode($versions),
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
            targetBranchName: Branch\Name::fromString('master'),
            newTargetBranchName: Branch\Name::fromString('develop'),
        );
        $expectedHotfixes = $this->addCopiesWithNewTargetBranchToMergeRequestsToMerge(
            issues: $expectedHotfixes,
            targetBranchName: Branch\Name::fromString('master'),
            newTargetBranchName: Branch\Name::fromString((string) $latestReleaseVersionName),
        );
        $expectedMrsToMerge = $expectedHotfixes->toArray()[0]->mergeRequestsToMerge->toArray();

        $this->assertObjectEquals($createPublicationCommand->tagName, $publication->tagName());
        $this->assertObjectEquals($createPublicationCommand->tagMessage, $publication->tagMessage());
        $this->assertObjectEquals($expectedHotfixes, $publication->hotfixes());

        $dispatchedEvents = $eventBus->getDispatchedEvents();

        $this->assertCount(62, $dispatchedEvents);

        $this->assertArrayHasKey(0, $dispatchedEvents);
        $event = $dispatchedEvents[0]->event;
        $this->assertInstanceOf(HotfixPublicationCreated::class, $event);
        $this->assertObjectEquals(new StatusCreated(), $event->status);
        $this->assertObjectEquals($createPublicationCommand->hotfixes, $event->hotfixes);

        $this->assertArrayHasKey(1, $dispatchedEvents);
        $event = $dispatchedEvents[1]->event;
        $this->assertInstanceOf(MergeRequestMerged::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals($expectedMrsToMerge[0]->id, $event->mergeRequestId);
        $this->assertObjectEquals($expectedMrsToMerge[0]->title, $event->title);
        $this->assertObjectEquals($expectedMrsToMerge[0]->sourceBranchName, $event->sourceBranchName);
        $this->assertObjectEquals($expectedMrsToMerge[0]->targetBranchName, $event->targetBranchName);
        $this->assertObjectEquals($expectedMrsToMerge[0]->details, $event->details);

        $this->assertArrayHasKey(2, $dispatchedEvents);
        $event = $dispatchedEvents[2]->event;
        $this->assertInstanceOf(MergeRequestMerged::class, $event);
        $this->assertObjectEquals($frontendProjectId, $event->projectId);
        $this->assertObjectEquals($expectedMrsToMerge[1]->id, $event->mergeRequestId);
        $this->assertObjectEquals($expectedMrsToMerge[1]->title, $event->title);
        $this->assertObjectEquals($expectedMrsToMerge[1]->sourceBranchName, $event->sourceBranchName);
        $this->assertObjectEquals($expectedMrsToMerge[1]->targetBranchName, $event->targetBranchName);
        $this->assertObjectEquals($expectedMrsToMerge[1]->details, $event->details);

        $this->assertArrayHasKey(3, $dispatchedEvents);
        $event = $dispatchedEvents[3]->event;
        $this->assertInstanceOf(HotfixPublicationStatusChanged::class, $event);
        $this->assertObjectEquals(new StatusMergeRequestsMerged(), $event->status);
        $this->assertObjectEquals(new StatusCreated(), $event->previousStatus);

        $this->assertArrayHasKey(4, $dispatchedEvents);
        $event = $dispatchedEvents[4]->event;
        $this->assertInstanceOf(LatestPipelineStatusChanged::class, $event);
        $this->assertObjectEquals($expectedMrsToMerge[1]->projectId, $event->projectId);
        $this->assertNull($event->previousStatus);
        $this->assertObjectEquals(Status::Created, $event->status);
        $pipelineId = $event->pipelineId;

        $this->assertArrayHasKey(5, $dispatchedEvents);
        $event = $dispatchedEvents[5]->event;
        $this->assertInstanceOf(LatestPipelineAwaitingTick::class, $event);
        $this->assertObjectEquals($frontendProjectId, $event->projectId);
        $this->assertObjectEquals($pipelineId, $event->pipelineId);
        $this->assertObjectEquals(Status::Created, $event->status);

        $this->assertArrayHasKey(6, $dispatchedEvents);
        $event = $dispatchedEvents[6]->event;
        $this->assertInstanceOf(LatestPipelineStatusChanged::class, $event);
        $this->assertObjectEquals($frontendProjectId, $event->projectId);
        $this->assertObjectEquals(Status::Created, $event->previousStatus);
        $this->assertObjectEquals(Status::WaitingForResource, $event->status);

        $this->assertArrayHasKey(7, $dispatchedEvents);
        $event = $dispatchedEvents[7]->event;
        $this->assertInstanceOf(LatestPipelineAwaitingTick::class, $event);
        $this->assertObjectEquals($frontendProjectId, $event->projectId);
        $this->assertObjectEquals($pipelineId, $event->pipelineId);
        $this->assertObjectEquals(Status::WaitingForResource, $event->status);

        $this->assertArrayHasKey(8, $dispatchedEvents);
        $event = $dispatchedEvents[8]->event;
        $this->assertInstanceOf(LatestPipelineStatusChanged::class, $event);
        $this->assertObjectEquals($frontendProjectId, $event->projectId);
        $this->assertObjectEquals(Status::WaitingForResource, $event->previousStatus);
        $this->assertObjectEquals(Status::Preparing, $event->status);

        $this->assertArrayHasKey(9, $dispatchedEvents);
        $event = $dispatchedEvents[9]->event;
        $this->assertInstanceOf(LatestPipelineAwaitingTick::class, $event);
        $this->assertObjectEquals($frontendProjectId, $event->projectId);
        $this->assertObjectEquals($pipelineId, $event->pipelineId);
        $this->assertObjectEquals(Status::Preparing, $event->status);

        $this->assertArrayHasKey(10, $dispatchedEvents);
        $event = $dispatchedEvents[10]->event;
        $this->assertInstanceOf(LatestPipelineStatusChanged::class, $event);
        $this->assertObjectEquals($frontendProjectId, $event->projectId);
        $this->assertObjectEquals(Status::Preparing, $event->previousStatus);
        $this->assertObjectEquals(Status::Pending, $event->status);

        $this->assertArrayHasKey(11, $dispatchedEvents);
        $event = $dispatchedEvents[11]->event;
        $this->assertInstanceOf(LatestPipelineAwaitingTick::class, $event);
        $this->assertObjectEquals($frontendProjectId, $event->projectId);
        $this->assertObjectEquals($pipelineId, $event->pipelineId);
        $this->assertObjectEquals(Status::Pending, $event->status);

        $this->assertArrayHasKey(12, $dispatchedEvents);
        $event = $dispatchedEvents[12]->event;
        $this->assertInstanceOf(LatestPipelineStatusChanged::class, $event);
        $this->assertObjectEquals($frontendProjectId, $event->projectId);
        $this->assertObjectEquals(Status::Pending, $event->previousStatus);
        $this->assertObjectEquals(Status::Running, $event->status);

        $this->assertArrayHasKey(13, $dispatchedEvents);
        $event = $dispatchedEvents[13]->event;
        $this->assertInstanceOf(LatestPipelineAwaitingTick::class, $event);
        $this->assertObjectEquals($frontendProjectId, $event->projectId);
        $this->assertObjectEquals($pipelineId, $event->pipelineId);
        $this->assertObjectEquals(Status::Running, $event->status);

        $this->assertArrayHasKey(14, $dispatchedEvents);
        $event = $dispatchedEvents[14]->event;
        $this->assertInstanceOf(LatestPipelineStatusChanged::class, $event);
        $this->assertObjectEquals($frontendProjectId, $event->projectId);
        $this->assertObjectEquals(Status::Running, $event->previousStatus);
        $this->assertObjectEquals(Status::Success, $event->status);

        $this->assertArrayHasKey(15, $dispatchedEvents);
        $event = $dispatchedEvents[15]->event;
        $this->assertInstanceOf(HotfixPublicationStatusChanged::class, $event);
        $this->assertObjectEquals(new StatusFrontendPipelineSuccess(), $event->status);
        $this->assertObjectEquals(new StatusMergeRequestsMerged(), $event->previousStatus);

        $this->assertArrayHasKey(16, $dispatchedEvents);
        $event = $dispatchedEvents[16]->event;
        $this->assertInstanceOf(TagCreated::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals($tagName, $event->name);
        $this->assertObjectEquals($tagMessage, $event->message);

        $this->assertArrayHasKey(17, $dispatchedEvents);
        $event = $dispatchedEvents[17]->event;
        $this->assertInstanceOf(HotfixPublicationStatusChanged::class, $event);
        $this->assertObjectEquals(new StatusTagCreated(), $event->status);
        $this->assertObjectEquals(new StatusFrontendPipelineSuccess(), $event->previousStatus);

        $this->assertArrayHasKey(18, $dispatchedEvents);
        $event = $dispatchedEvents[18]->event;
        $this->assertInstanceOf(LatestPipelineStatusChanged::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertNull($event->previousStatus);
        $this->assertObjectEquals(Status::Created, $event->status);
        $pipelineId = $event->pipelineId;

        $this->assertArrayHasKey(19, $dispatchedEvents);
        $event = $dispatchedEvents[19]->event;
        $this->assertInstanceOf(LatestPipelineAwaitingTick::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals($pipelineId, $event->pipelineId);
        $this->assertObjectEquals(Status::Created, $event->status);

        $this->assertArrayHasKey(20, $dispatchedEvents);
        $event = $dispatchedEvents[20]->event;
        $this->assertInstanceOf(LatestPipelineStatusChanged::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals(Status::Created, $event->previousStatus);
        $this->assertObjectEquals(Status::WaitingForResource, $event->status);

        $this->assertArrayHasKey(21, $dispatchedEvents);
        $event = $dispatchedEvents[21]->event;
        $this->assertInstanceOf(LatestPipelineAwaitingTick::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals($pipelineId, $event->pipelineId);
        $this->assertObjectEquals(Status::WaitingForResource, $event->status);

        $this->assertArrayHasKey(22, $dispatchedEvents);
        $event = $dispatchedEvents[22]->event;
        $this->assertInstanceOf(LatestPipelineStatusChanged::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals(Status::WaitingForResource, $event->previousStatus);
        $this->assertObjectEquals(Status::Preparing, $event->status);

        $this->assertArrayHasKey(23, $dispatchedEvents);
        $event = $dispatchedEvents[23]->event;
        $this->assertInstanceOf(LatestPipelineAwaitingTick::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals($pipelineId, $event->pipelineId);
        $this->assertObjectEquals(Status::Preparing, $event->status);

        $this->assertArrayHasKey(24, $dispatchedEvents);
        $event = $dispatchedEvents[24]->event;
        $this->assertInstanceOf(LatestPipelineStatusChanged::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals(Status::Preparing, $event->previousStatus);
        $this->assertObjectEquals(Status::Pending, $event->status);

        $this->assertArrayHasKey(25, $dispatchedEvents);
        $event = $dispatchedEvents[25]->event;
        $this->assertInstanceOf(LatestPipelineAwaitingTick::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals($pipelineId, $event->pipelineId);
        $this->assertObjectEquals(Status::Pending, $event->status);

        $this->assertArrayHasKey(26, $dispatchedEvents);
        $event = $dispatchedEvents[26]->event;
        $this->assertInstanceOf(LatestPipelineStatusChanged::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals(Status::Pending, $event->previousStatus);
        $this->assertObjectEquals(Status::Running, $event->status);

        $this->assertArrayHasKey(27, $dispatchedEvents);
        $event = $dispatchedEvents[27]->event;
        $this->assertInstanceOf(LatestPipelineAwaitingTick::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals($pipelineId, $event->pipelineId);
        $this->assertObjectEquals(Status::Running, $event->status);

        $this->assertArrayHasKey(28, $dispatchedEvents);
        $event = $dispatchedEvents[28]->event;
        $this->assertInstanceOf(LatestPipelineStatusChanged::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals(Status::Running, $event->previousStatus);
        $this->assertObjectEquals(Status::Success, $event->status);

        $this->assertArrayHasKey(29, $dispatchedEvents);
        $event = $dispatchedEvents[29]->event;
        $this->assertInstanceOf(HotfixPublicationStatusChanged::class, $event);
        $this->assertObjectEquals(new StatusTagPipelineSuccess(), $event->status);
        $this->assertObjectEquals(new StatusTagCreated(), $event->previousStatus);

        $this->assertArrayHasKey(30, $dispatchedEvents);
        $event = $dispatchedEvents[30]->event;
        $this->assertInstanceOf(JobRan::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals($pipelineId, $event->pipelineId);
        $this->assertObjectEquals(Job\JobId::from($deployJob['id']), $event->jobId);
        $this->assertObjectEquals(Job\Name::fromString($deployJobName), $event->name);

        $this->assertArrayHasKey(31, $dispatchedEvents);
        $event = $dispatchedEvents[31]->event;
        $this->assertInstanceOf(HotfixPublicationStatusChanged::class, $event);
        $this->assertObjectEquals(new StatusDeploymentJobInited(), $event->status);
        $this->assertObjectEquals(new StatusTagPipelineSuccess(), $event->previousStatus);

        $this->assertArrayHasKey(32, $dispatchedEvents);
        $event = $dispatchedEvents[32]->event;
        $this->assertInstanceOf(LatestPipelineStatusChanged::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertNull($event->previousStatus);
        $this->assertObjectEquals(Status::WaitingForResource, $event->status);

        $this->assertArrayHasKey(33, $dispatchedEvents);
        $event = $dispatchedEvents[33]->event;
        $this->assertInstanceOf(LatestPipelineAwaitingTick::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals($pipelineId, $event->pipelineId);
        $this->assertObjectEquals(Status::WaitingForResource, $event->status);

        $this->assertArrayHasKey(34, $dispatchedEvents);
        $event = $dispatchedEvents[34]->event;
        $this->assertInstanceOf(LatestPipelineStatusChanged::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals(Status::WaitingForResource, $event->previousStatus);
        $this->assertObjectEquals(Status::Preparing, $event->status);

        $this->assertArrayHasKey(35, $dispatchedEvents);
        $event = $dispatchedEvents[35]->event;
        $this->assertInstanceOf(LatestPipelineAwaitingTick::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals($pipelineId, $event->pipelineId);
        $this->assertObjectEquals(Status::Preparing, $event->status);

        $this->assertArrayHasKey(36, $dispatchedEvents);
        $event = $dispatchedEvents[36]->event;
        $this->assertInstanceOf(LatestPipelineStatusChanged::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals(Status::Preparing, $event->previousStatus);
        $this->assertObjectEquals(Status::Pending, $event->status);

        $this->assertArrayHasKey(37, $dispatchedEvents);
        $event = $dispatchedEvents[37]->event;
        $this->assertInstanceOf(LatestPipelineAwaitingTick::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals($pipelineId, $event->pipelineId);
        $this->assertObjectEquals(Status::Pending, $event->status);

        $this->assertArrayHasKey(38, $dispatchedEvents);
        $event = $dispatchedEvents[38]->event;
        $this->assertInstanceOf(LatestPipelineStatusChanged::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals(Status::Pending, $event->previousStatus);
        $this->assertObjectEquals(Status::Running, $event->status);

        $this->assertArrayHasKey(39, $dispatchedEvents);
        $event = $dispatchedEvents[39]->event;
        $this->assertInstanceOf(LatestPipelineAwaitingTick::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals($pipelineId, $event->pipelineId);
        $this->assertObjectEquals(Status::Running, $event->status);

        $this->assertArrayHasKey(40, $dispatchedEvents);
        $event = $dispatchedEvents[40]->event;
        $this->assertInstanceOf(LatestPipelineStatusChanged::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals(Status::Running, $event->previousStatus);
        $this->assertObjectEquals(Status::Success, $event->status);

        $this->assertArrayHasKey(41, $dispatchedEvents);
        $event = $dispatchedEvents[41]->event;
        $this->assertInstanceOf(HotfixPublicationStatusChanged::class, $event);
        $this->assertObjectEquals(new StatusDeploymentPipelineSuccess(), $event->status);
        $this->assertObjectEquals(new StatusDeploymentJobInited(), $event->previousStatus);

        $this->assertArrayHasKey(42, $dispatchedEvents);
        $event = $dispatchedEvents[42]->event;
        $this->assertInstanceOf(HotfixTransitionedToDone::class, $event);
        $this->assertObjectEquals($container->get(Project\Key::class), $event->projectKey);
        $this->assertObjectEquals($hotfixesArray[0]->key, $event->key);

        $this->assertArrayHasKey(43, $dispatchedEvents);
        $event = $dispatchedEvents[43]->event;
        $this->assertInstanceOf(HotfixPublicationStatusChanged::class, $event);
        $this->assertObjectEquals(new StatusHotfixesTransitionedToDone(), $event->status);
        $this->assertObjectEquals(new StatusDeploymentPipelineSuccess(), $event->previousStatus);

        $this->assertArrayHasKey(44, $dispatchedEvents);
        $event = $dispatchedEvents[44]->event;
        $this->assertInstanceOf(MergeRequestCreated::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals($backendMrToMerge->title, $event->title);
        $this->assertObjectEquals($backendMrToMerge->sourceBranchName, $event->sourceBranchName);
        $this->assertObjectEquals(Branch\Name::fromString('develop'), $event->targetBranchName);
        $this->assertObjectEquals(new MergeRequest\Details\Status\StatusMergeable(), $event->details->status);

        $this->assertArrayHasKey(45, $dispatchedEvents);
        $event = $dispatchedEvents[45]->event;
        $this->assertInstanceOf(MergeRequestCreated::class, $event);
        $this->assertObjectEquals($frontendProjectId, $event->projectId);
        $this->assertObjectEquals($frontendMrToMerge->title, $event->title);
        $this->assertObjectEquals($frontendMrToMerge->sourceBranchName, $event->sourceBranchName);
        $this->assertObjectEquals(Branch\Name::fromString('develop'), $event->targetBranchName);
        $this->assertObjectEquals(new MergeRequest\Details\Status\StatusMergeable(), $event->details->status);

        $this->assertArrayHasKey(46, $dispatchedEvents);
        $event = $dispatchedEvents[46]->event;
        $this->assertInstanceOf(HotfixPublicationStatusChanged::class, $event);
        $this->assertObjectEquals(new StatusMergeRequestsIntoDevelopCreated(), $event->status);
        $this->assertObjectEquals(new StatusHotfixesTransitionedToDone(), $event->previousStatus);

        $this->assertArrayHasKey(47, $dispatchedEvents);
        $event = $dispatchedEvents[47]->event;
        $this->assertInstanceOf(MergeRequestMerged::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals($expectedMrsToMerge[2]->id, $event->mergeRequestId);
        $this->assertObjectEquals($expectedMrsToMerge[2]->title, $event->title);
        $this->assertObjectEquals($expectedMrsToMerge[2]->sourceBranchName, $event->sourceBranchName);
        $this->assertObjectEquals($expectedMrsToMerge[2]->targetBranchName, $event->targetBranchName);
        $this->assertObjectEquals($expectedMrsToMerge[2]->details, $event->details);

        $this->assertArrayHasKey(48, $dispatchedEvents);
        $event = $dispatchedEvents[48]->event;
        $this->assertInstanceOf(MergeRequestMerged::class, $event);
        $this->assertObjectEquals($frontendProjectId, $event->projectId);
        $this->assertObjectEquals($expectedMrsToMerge[2]->id, $event->mergeRequestId);
        $this->assertObjectEquals($expectedMrsToMerge[2]->title, $event->title);
        $this->assertObjectEquals($expectedMrsToMerge[2]->sourceBranchName, $event->sourceBranchName);
        $this->assertObjectEquals($expectedMrsToMerge[2]->targetBranchName, $event->targetBranchName);
        $this->assertObjectEquals($expectedMrsToMerge[2]->details, $event->details);

        $this->assertArrayHasKey(49, $dispatchedEvents);
        $event = $dispatchedEvents[49]->event;
        $this->assertInstanceOf(HotfixPublicationStatusChanged::class, $event);
        $this->assertObjectEquals(new StatusDevelopBranchSynchronized(), $event->status);
        $this->assertObjectEquals(new StatusMergeRequestsIntoDevelopCreated(), $event->previousStatus);

        $this->assertArrayHasKey(50, $dispatchedEvents);
        $event = $dispatchedEvents[50]->event;
        $this->assertInstanceOf(MergeRequestCreated::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals($backendMrToMerge->title, $event->title);
        $this->assertObjectEquals($backendMrToMerge->sourceBranchName, $event->sourceBranchName);
        $this->assertObjectEquals(Branch\Name::fromString((string) $latestReleaseVersionName), $event->targetBranchName);
        $this->assertObjectEquals(new MergeRequest\Details\Status\StatusMergeable(), $event->details->status);

        $this->assertArrayHasKey(51, $dispatchedEvents);
        $event = $dispatchedEvents[51]->event;
        $this->assertInstanceOf(MergeRequestCreated::class, $event);
        $this->assertObjectEquals($frontendProjectId, $event->projectId);
        $this->assertObjectEquals($frontendMrToMerge->title, $event->title);
        $this->assertObjectEquals($frontendMrToMerge->sourceBranchName, $event->sourceBranchName);
        $this->assertObjectEquals(Branch\Name::fromString((string) $latestReleaseVersionName), $event->targetBranchName);
        $this->assertObjectEquals(new MergeRequest\Details\Status\StatusMergeable(), $event->details->status);

        $this->assertArrayHasKey(52, $dispatchedEvents);
        $event = $dispatchedEvents[52]->event;
        $this->assertInstanceOf(HotfixPublicationStatusChanged::class, $event);
        $this->assertObjectEquals(new StatusMergeRequestsIntoReleaseCreated(), $event->status);
        $this->assertObjectEquals(new StatusDevelopBranchSynchronized(), $event->previousStatus);

        $this->assertArrayHasKey(53, $dispatchedEvents);
        $event = $dispatchedEvents[53]->event;
        $this->assertInstanceOf(MergeRequestMerged::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals($expectedMrsToMerge[4]->id, $event->mergeRequestId);
        $this->assertObjectEquals($expectedMrsToMerge[4]->title, $event->title);
        $this->assertObjectEquals($expectedMrsToMerge[4]->sourceBranchName, $event->sourceBranchName);
        $this->assertObjectEquals($expectedMrsToMerge[4]->targetBranchName, $event->targetBranchName);
        $this->assertObjectEquals($expectedMrsToMerge[4]->details, $event->details);

        $this->assertArrayHasKey(54, $dispatchedEvents);
        $event = $dispatchedEvents[54]->event;
        $this->assertInstanceOf(MergeRequestMerged::class, $event);
        $this->assertObjectEquals($frontendProjectId, $event->projectId);
        $this->assertObjectEquals($expectedMrsToMerge[5]->id, $event->mergeRequestId);
        $this->assertObjectEquals($expectedMrsToMerge[5]->title, $event->title);
        $this->assertObjectEquals($expectedMrsToMerge[5]->sourceBranchName, $event->sourceBranchName);
        $this->assertObjectEquals($expectedMrsToMerge[5]->targetBranchName, $event->targetBranchName);
        $this->assertObjectEquals($expectedMrsToMerge[5]->details, $event->details);

        $this->assertArrayHasKey(55, $dispatchedEvents);
        $event = $dispatchedEvents[55]->event;
        $this->assertInstanceOf(HotfixPublicationStatusChanged::class, $event);
        $this->assertObjectEquals(new StatusReleaseBranchSynchronized(), $event->status);
        $this->assertObjectEquals(new StatusMergeRequestsIntoReleaseCreated(), $event->previousStatus);
        $this->assertObjectEquals($expectedHotfixes, $event->hotfixes);

        $this->assertArrayHasKey(56, $dispatchedEvents);
        $event = $dispatchedEvents[56]->event;
        $this->assertInstanceOf(CommitCreated::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals(Branch\Name::fromString('develop'), $event->branchName);
        $this->assertNull($event->startBranchName);
        $this->assertObjectEquals(
            Commit\Message::fromString('Change frontend application branch name to develop'),
            $event->message,
        );

        $this->assertArrayHasKey(57, $dispatchedEvents);
        $event = $dispatchedEvents[57]->event;
        $this->assertInstanceOf(HotfixPublicationStatusChanged::class, $event);
        $this->assertObjectEquals(new StatusFrontendApplicationBranchSet(), $event->status);
        $this->assertObjectEquals(new StatusReleaseBranchSynchronized(), $event->previousStatus);
        $this->assertObjectEquals($expectedHotfixes, $event->hotfixes);

        $this->assertArrayHasKey(58, $dispatchedEvents);
        $event = $dispatchedEvents[58]->event;
        $this->assertInstanceOf(MergeRequestCreated::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals($updateExtraDeployBranchMrTitle, $event->title);
        $this->assertObjectEquals($updateExtraDeployBranchMrSourceBranchName, $event->sourceBranchName);
        $this->assertObjectEquals($updateExtraDeployBranchMrTargetBranchName, $event->targetBranchName);
        $this->assertObjectEquals(new MergeRequest\Details\Status\StatusMergeable(), $event->details->status);

        $this->assertArrayHasKey(59, $dispatchedEvents);
        $event = $dispatchedEvents[59]->event;
        $this->assertInstanceOf(HotfixPublicationStatusChanged::class, $event);
        $this->assertObjectEquals(new StatusUpdateExtraDeployBranchMergeRequestCreated([
            'project_id' => $backendProjectId->value(),
            'merge_request_id' => $updateExtraDeployBranchMrId->value(),
        ]), $event->status);
        $this->assertObjectEquals(new StatusFrontendApplicationBranchSet(), $event->previousStatus);
        $this->assertObjectEquals($expectedHotfixes, $event->hotfixes);

        $this->assertArrayHasKey(60, $dispatchedEvents);
        $event = $dispatchedEvents[60]->event;
        $this->assertInstanceOf(MergeRequestMerged::class, $event);
        $this->assertObjectEquals($backendProjectId, $event->projectId);
        $this->assertObjectEquals($updateExtraDeployBranchMrId, $event->mergeRequestId);
        $this->assertObjectEquals($updateExtraDeployBranchMrTitle, $event->title);
        $this->assertObjectEquals($updateExtraDeployBranchMrSourceBranchName, $event->sourceBranchName);
        $this->assertObjectEquals($updateExtraDeployBranchMrTargetBranchName, $event->targetBranchName);
        $this->assertObjectEquals(new MergeRequest\Details\Details(new MergeRequest\Details\Status\StatusNotOpen()), $event->details);

        $this->assertArrayHasKey(61, $dispatchedEvents);
        $event = $dispatchedEvents[61]->event;
        $this->assertInstanceOf(HotfixPublicationStatusChanged::class, $event);
        $this->assertObjectEquals(new StatusDone(), $event->status);
        $this->assertObjectEquals(new StatusUpdateExtraDeployBranchMergeRequestCreated([
            'project_id' => $backendProjectId->value(),
            'merge_request_id' => $updateExtraDeployBranchMrId->value(),
        ]), $event->previousStatus);
        $this->assertObjectEquals($expectedHotfixes, $event->hotfixes);
    }

    private function createPipelineResponse(
        ContinuousIntegration\Project\ProjectId $projectId,
        ContinuousIntegration\Project\Name $projectName,
        Status $status,
        \DateTimeImmutable $createdAt,
    ): Response {
        $pipeline = json_decode(
            file_get_contents(__DIR__ . '/fixture/pipeline/response/pipeline.200.json'),
            true,
        );

        return new Response(
            status: 200,
            body: json_encode([
                'id' => 123,
                'project_id' => $projectId->value(),
                'status' => $status->value,
                'created_at' => $createdAt->format(DATE_RFC3339_EXTENDED),
                'updated_at' => $createdAt->format(DATE_RFC3339_EXTENDED),
                'started_at' => $createdAt->format(DATE_RFC3339_EXTENDED),
                'finished_at' => $createdAt->format(DATE_RFC3339_EXTENDED),
                'committed_at' => $createdAt->format(DATE_RFC3339_EXTENDED),
                'web_url' => "http://127.0.0.1:3000/$projectName/-/pipelines/{$pipeline['id']}",
            ] + $pipeline),
        );
    }

    private function createMergeRequestResponse(
        MergeRequest\MergeRequestId $mergeRequestId,
        ContinuousIntegration\Project\ProjectId $projectId,
        ContinuousIntegration\Project\Name $projectName,
        MergeRequest\Title $title,
        Branch\Name $sourceBranchName,
        Branch\Name $targetBranchName,
        MergeRequest\Status $status,
        MergeRequest\Details\Status\Dictionary $detailedStatus,
        UriInterface $guiUrl,
    ): Response {
        $mr = json_decode(
            file_get_contents(__DIR__ . '/fixture/merge_request/response/merge_request.200.json'),
            true,
        );

        return new Response(
            status: 200,
            body: json_encode([
                'id' => $mergeRequestId->value(),
                'project_id' => $projectId->value(),
                'project_name' => (string) $projectName,
                'title' => (string) $title,
                'source_branch' => (string) $sourceBranchName,
                'target_branch' => (string) $targetBranchName,
                'status' => $status->value,
                'detailed_merge_status' => $detailedStatus->value,
                'web_url' => (string) $guiUrl,
            ] + $mr),
        );
    }

    private function createCreateMergeRequestResponse(
        MergeRequest\MergeRequestId $mergeRequestId,
        ContinuousIntegration\Project\ProjectId $projectId,
        ContinuousIntegration\Project\Name $projectName,
        MergeRequest\Title $title,
        Branch\Name $sourceBranchName,
        Branch\Name $targetBranchName,
        UriInterface $guiUrl,
    ): Response {
        return $this->createMergeRequestResponse(
            mergeRequestId: $mergeRequestId,
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

    private function createMergeMergeRequestResponse(
        MergeRequest\MergeRequestId $mergeRequestId,
        ContinuousIntegration\Project\ProjectId $projectId,
        ContinuousIntegration\Project\Name $projectName,
        MergeRequest\Title $title,
        Branch\Name $sourceBranchName,
        Branch\Name $targetBranchName,
        UriInterface $guiUrl,
    ): Response {
        return $this->createMergeRequestResponse(
            mergeRequestId: $mergeRequestId,
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
