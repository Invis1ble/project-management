<?php

declare(strict_types=1);

namespace HotfixPublication\Application\Saga\HotfixPublicationSaga;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Invis1ble\Messenger\Command\CommandBusInterface;
use Invis1ble\Messenger\Event\TraceableEventBus;
use Invis1ble\ProjectManagement\HotfixPublication\Application\UseCase\Command\CreateHotfixPublication\CreateHotfixPublicationCommand;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Event\HotfixPublicationCreated;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Event\HotfixPublicationStatusChanged;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\HotfixPublicationId;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\Status\StatusCreated;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\Status\StatusMergeRequestsMerged;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Repository\HotfixPublicationRepositoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Event\ContinuousIntegration\Pipeline\LatestPipelineAwaitingTick;
use Invis1ble\ProjectManagement\Shared\Domain\Event\ContinuousIntegration\Pipeline\LatestPipelineStatusChanged;
use Invis1ble\ProjectManagement\Shared\Domain\Event\DevelopmentCollaboration\MergeRequest\MergeRequestMerged;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Pipeline\Status;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project\Name;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project\ProjectId;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Tag;
use Invis1ble\ProjectManagement\Tests\Shared\Infrastructure\Domain\Model\TaskTracker\Issue\CreateIssuesTrait;
use Invis1ble\ProjectManagement\Tests\Shared\Infrastructure\Domain\Model\TaskTracker\Issue\MapMergeRequestsToMergeToMergedTrait;
use Psr\Http\Message\UriFactoryInterface;
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

        $mr = file_get_contents(__DIR__ . '/fixtures/merge_request/response/merge_request.200.json');
        $mr = json_decode($mr, true);

        $now = new \DateTimeImmutable();

        $mock = new MockHandler([
            new Response(
                status: 200,
                body: json_encode([
                    'id' => $mrToMerge[0]->id->value(),
                    'project_id' => $mrToMerge[0]->projectId->value(),
                    'project_name' => (string) $mrToMerge[0]->projectName,
                    'title' => (string) $mrToMerge[0]->title,
                    'source_branch' => (string) $mrToMerge[0]->sourceBranchName,
                    'target_branch' => (string) $mrToMerge[0]->targetBranchName,
                    'status' => MergeRequest\Status::Merged->value,
                    'detailed_merge_status' => MergeRequest\Details\Status\Dictionary::NotOpen->value,
                    'web_url' => (string) $mrToMerge[0]->guiUrl,
                ] + $mr),
            ),
            new Response(
                status: 200,
                body: json_encode([
                    'id' => $mrToMerge[1]->id->value(),
                    'project_id' => $mrToMerge[1]->projectId->value(),
                    'project_name' => (string) $mrToMerge[1]->projectName,
                    'title' => (string) $mrToMerge[1]->title,
                    'source_branch' => (string) $mrToMerge[1]->sourceBranchName,
                    'target_branch' => (string) $mrToMerge[0]->targetBranchName,
                    'status' => MergeRequest\Status::Merged->value,
                    'detailed_merge_status' => MergeRequest\Details\Status\Dictionary::NotOpen->value,
                    'web_url' => (string) $mrToMerge[1]->guiUrl,
                ] + $mr),
            ),
            $this->createPipelineResponse(
                projectId: $mrToMerge[1]->projectId,
                projectName: $mrToMerge[1]->projectName,
                status: Status::Created,
                createdAt: $now,
            ),
            $this->createPipelineResponse(
                projectId: $mrToMerge[1]->projectId,
                projectName: $mrToMerge[1]->projectName,
                status: Status::WaitingForResource,
                createdAt: $now,
            ),
            $this->createPipelineResponse(
                projectId: $mrToMerge[1]->projectId,
                projectName: $mrToMerge[1]->projectName,
                status: Status::Preparing,
                createdAt: $now,
            ),
            $this->createPipelineResponse(
                projectId: $mrToMerge[1]->projectId,
                projectName: $mrToMerge[1]->projectName,
                status: Status::Pending,
                createdAt: $now,
            ),
            $this->createPipelineResponse(
                projectId: $mrToMerge[1]->projectId,
                projectName: $mrToMerge[1]->projectName,
                status: Status::Running,
                createdAt: $now,
            ),
            $this->createPipelineResponse(
                projectId: $mrToMerge[1]->projectId,
                projectName: $mrToMerge[1]->projectName,
                status: Status::Success,
                createdAt: $now,
            ),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $container->set('eight_points_guzzle.client.gitlab', new Client(['handler' => $handlerStack]));

        $createPublicationCommand = new CreateHotfixPublicationCommand(
            tagName: Tag\VersionName::create(),
            tagMessage: Tag\Message::fromString("{$hotfixesArray[0]->summary} | {$hotfixesArray[0]->key}"),
            hotfixes: $hotfixes,
        );

        static::mockTime($now->sub(new \DateInterval('PT1M')));

        $commandBus->dispatch($createPublicationCommand);

        $publication = $hotfixPublicationRepository->get(
            HotfixPublicationId::fromVersionName($createPublicationCommand->tagName),
        );

        $expectedHotfixes = $this->mapMereRequestsToMergeToMerged($createPublicationCommand->hotfixes);
        $expectedMrsToMerge = $expectedHotfixes->toArray()[0]->mergeRequestsToMerge->toArray();

        $this->assertObjectEquals($createPublicationCommand->tagName, $publication->tagName());
        $this->assertObjectEquals($createPublicationCommand->tagMessage, $publication->tagMessage());
        $this->assertObjectEquals($expectedHotfixes, $publication->hotfixes());

        $dispatchedEvents = $eventBus->getDispatchedEvents();

        $this->assertCount(15, $dispatchedEvents);

        $this->assertArrayHasKey(0, $dispatchedEvents);
        $event = $dispatchedEvents[0]->event;
        $this->assertInstanceOf(HotfixPublicationCreated::class, $event);
        $this->assertObjectEquals(new StatusCreated(), $event->status);
        $this->assertObjectEquals($createPublicationCommand->hotfixes, $event->hotfixes);

        $this->assertArrayHasKey(1, $dispatchedEvents);
        $event = $dispatchedEvents[1]->event;
        $this->assertInstanceOf(MergeRequestMerged::class, $event);
        $this->assertObjectEquals($expectedMrsToMerge[0]->projectId, $event->projectId);
        $this->assertObjectEquals($expectedMrsToMerge[0]->id, $event->mergeRequestId);
        $this->assertObjectEquals($expectedMrsToMerge[0]->title, $event->title);
        $this->assertObjectEquals($expectedMrsToMerge[0]->sourceBranchName, $event->sourceBranchName);
        $this->assertObjectEquals($expectedMrsToMerge[0]->targetBranchName, $event->targetBranchName);
        $this->assertObjectEquals($expectedMrsToMerge[0]->details, $event->details);

        $this->assertArrayHasKey(2, $dispatchedEvents);
        $event = $dispatchedEvents[2]->event;
        $this->assertInstanceOf(MergeRequestMerged::class, $event);
        $this->assertObjectEquals($expectedMrsToMerge[1]->projectId, $event->projectId);
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
        $this->assertObjectEquals($expectedHotfixes, $event->hotfixes);

        $this->assertArrayHasKey(4, $dispatchedEvents);
        $event = $dispatchedEvents[4]->event;
        $this->assertInstanceOf(LatestPipelineStatusChanged::class, $event);
        $this->assertObjectEquals($expectedMrsToMerge[1]->projectId, $event->projectId);
        $this->assertNull($event->previousStatus);
        $this->assertObjectEquals(Status::Created, $event->status);

        $projectId = $event->projectId;
        $pipelineId = $event->pipelineId;

        $this->assertArrayHasKey(5, $dispatchedEvents);
        $event = $dispatchedEvents[5]->event;
        $this->assertInstanceOf(LatestPipelineAwaitingTick::class, $event);
        $this->assertObjectEquals($projectId, $event->projectId);
        $this->assertObjectEquals($pipelineId, $event->pipelineId);
        $this->assertObjectEquals(Status::Created, $event->status);

        $this->assertArrayHasKey(6, $dispatchedEvents);
        $event = $dispatchedEvents[6]->event;
        $this->assertInstanceOf(LatestPipelineStatusChanged::class, $event);
        $this->assertObjectEquals($projectId, $event->projectId);
        $this->assertObjectEquals(Status::Created, $event->previousStatus);
        $this->assertObjectEquals(Status::WaitingForResource, $event->status);

        $this->assertArrayHasKey(7, $dispatchedEvents);
        $event = $dispatchedEvents[7]->event;
        $this->assertInstanceOf(LatestPipelineAwaitingTick::class, $event);
        $this->assertObjectEquals($projectId, $event->projectId);
        $this->assertObjectEquals($pipelineId, $event->pipelineId);
        $this->assertObjectEquals(Status::WaitingForResource, $event->status);

        $this->assertArrayHasKey(8, $dispatchedEvents);
        $event = $dispatchedEvents[8]->event;
        $this->assertInstanceOf(LatestPipelineStatusChanged::class, $event);
        $this->assertObjectEquals($projectId, $event->projectId);
        $this->assertObjectEquals(Status::WaitingForResource, $event->previousStatus);
        $this->assertObjectEquals(Status::Preparing, $event->status);

        $this->assertArrayHasKey(9, $dispatchedEvents);
        $event = $dispatchedEvents[9]->event;
        $this->assertInstanceOf(LatestPipelineAwaitingTick::class, $event);
        $this->assertObjectEquals($projectId, $event->projectId);
        $this->assertObjectEquals($pipelineId, $event->pipelineId);
        $this->assertObjectEquals(Status::Preparing, $event->status);

        $this->assertArrayHasKey(10, $dispatchedEvents);
        $event = $dispatchedEvents[10]->event;
        $this->assertInstanceOf(LatestPipelineStatusChanged::class, $event);
        $this->assertObjectEquals($projectId, $event->projectId);
        $this->assertObjectEquals(Status::Preparing, $event->previousStatus);
        $this->assertObjectEquals(Status::Pending, $event->status);

        $this->assertArrayHasKey(11, $dispatchedEvents);
        $event = $dispatchedEvents[11]->event;
        $this->assertInstanceOf(LatestPipelineAwaitingTick::class, $event);
        $this->assertObjectEquals($projectId, $event->projectId);
        $this->assertObjectEquals($pipelineId, $event->pipelineId);
        $this->assertObjectEquals(Status::Pending, $event->status);

        $this->assertArrayHasKey(12, $dispatchedEvents);
        $event = $dispatchedEvents[12]->event;
        $this->assertInstanceOf(LatestPipelineStatusChanged::class, $event);
        $this->assertObjectEquals($projectId, $event->projectId);
        $this->assertObjectEquals(Status::Pending, $event->previousStatus);
        $this->assertObjectEquals(Status::Running, $event->status);

        $this->assertArrayHasKey(13, $dispatchedEvents);
        $event = $dispatchedEvents[13]->event;
        $this->assertInstanceOf(LatestPipelineAwaitingTick::class, $event);
        $this->assertObjectEquals($projectId, $event->projectId);
        $this->assertObjectEquals($pipelineId, $event->pipelineId);
        $this->assertObjectEquals(Status::Running, $event->status);

        $this->assertArrayHasKey(14, $dispatchedEvents);
        $event = $dispatchedEvents[14]->event;
        $this->assertInstanceOf(LatestPipelineStatusChanged::class, $event);
        $this->assertObjectEquals($projectId, $event->projectId);
        $this->assertObjectEquals(Status::Running, $event->previousStatus);
        $this->assertObjectEquals(Status::Success, $event->status);
    }

    private function createPipelineResponse(
        ProjectId $projectId,
        Name $projectName,
        Status $status,
        \DateTimeImmutable $createdAt,
    ): Response {
        $pipeline = json_decode(
            file_get_contents(__DIR__ . '/fixtures/pipeline/response/pipeline.200.json'),
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
}
