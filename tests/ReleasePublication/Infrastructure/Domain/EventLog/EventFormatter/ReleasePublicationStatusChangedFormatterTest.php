<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Tests\ReleasePublication\Infrastructure\Domain\EventLog\EventFormatter;

use GuzzleHttp\Psr7\Uri;
use Invis1ble\Messenger\Event\EventInterface;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Event\ReleasePublicationStatusChanged;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\ReleasePublicationId;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\SourceCodeRepository\Branch;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\Status\StatusCreated;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\Status\StatusTasksWithoutMergeRequestTransitioned;
use Invis1ble\ProjectManagement\ReleasePublication\Infrastructure\Domain\EventLog\EventFormatter\ReleasePublicationStatusChangedFormatter;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Tag;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Board\BoardId;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Sprint;
use Invis1ble\ProjectManagement\Tests\Shared\Infrastructure\Domain\EventLog\EventFormatter\EventFormatterTestCase;

/**
 * @extends EventFormatterTestCase<ReleasePublicationStatusChanged>
 */
class ReleasePublicationStatusChangedFormatterTest extends EventFormatterTestCase
{
    protected function createEventFormatter(): ReleasePublicationStatusChangedFormatter
    {
        return new ReleasePublicationStatusChangedFormatter();
    }

    protected function createEvent(): ReleasePublicationStatusChanged
    {
        $branchName = Branch\Name::fromString('v-1-0-0');

        return new ReleasePublicationStatusChanged(
            id: ReleasePublicationId::fromBranchName($branchName),
            branchName: $branchName,
            tagName: Tag\VersionName::fromString('v.25-02-13.0'),
            tagMessage: Tag\Message::fromString('Fix terrible bug'),
            previousStatus: new StatusTasksWithoutMergeRequestTransitioned(),
            status: new StatusCreated(),
            tasks: new Issue\IssueList(
                new Issue\Issue(
                    id: Issue\IssueId::from(1),
                    key: Issue\Key::fromString('PROJECT-2'),
                    typeId: Issue\TypeId::fromString('3'),
                    subtask: false,
                    status: Issue\Status::fromString('Ready to Merge'),
                    summary: Issue\Summary::fromString('Fix terrible bug'),
                    sprints: new Sprint\SprintList(
                        new Sprint\Sprint(
                            boardId: BoardId::from(42),
                            name: Sprint\Name::fromString('June 2024 1-2'),
                            state: Sprint\State::Active,
                        ),
                    ),
                    mergeRequests: new MergeRequest\MergeRequestList(
                        new MergeRequest\MergeRequest(
                            iid: MergeRequest\MergeRequestIid::from(2),
                            title: MergeRequest\Title::fromString('Close PROJECT-1 Fix terrible bug'),
                            projectId: Project\ProjectId::from(4),
                            projectName: Project\Name::fromString('my-group/my-project'),
                            sourceBranchName: Branch\Name::fromString('PROJECT-1'),
                            targetBranchName: Branch\Name::fromString('master'),
                            status: MergeRequest\Status::Open,
                            guiUrl: new Uri('https://gitlab.example.com/my-group/my-project/-/merge_requests/2'),
                            details: new MergeRequest\Details\Details(
                                status: MergeRequest\Details\Status\StatusFactory::createStatus(MergeRequest\Details\Status\Dictionary::Mergeable),
                            ),
                        ),
                    ),
                    mergeRequestsToMerge: new MergeRequest\MergeRequestList(
                        new MergeRequest\MergeRequest(
                            iid: MergeRequest\MergeRequestIid::from(2),
                            title: MergeRequest\Title::fromString('Close PROJECT-1 Fix terrible bug'),
                            projectId: Project\ProjectId::from(4),
                            projectName: Project\Name::fromString('my-group/my-project'),
                            sourceBranchName: Branch\Name::fromString('PROJECT-1'),
                            targetBranchName: Branch\Name::fromString('master'),
                            status: MergeRequest\Status::Open,
                            guiUrl: new Uri('https://gitlab.example.com/my-group/my-project/-/merge_requests/2'),
                            details: new MergeRequest\Details\Details(
                                status: MergeRequest\Details\Status\StatusFactory::createStatus(MergeRequest\Details\Status\Dictionary::Mergeable),
                            ),
                        ),
                    ),
                ),
            ),
            createdAt: new \DateTimeImmutable(),
        );
    }

    protected function createExpectedMessage(EventInterface $event): string
    {
        return "Release publication $event->id `$event->tagName` status changed from `$event->previousStatus` to `$event->status`";
    }
}
