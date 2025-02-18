<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Application\Event;

use Invis1ble\Messenger\Event\EventHandlerInterface;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Serializer\SerializerInterface;

abstract readonly class AbstractSsePublishingEventHandler implements EventHandlerInterface
{
    public function __construct(
        private HubInterface $hub,
        private SerializerInterface $serializer,
    ) {
    }

    protected function publishUpdate(string|array $topics, array|object $data): void
    {
        $this->hub->publish(new Update(
            topics: $topics,
            data: $this->serializer->serialize($data, 'json'),
        ));
    }
}
