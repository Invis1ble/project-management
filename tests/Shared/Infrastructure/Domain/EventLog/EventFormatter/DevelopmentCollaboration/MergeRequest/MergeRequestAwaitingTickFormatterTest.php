<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Tests\Shared\Infrastructure\Domain\EventLog\EventFormatter\DevelopmentCollaboration\MergeRequest;

use GuzzleHttp\Psr7\Uri;
use Invis1ble\Messenger\Event\EventInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Event\DevelopmentCollaboration\MergeRequest\MergeRequestAwaitingTick;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Branch;
use Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\EventLog\EventFormatter\DevelopmentCollaboration\MergeRequest\MergeRequestAwaitingTickFormatter;
use Invis1ble\ProjectManagement\Tests\Shared\Infrastructure\Domain\EventLog\EventFormatter\EventFormatterTestCase;

/**
 * @extends EventFormatterTestCase<MergeRequestAwaitingTick>
 */
class MergeRequestAwaitingTickFormatterTest extends EventFormatterTestCase
{
    protected function createEventFormatter(): MergeRequestAwaitingTickFormatter
    {
        return new MergeRequestAwaitingTickFormatter();
    }

    protected function createEvent(): MergeRequestAwaitingTick
    {
        return new MergeRequestAwaitingTick(
            projectId: Project\ProjectId::from(1),
            projectName: Project\Name::fromString('my-group/my-project'),
            mergeRequestIid: MergeRequest\MergeRequestIid::from(2),
            title: MergeRequest\Title::fromString('Fix bug'),
            sourceBranchName: Branch\Name::fromString('TEST-1'),
            targetBranchName: Branch\Name::fromString('develop'),
            status: MergeRequest\Status::Open,
            guiUrl: new Uri('https://gitlab.example.com/my-group/my-project/merge_requests/2'),
            details: new MergeRequest\Details\Details(
                status: new MergeRequest\Details\Status\StatusChecking(),
            ),
            tickInterval: new \DateInterval('PT10S'),
            maxAwaitingTime: new \DateInterval('PT1M'),
        );
    }

    protected function createExpectedMessage(EventInterface $event): string
    {
        return "MR $event->guiUrl awaiting tick (`$event->sourceBranchName` -> `$event->targetBranchName` | `$event->title`)";
    }
}
