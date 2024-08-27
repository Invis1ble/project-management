<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\HotfixPublication\Infrastructure\Domain\Model\TaskTracker;

use Invis1ble\Messenger\Event\EventBusInterface;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\TaskTracker\TaskTrackerInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequestFactoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Board;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Project;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Transition;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Version;
use Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\Model\TaskTracker\TaskTracker as BasicTaskTracker;
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
        Version\VersionFactoryInterface $versionFactory,
        Issue\IssueFactoryInterface $issueFactory,
        MergeRequestFactoryInterface $mergeRequestFactory,
        Transition\TransitionFactoryInterface $transitionFactory,
        EventBusInterface $eventBus,
        Project\Key $projectKey,
        Board\BoardId $sprintBoardId,
        int $sprintFieldId,
        private string $readyForPublishStatus = 'Ready for Publish',
        private Transition\Name $hotfixTransitionToDoneName = new Transition\Name('Close Issue'),
        private array $supportedIssueTypes = ['Hotfix'],
    ) {
        parent::__construct(
            $httpClient,
            $uriFactory,
            $streamFactory,
            $requestFactory,
            $versionFactory,
            $issueFactory,
            $mergeRequestFactory,
            $transitionFactory,
            $eventBus,
            $projectKey,
            $sprintBoardId,
            $sprintFieldId,
        );
    }

    public function readyForPublishHotfixes(Issue\Key ...$keys): Issue\IssueList
    {
        return $this->issuesFromActiveSprint(
            $this->readyForPublishStatus,
            $this->supportedIssueTypes,
            ...$keys,
        );
    }

    public function transitionHotfixesToDone(Issue\Key ...$keys): void
    {
        foreach ($this->readyForPublishHotfixes(...$keys) as $issue) {
            $this->transitionTo($issue->key, $this->hotfixTransitionToDoneName);
        }
    }
}
