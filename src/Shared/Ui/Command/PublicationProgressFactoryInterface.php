<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Ui\Command;

use Symfony\Component\Console\Style\OutputStyle;

interface PublicationProgressFactoryInterface
{
    public function create(
        OutputStyle $io,
        int $eventLogTailSize = 30,
        ?string $dateTimeFormat = null,
    ): PublicationProgressInterface;
}
