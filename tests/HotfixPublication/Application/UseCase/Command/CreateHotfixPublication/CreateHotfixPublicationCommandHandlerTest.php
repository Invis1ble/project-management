<?php

declare(strict_types=1);

namespace HotfixPublication\Application\UseCase\Command\CreateHotfixPublication;

use Invis1ble\Messenger\Command\TraceableCommandBus;
use Invis1ble\Messenger\Event\TraceableEventBus;
use Invis1ble\ProjectManagement\HotfixPublication\Application\UseCase\Command\CreateHotfixPublication\CreateHotfixPublicationCommand;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Event\HotfixPublicationCreated;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\Status\StatusCreated;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Repository\HotfixPublicationRepositoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Branch;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Tag;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Board\BoardId;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Sprint;
use Psr\Http\Message\UriFactoryInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CreateHotfixPublicationCommandHandlerTest extends KernelTestCase
{
    public function testCreateHotfixPublication(): void
    {
        self::bootKernel();

        $container = static::getContainer();

        /** @var HotfixPublicationRepositoryInterface $hotfixPublicationRepository */
        $hotfixPublicationRepository = $container->get(HotfixPublicationRepositoryInterface::class);

        /** @var UriFactoryInterface $uriFactory */
        $uriFactory = $container->get(UriFactoryInterface::class);

        /** @var TraceableCommandBus $commandBus */
        $commandBus = $container->get(TraceableCommandBus::class);

        /** @var TraceableEventBus $eventBus */
        $eventBus = $container->get(TraceableEventBus::class);

        $mergeRequests = new MergeRequest\MergeRequestList(
            new MergeRequest\MergeRequest(
                id: MergeRequest\MergeRequestId::from(4),
                title: MergeRequest\Title::fromString('Update issue branch'),
                projectId: Project\ProjectId::from(5),
                projectName: Project\Name::fromString('PROJECT'),
                sourceBranchName: Branch\Name::fromString('master'),
                targetBranchName: Branch\Name::fromString('PROJECT-2'),
                status: MergeRequest\Status::Merged,
                guiUrl: $uriFactory->createUri('https://gitlab.example.com/example/coolproject/-/merge_requests/4'),
                details: new MergeRequest\Details\Details(
                    status: new MergeRequest\Details\Status\StatusNotOpen(),
                ),
            ),
        );

        $mergeRequestsToMerge = $mergeRequests->append(
            new MergeRequest\MergeRequest(
                id: MergeRequest\MergeRequestId::from(45),
                title: MergeRequest\Title::fromString('Close PROJECT-2 Fix terrible bug'),
                projectId: Project\ProjectId::from(5),
                projectName: Project\Name::fromString('PROJECT'),
                sourceBranchName: Branch\Name::fromString('PROJECT-2'),
                targetBranchName: Branch\Name::fromString('master'),
                status: MergeRequest\Status::Open,
                guiUrl: $uriFactory->createUri('https://gitlab.example.com/example/coolproject/-/merge_requests/45'),
                details: new MergeRequest\Details\Details(
                    status: new MergeRequest\Details\Status\StatusMergeable(),
                ),
            ),
        );

        $command = new CreateHotfixPublicationCommand(
            tagName: Tag\VersionName::create(),
            tagMessage: Tag\Message::fromString('Fix terrible bug | PROJECT-2'),
            hotfixes: new Issue\IssueList(
                new Issue\Issue(
                    id: Issue\IssueId::from(1),
                    key: Issue\Key::fromString('PROJECT-2'),
                    typeId: Issue\TypeId::fromString('3'),
                    summary: Issue\Summary::fromString('Fix terrible bug'),
                    sprints: new Sprint\SprintList(
                        new Sprint\Sprint(
                            boardId: BoardId::from(42),
                            name: Sprint\Name::fromString('June 2024 1-2'),
                            state: Sprint\State::Active,
                        ),
                    ),
                    mergeRequests: $mergeRequests,
                    mergeRequestsToMerge: $mergeRequestsToMerge,
                ),
            ),
        );

        $commandBus->dispatch($command);

        $dispatchedEvents = $eventBus->getDispatchedEvents();

        $this->assertCount(1, $dispatchedEvents);
        $this->assertArrayHasKey(0, $dispatchedEvents);
        $dispatchedEvent = $dispatchedEvents[0]->event;
        $this->assertInstanceOf(HotfixPublicationCreated::class, $dispatchedEvent);
        $this->assertObjectEquals(new StatusCreated(), $dispatchedEvent->status);

        $publication = $hotfixPublicationRepository->get($dispatchedEvent->id);

        $this->assertObjectEquals($command->tagName, $publication->tagName());
        $this->assertObjectEquals($command->tagMessage, $publication->tagMessage());
        $this->assertObjectEquals($command->hotfixes, $publication->hotfixes());
    }
}
