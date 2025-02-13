<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\EventLog\EventFormatter;

use Invis1ble\ProjectManagement\Shared\Domain\EventLog\EventFormatterInterface;

/**
 * @template T
 *
 * @extends EventFormatterInterface<T>
 */
abstract readonly class AbstractFormatter implements EventFormatterInterface
{
}
