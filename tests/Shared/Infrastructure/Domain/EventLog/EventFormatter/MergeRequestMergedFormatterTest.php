<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Tests\Shared\Infrastructure\Domain\EventLog\EventFormatter;

use GuzzleHttp\Psr7\Uri;
use Invis1ble\Messenger\Event\EventInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Event\DevelopmentCollaboration\MergeRequest\MergeRequestMerged;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Branch;
use Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\EventLog\EventFormatter\MergeRequestMergedFormatter;

/**
 * @extends EventFormatterTestCase<MergeRequestMerged>
 */
class MergeRequestMergedFormatterTest extends EventFormatterTestCase
{
    protected function createEventFormatter(): MergeRequestMergedFormatter
    {
        return new MergeRequestMergedFormatter();
    }

    protected function createEvent(): MergeRequestMerged
    {
        return new MergeRequestMerged(
            projectId: Project\ProjectId::from(1),
            projectName: Project\Name::fromString('my-group/my-project'),
            mergeRequestIid: MergeRequest\MergeRequestIid::from(2),
            title: MergeRequest\Title::fromString('Fix bug'),
            sourceBranchName: Branch\Name::fromString('TEST-1'),
            targetBranchName: Branch\Name::fromString('develop'),
            status: MergeRequest\Status::Open,
            guiUrl: new Uri('https://gitlab.example.com/my-group/my-project/merge_requests/1'),
            details: new MergeRequest\Details\Details(
                status: new MergeRequest\Details\Status\StatusChecking(),
            ),
        );
    }

    protected function createExpectedMessage(EventInterface $event): string
    {
        return "MR $event->guiUrl merged ($event->sourceBranchName -> $event->targetBranchName | $event->title)";
    }
}
