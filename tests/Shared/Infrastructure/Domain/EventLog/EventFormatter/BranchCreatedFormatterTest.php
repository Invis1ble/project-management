<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Tests\Shared\Infrastructure\Domain\EventLog\EventFormatter;

use GuzzleHttp\Psr7\Uri;
use Invis1ble\Messenger\Event\EventInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Event\SourceCodeRepository\Branch\BranchCreated;
use Invis1ble\ProjectManagement\Shared\Domain\EventLog\EventFormatterInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Branch;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Commit;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Ref;
use Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\EventLog\EventFormatter\BranchCreatedFormatter;

/**
 * @extends EventFormatterTestCase<BranchCreated>
 */
class BranchCreatedFormatterTest extends EventFormatterTestCase
{
    protected function createEventFormatter(): EventFormatterInterface
    {
        return new BranchCreatedFormatter();
    }

    protected function createEvent(): BranchCreated
    {
        return new BranchCreated(
            projectId: Project\ProjectId::from(1),
            ref: Ref::fromString('1234567890abcdef1234567890abcdef12345678'),
            name: Branch\Name::fromString('feature/test'),
            protected: false,
            guiUrl: new Uri('https://example.com/branch/feature/test'),
            commitId: Commit\CommitId::fromString('87654321fedcba0987654321fecdba0987654321'),
            commitMessage: Commit\Message::fromString('Init new branch'),
            commitCreatedAt: new \DateTimeImmutable(),
        );
    }

    protected function createExpectedMessage(EventInterface $event): string
    {
        return "Branch `$event->name` created ($event->guiUrl)";
    }
}
