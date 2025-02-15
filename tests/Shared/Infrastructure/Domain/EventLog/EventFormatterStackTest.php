<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Tests\Shared\Infrastructure\Domain\EventLog;

use Invis1ble\Messenger\Event\EventInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Event\SourceCodeRepository\Tag\TagCreated;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Ref;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Tag;
use Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\Event\EventNameReducer;
use Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\EventLog\EventFormatter\SourceCodeRepository\Tag\TagCreatedFormatter;
use Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\EventLog\EventFormatterStack;
use Invis1ble\ProjectManagement\Tests\Shared\Domain\Event\TestEvent;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\MockClock;

class EventFormatterStackTest extends TestCase
{
    #[DataProvider('provideFormatData')]
    public function testFormat(EventInterface $event, string $expectedMessage): void
    {
        $mockClock = new MockClock();

        $formatter = new EventFormatterStack(
            eventFormatters: [
                new TagCreatedFormatter(),
            ],
            eventNameReducer: new EventNameReducer(),
            clock: $mockClock,
        );

        $formatter->setFormat('[@%time%] %message%');
        $timeFormat = 'H:i:s.u';
        $formatter->setTimeFormat($timeFormat);

        $this->assertSame(
            expected: "[@{$mockClock->now()->format($timeFormat)}] $expectedMessage",
            actual: $formatter->format($event),
        );
    }

    /**
     * @return iterable<array>
     */
    public static function provideFormatData(): iterable
    {
        yield [new TestEvent(), 'Tests\Shared\Domain\Event\TestEvent'];

        $tagCreated = new TagCreated(
            projectId: Project\ProjectId::from(1),
            name: Tag\Name::fromString('v1.0.0'),
            ref: Ref::fromString('1234567890abcdef1234567890abcdef12345678'),
            message: Tag\Message::fromString('Release v1.0.0'),
            createdAt: new \DateTimeImmutable(),
        );

        yield [
            $tagCreated,
            "Tag `$tagCreated->name` created",
        ];
    }
}
