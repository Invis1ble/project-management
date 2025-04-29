<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Application\Event;

use Invis1ble\Messenger\Event\EventInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Event\EventNameReducerInterface;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Serializer\SerializerInterface;

final readonly class SsePublishingEventHandler extends AbstractSsePublishingEventHandler
{
    public function __construct(
        HubInterface $hub,
        SerializerInterface $serializer,
        private EventNameReducerInterface $eventNameReducer,
    ) {
        parent::__construct($hub, $serializer);
    }

    public function __invoke(EventInterface $event): void
    {
        $this->publishUpdate(
            topics: '/api/events',
            data: [
                'name' => $this->eventNameReducer->reduce($event),
                'context' => $event,
                'published_at' => new \DateTimeImmutable(),
            ],
        );
    }
}
