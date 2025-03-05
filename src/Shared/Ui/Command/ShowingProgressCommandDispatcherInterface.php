<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Ui\Command;

use Invis1ble\Messenger\Command\CommandInterface;
use Invis1ble\Messenger\Event\EventInterface;
use Symfony\Component\Console\Style\OutputStyle;

interface ShowingProgressCommandDispatcherInterface
{
    /**
     * @template T of object
     * @template TStatus of \BackedEnum
     *
     * @phpstan-param TStatus              $finalStatus
     *
     * @param class-string<T>              $publicationClass
     * @param class-string<EventInterface> $publicationStatusSetEventClass
     * @param class-string<TStatus>        $publicationStatusDictionaryClass
     */
    public function dispatch(
        OutputStyle $io,
        CommandInterface $command,
        string $initialStatus,
        \BackedEnum $finalStatus,
        string $publicationClass,
        string $publicationStatusSetEventClass,
        string $publicationStatusDictionaryClass,
    ): int;

    public function setDateTimeFormat(string $format): void;
}
