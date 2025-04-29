<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Tests\Shared\Infrastructure\Domain\EventLog\EventFormatter\SourceCodeRepository\Commit;

use GuzzleHttp\Psr7\Uri;
use Invis1ble\Messenger\Event\EventInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Event\SourceCodeRepository\Commit\CommitCreated;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Branch;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Commit;
use Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\EventLog\EventFormatter\SourceCodeRepository\Commit\CommitCreatedFormatter;
use Invis1ble\ProjectManagement\Tests\Shared\Infrastructure\Domain\EventLog\EventFormatter\EventFormatterTestCase;

/**
 * @extends EventFormatterTestCase<CommitCreated>
 */
class CommitCreatedFormatterTest extends EventFormatterTestCase
{
    protected function createEventFormatter(): CommitCreatedFormatter
    {
        return new CommitCreatedFormatter();
    }

    protected function createEvent(): CommitCreated
    {
        return new CommitCreated(
            projectId: Project\ProjectId::from(1),
            branchName: Branch\Name::fromString('feature/test'),
            startBranchName: Branch\Name::fromString('develop'),
            commitId: Commit\CommitId::fromString('1234567890abcdef1234567890abcdef12345678'),
            message: Commit\Message::fromString('Init new branch'),
            guiUrl: new Uri('https://example.com/commit/1234567890abcdef1234567890abcdef12345678'),
            createdAt: new \DateTimeImmutable(),
        );
    }

    protected function createExpectedMessage(EventInterface $event): string
    {
        return "Commit `$event->message` on branch `$event->branchName` created";
    }
}
