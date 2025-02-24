<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Infrastructure\Ui\PublicationProgress;

use Invis1ble\ProjectManagement\Shared\Domain\EventLog\EventFormatterStackInterface;
use Invis1ble\ProjectManagement\Shared\Ui\PublicationProgress\PublicationProgressFactoryInterface;
use Invis1ble\ProjectManagement\Shared\Ui\PublicationProgress\PublicationProgressInterface;
use Invis1ble\ProjectManagement\Shared\Ui\PublicationProgress\Step;
use Symfony\Component\Console\Style\OutputStyle;

final readonly class PublicationProgressFactory implements PublicationProgressFactoryInterface
{
    public function __construct(private EventFormatterStackInterface $eventFormatter)
    {
    }

    public function create(
        OutputStyle $io,
        Step $finalStep,
        int $eventLogTailSize = 30,
        ?string $dateTimeFormat = null,
    ): PublicationProgressInterface {
        return new PublicationProgress(
            io: $io,
            eventFormatter: $this->eventFormatter,
            finalStep: $finalStep,
            eventLogTailSize: $eventLogTailSize,
            dateTimeFormat: $dateTimeFormat ?? PublicationProgress::DEFAULT_DATETIME_FORMAT,
        );
    }
}
