<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\HotfixPublication\Infrastructure\Domain\Model\TaskTracker;

use Invis1ble\Messenger\Event\EventBusInterface;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Event\TaskTracker\HotfixTransitionedToDone;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\TaskTracker\TaskTrackerInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequestFactoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Board;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\IssueFactoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\IssueList;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\Key;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Project;
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
        IssueFactoryInterface $issueFactory,
        MergeRequestFactoryInterface $mergeRequestFactory,
        private EventBusInterface $eventBus,
        Project\Key $projectKey,
        Board\BoardId $sprintBoardId,
        int $sprintFieldId,
        private string $readyForPublishStatus = 'Ready for Publish',
        private string $transitionToDoneName = 'Close Issue',
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

    public function transitionHotfixesToDone(Key ...$keys): void
    {
        foreach ($this->readyForPublishHotfixes(...$keys) as $issue) {
            $transitions = $this->issueTransitions($issue->key);

            foreach ($transitions as $transition) {
                if ($this->transitionToDoneName === $transition['name']) {
                    $transitionToDoneId = $transition['id'];
                }
            }

            if (!isset($transitionToDoneId)) {
                throw new \RuntimeException("Transition $this->transitionToDoneName not found");
            }

            $request = $this->requestFactory->createRequest(
                'POST',
                $this->uriFactory->createUri("/rest/api/3/issue/$issue->key/transitions"),
            )
                ->withHeader('Content-Type', 'application/json')
                ->withBody($this->streamFactory->createStream(json_encode([
                    'transition' => $transitionToDoneId,
                ])))
            ;

            $statusCode = $this->httpClient->sendRequest($request)
                ->getStatusCode();

            if (204 !== $statusCode) {
                throw new \RuntimeException("Something went wrong during hotfix $issue->key transition to Done");
            }

            $this->eventBus->dispatch(new HotfixTransitionedToDone(
                projectKey: $this->projectKey,
                key: $issue->key,
            ));
        }
    }
}
