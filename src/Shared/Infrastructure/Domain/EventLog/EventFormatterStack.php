<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\EventLog;

use Invis1ble\Messenger\Event\EventInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Event\EventNameReducerInterface;
use Invis1ble\ProjectManagement\Shared\Domain\EventLog\EventFormatterInterface;
use Invis1ble\ProjectManagement\Shared\Domain\EventLog\EventFormatterStackInterface;
use Psr\Clock\ClockInterface;

final class EventFormatterStack implements EventFormatterStackInterface
{
    /**
     * @param iterable<EventFormatterInterface> $eventFormatters
     */
    public function __construct(
        private readonly iterable $eventFormatters,
        private readonly EventNameReducerInterface $eventNameReducer,
        private readonly ClockInterface $clock,
        private string $format = '[%time%] %message%',
        private string $timeFormat = \DATE_ATOM,
    ) {
    }

    public function setFormat(string $format): void
    {
        $this->format = $format;
    }

    public function setTimeFormat(string $timeFormat): void
    {
        $this->timeFormat = $timeFormat;
    }

    public function format(EventInterface $event): string
    {
        foreach ($this->eventFormatters as $formatter) {
            if ($formatter->supports($event)) {
                $message = $formatter->format($event);
            }
        }

        if (!isset($message)) {
            $message = $this->eventNameReducer->reduce($event);
        }

        return str_replace(
            search: [
                '%time%',
                '%message%',
            ],
            replace: [
                $this->clock->now()->format($this->timeFormat),
                $message,
            ],
            subject: $this->format,
        );
    }
}
