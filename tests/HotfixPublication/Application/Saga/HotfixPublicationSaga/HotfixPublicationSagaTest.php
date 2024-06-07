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
use Invis1ble\ProjectManagement\Shared\Domain\Event\DevelopmentCollaboration\MergeRequest\MergeRequestMerged;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Tag;
use Invis1ble\ProjectManagement\Tests\Shared\Infrastructure\Domain\Model\TaskTracker\Issue\CreateIssuesTrait;
use Invis1ble\ProjectManagement\Tests\Shared\Infrastructure\Domain\Model\TaskTracker\Issue\MapMergeRequestsToMergeToMergedTrait;
use Psr\Http\Message\UriFactoryInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class HotfixPublicationSagaTest extends KernelTestCase
{
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
        $mergeRequest0 = $hotfixesArray[0]->mergeRequestsToMerge->toArray()[0];

        $responseBodyMock = file_get_contents(__DIR__ . '/fixtures/merge_request/response/merge_request.200.json');
        $responseBodyMock = json_decode($responseBodyMock, true);
        $responseBodyMock['id'] = $mergeRequest0->id->value();
        $responseBodyMock['project_id'] = $mergeRequest0->projectId->value();
        $responseBodyMock['project_name'] = (string) $mergeRequest0->projectName;
        $responseBodyMock['title'] = (string) $mergeRequest0->title;
        $responseBodyMock['source_branch'] = (string) $mergeRequest0->sourceBranchName;
        $responseBodyMock['target_branch'] = (string) $mergeRequest0->targetBranchName;
        $responseBodyMock['status'] = MergeRequest\Status::Merged->value;
        $responseBodyMock['detailed_merge_status'] = MergeRequest\Details\Status\Dictionary::NotOpen->value;
        $responseBodyMock['web_url'] = (string) $mergeRequest0->guiUrl;

        $mock = new MockHandler([
            new Response(
                status: 200,
                body: json_encode($responseBodyMock),
            ),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $container->set('eight_points_guzzle.client.gitlab', new Client(['handler' => $handlerStack]));

        $createPublicationCommand = new CreateHotfixPublicationCommand(
            tagName: Tag\VersionName::create(),
            tagMessage: Tag\Message::fromString("{$hotfixesArray[0]->summary} | {$hotfixesArray[0]->key}"),
            hotfixes: $hotfixes,
        );

        $commandBus->dispatch($createPublicationCommand);

        $publication = $hotfixPublicationRepository->get(
            HotfixPublicationId::fromVersionName($createPublicationCommand->tagName),
        );

        $expectedHotfixes = $this->mapMereRequestsToMergeToMerged($createPublicationCommand->hotfixes);
        $expectedMrToMerge0 = $expectedHotfixes->toArray()[0]->mergeRequestsToMerge->toArray()[0];

        $this->assertObjectEquals($createPublicationCommand->tagName, $publication->tagName());
        $this->assertObjectEquals($createPublicationCommand->tagMessage, $publication->tagMessage());
        $this->assertObjectEquals($expectedHotfixes, $publication->hotfixes());

        $dispatchedEvents = $eventBus->getDispatchedEvents();

        $this->assertCount(3, $dispatchedEvents);

        $this->assertArrayHasKey(2, $dispatchedEvents);
        $event = $dispatchedEvents[2]->event;
        $this->assertInstanceOf(HotfixPublicationCreated::class, $event);
        $this->assertObjectEquals(new StatusCreated(), $event->status);
        $this->assertObjectEquals($createPublicationCommand->hotfixes, $event->hotfixes);

        $this->assertArrayHasKey(1, $dispatchedEvents);
        $event = $dispatchedEvents[1]->event;
        $this->assertInstanceOf(HotfixPublicationStatusChanged::class, $event);
        $this->assertObjectEquals(new StatusMergeRequestsMerged(), $event->status);
        $this->assertObjectEquals(new StatusCreated(), $event->previousStatus);
        $this->assertObjectEquals($expectedHotfixes, $event->hotfixes);

        $this->assertArrayHasKey(0, $dispatchedEvents);
        $event = $dispatchedEvents[0]->event;
        $this->assertInstanceOf(MergeRequestMerged::class, $event);
        $this->assertObjectEquals($expectedMrToMerge0->projectId, $event->projectId);
        $this->assertObjectEquals($expectedMrToMerge0->id, $event->mergeRequestId);
        $this->assertObjectEquals($expectedMrToMerge0->title, $event->title);
        $this->assertObjectEquals($expectedMrToMerge0->sourceBranchName, $event->sourceBranchName);
        $this->assertObjectEquals($expectedMrToMerge0->targetBranchName, $event->targetBranchName);
        $this->assertObjectEquals($expectedMrToMerge0->details, $event->details);
    }
}
