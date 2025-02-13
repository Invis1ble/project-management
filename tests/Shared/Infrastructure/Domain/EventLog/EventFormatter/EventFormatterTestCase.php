<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Tests\Shared\Infrastructure\Domain\EventLog\EventFormatter;

use Invis1ble\Messenger\Event\EventInterface;
use Invis1ble\ProjectManagement\Shared\Domain\EventLog\EventFormatterInterface;
use PHPUnit\Framework\TestCase;

/**
 * @template T of EventInterface
 */
abstract class EventFormatterTestCase extends TestCase
{
    public function testFormat(): void
    {
        $formatter = $this->createEventFormatter();
        $event = $this->createEvent();

        $this->assertSame($this->createExpectedMessage($event), $formatter->format($event));
    }

    abstract protected function createEventFormatter(): EventFormatterInterface;

    /**
     * @return T
     */
    abstract protected function createEvent(): EventInterface;

    /**
     * @param T $event
     */
    abstract protected function createExpectedMessage(EventInterface $event): string;
}
