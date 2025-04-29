<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Tests\Shared\Infrastructure\Domain\EventLog\EventFormatter\SourceCodeRepository\Tag;

use Invis1ble\Messenger\Event\EventInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Event\SourceCodeRepository\Tag\TagCreated;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Ref;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Tag;
use Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\EventLog\EventFormatter\SourceCodeRepository\Tag\TagCreatedFormatter;
use Invis1ble\ProjectManagement\Tests\Shared\Infrastructure\Domain\EventLog\EventFormatter\EventFormatterTestCase;

/**
 * @extends EventFormatterTestCase<TagCreated>
 */
class TagCreatedFormatterTest extends EventFormatterTestCase
{
    protected function createEventFormatter(): TagCreatedFormatter
    {
        return new TagCreatedFormatter();
    }

    protected function createEvent(): TagCreated
    {
        return new TagCreated(
            projectId: Project\ProjectId::from(1),
            name: Tag\Name::fromString('v1.0.0'),
            ref: Ref::fromString('1234567890abcdef1234567890abcdef12345678'),
            message: Tag\Message::fromString('Release v1.0.0'),
            createdAt: new \DateTimeImmutable(),
        );
    }

    protected function createExpectedMessage(EventInterface $event): string
    {
        return "Tag `$event->name` created";
    }
}
