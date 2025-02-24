<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\HotfixPublication\Infrastructure\Ui\Command;

use Invis1ble\ProjectManagement\HotfixPublication\Ui\Command\PublicationProgressFactoryInterface;
use Invis1ble\ProjectManagement\HotfixPublication\Ui\Command\PublicationProgressInterface;
use Invis1ble\ProjectManagement\Shared\Domain\EventLog\EventFormatterStackInterface;
use Symfony\Component\Console\Style\OutputStyle;

final readonly class PublicationProgressFactory implements PublicationProgressFactoryInterface
{
    public function __construct(private EventFormatterStackInterface $eventFormatter)
    {
    }

    public function create(
        OutputStyle $io,
        int $eventLogTailSize = 30,
        ?string $dateTimeFormat = null,
    ): PublicationProgressInterface {
        return new PublicationProgress(
            io: $io,
            eventFormatter: $this->eventFormatter,
            eventLogTailSize: $eventLogTailSize,
            dateTimeFormat: $dateTimeFormat ?? PublicationProgress::DEFAULT_DATETIME_FORMAT,
        );
    }
}
