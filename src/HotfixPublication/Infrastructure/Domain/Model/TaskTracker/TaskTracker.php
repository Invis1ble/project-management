<?php

declare(strict_types=1);

namespace ProjectManagement\HotfixPublication\Infrastructure\Domain\Model\TaskTracker;

use Invis1ble\Messenger\Event\EventBusInterface;
use ProjectManagement\HotfixPublication\Domain\Model\TaskTracker\TaskTrackerInterface;
use ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequestFactoryInterface;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Board;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\IssueFactoryInterface;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\IssueList;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\Key;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Project;
use ProjectManagement\Shared\Infrastructure\Domain\Model\TaskTracker\TaskTracker as BasicTaskTracker;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;

final readonly class TaskTracker extends BasicTaskTracker implements TaskTrackerInterface
{
    public function __construct(
        ClientInterface $httpClient,
        UriFactoryInterface $uriFactory,
        StreamFactoryInterface $streamFactory,
        RequestFactoryInterface $requestFactory,
        IssueFactoryInterface $issueFactory,
        MergeRequestFactoryInterface $mergeRequestFactory,
        private EventBusInterface $eventBus,
        Project\Key $projectKey,
        Board\BoardId $sprintBoardId,
        int $sprintFieldId,
        private string $readyForPublishStatus = 'Ready for Publish',
        private array $supportedIssueTypes = ['Hotfix'],
    ) {
        parent::__construct(
            $httpClient,
            $uriFactory,
            $streamFactory,
            $requestFactory,
            $issueFactory,
            $mergeRequestFactory,
            $projectKey,
            $sprintBoardId,
            $sprintFieldId,
        );
    }

    public function readyForPublishHotfixes(Key ...$keys): IssueList
    {
        return $this->issuesFromActiveSprint(
            $this->readyForPublishStatus,
            $this->supportedIssueTypes,
            ...$keys,
        );
    }
}
