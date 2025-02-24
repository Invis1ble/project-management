<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Ui\PublicationProgress;

use Symfony\Component\Console\Style\OutputStyle;

interface PublicationProgressFactoryInterface
{
    public function create(
        OutputStyle $io,
        Step $finalStep,
        int $eventLogTailSize = 30,
        ?string $dateTimeFormat = null,
    ): PublicationProgressInterface;
}
