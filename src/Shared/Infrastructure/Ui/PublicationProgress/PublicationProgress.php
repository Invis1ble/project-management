<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Infrastructure\Ui\PublicationProgress;

use Invis1ble\Messenger\Event\EventInterface;
use Invis1ble\ProjectManagement\Shared\Domain\EventLog\EventFormatterStackInterface;
use Invis1ble\ProjectManagement\Shared\Ui\PublicationProgress\PublicationProgressInterface;
use Invis1ble\ProjectManagement\Shared\Ui\PublicationProgress\Step;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Style\OutputStyle;

final class PublicationProgress implements PublicationProgressInterface
{
    public const string DEFAULT_DATETIME_FORMAT = 'Y-m-d H:i:s';

    private readonly ProgressBar $progressBar;

    /**
     * @var string[]
     */
    private array $eventLog = [];

    public function __construct(
        OutputStyle $io,
        private readonly EventFormatterStackInterface $eventFormatter,
        private readonly ?Step $initialStep,
        Step $finalStep,
        string $initialStatus = 'inited',
        private readonly int $eventLogTailSize = 30,
        private readonly ?string $dateTimeFormat = self::DEFAULT_DATETIME_FORMAT,
    ) {
        $format = <<<FORMAT

 <fg=blue>[%time%] Publication status: %status%</>

 %current%/%max% [%bar%] %percent:3s%%  |  %elapsed:6s%/%estimated:-6s%  |  %memory:6s%

    <fg=gray>%event_log_tail%</>
FORMAT;

        ProgressBar::setFormatDefinition('custom', $format);

        $this->progressBar = $io->createProgressBar($finalStep->value);
        $this->progressBar->setFormat('custom');
        $this->progressBar->maxSecondsBetweenRedraws(0.1);

        $this->setStatus(
            status: $initialStatus,
            display: false,
        );

        $this->progressBar->setMessage(
            message: "\n",
            name: 'event_log_tail',
        );
    }

    public function start(): void
    {
        $this->progressBar->start(
            startAt: $this->initialStep?->value ?? 0,
        );
    }

    public function setProgress(Step $step): void
    {
        $this->progressBar->setProgress($step->value);
    }

    public function finish(): void
    {
        $this->progressBar->finish();
    }

    public function setStatus(string $status, bool $display = true): void
    {
        $this->progressBar->setMessage(
            (new \DateTimeImmutable())->format($this->dateTimeFormat),
            'time',
        );
        $this->progressBar->setMessage($status, 'status');

        if ($display) {
            $this->progressBar->display();
        }
    }

    public function addEvent(EventInterface $event): void
    {
        $this->eventLog[] = $this->eventFormatter->format($event);

        $this->progressBar->setMessage(
            message: join("\n    ", array_slice($this->eventLog, -$this->eventLogTailSize)) . "\n",
            name: 'event_log_tail',
        );

        $this->progressBar->display();
    }
}
