<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Infrastructure\Ui\Command;

use Invis1ble\Messenger\Command\CommandBusInterface;
use Invis1ble\Messenger\Command\CommandInterface;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\HotfixPublication;
use Invis1ble\ProjectManagement\Shared\Domain\Event\EventNameReducerInterface;
use Invis1ble\ProjectManagement\Shared\Ui\Command\ShowingProgressCommandDispatcherInterface;
use Invis1ble\ProjectManagement\Shared\Ui\PublicationProgress\PublicationProgressFactoryInterface;
use Invis1ble\ProjectManagement\Shared\Ui\PublicationProgress\StepResolverInterface;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Style\OutputStyle;
use Symfony\Component\HttpClient\Chunk\ServerSentEvent;
use Symfony\Component\HttpClient\EventSourceHttpClient;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Serializer\SerializerInterface;

final class ShowingProgressCommandDispatcher implements ShowingProgressCommandDispatcherInterface
{
    public function __construct(
        private readonly CommandBusInterface $commandBus,
        private readonly SerializerInterface $serializer,
        private readonly \DateInterval $pipelineMaxAwaitingTime,
        private readonly HubInterface $mercureHub,
        private readonly EventNameReducerInterface $eventNameReducer,
        private readonly StepResolverInterface $publicationProgressStepResolver,
        private readonly PublicationProgressFactoryInterface $publicationProgressFactory,
        private ?string $dateTimeFormat = 'Y-m-d H:i:s',
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch(
        OutputStyle $io,
        CommandInterface $command,
        string $initialStatus,
        \BackedEnum $finalStatus,
        string $publicationClass,
        string $publicationStatusSetEventClass,
        string $publicationStatusDictionaryClass,
    ): int {
        $status = $initialStatus;

        $publicationProgress = $this->publicationProgressFactory->create(
            io: $io,
            initialStep: $this->publicationProgressStepResolver->resolve($publicationStatusDictionaryClass::tryFrom($initialStatus)),
            finalStep: $this->publicationProgressStepResolver->resolve($finalStatus),
            initialStatus: $initialStatus,
            dateTimeFormat: $this->dateTimeFormat,
        );

        $publicationProgress->start();

        $topics = ['/api/events'];

        $url = $this->mercureHub->getPublicUrl();

        $separator = '?';

        foreach ($topics as $topic) {
            $url .= $separator . 'topic=' . rawurlencode($topic);

            if ('?' === $separator) {
                $separator = '&';
            }
        }

        $client = HttpClient::create();
        $client = new EventSourceHttpClient($client, 10);
        $source = $client->connect($url);

        $untilTime = (new \DateTimeImmutable())->add($this->pipelineMaxAwaitingTime);

        $result = SymfonyCommand::FAILURE;

        $this->commandBus->dispatch($command);

        while (true) {
            foreach ($client->stream($source, 0.1) as $chunk) {
                if ($chunk->isTimeout()) {
                    continue;
                }

                if (new \DateTimeImmutable() > $untilTime) {
                    $publicationProgress->setStatus("stuck in $status");

                    break 2;
                }

                if ($chunk->isLast()) {
                    break 2;
                }

                if ($chunk instanceof ServerSentEvent) {
                    $data = $chunk->getArrayData();
                    $eventClass = $this->eventNameReducer->expand($data['name']);

                    if (is_subclass_of($eventClass, $publicationStatusSetEventClass)) {
                        $untilTime = (new \DateTimeImmutable())->add($this->pipelineMaxAwaitingTime);

                        /** @var HotfixPublication $publication */
                        $publication = $this->serializer->denormalize($data['context'], $publicationClass);

                        $status = (string) $publication->status();
                        $publicationProgress->setStatus($status);
                        $publicationProgress->setProgress(
                            $this->publicationProgressStepResolver->resolve($publicationStatusDictionaryClass::from($status)),
                        );

                        if ($publication->published()) {
                            $publicationProgress->finish();

                            $result = SymfonyCommand::SUCCESS;

                            break 2;
                        }
                    } else {
                        $event = $this->serializer->denormalize($data['context'], $eventClass);
                        $publicationProgress->addEvent($event);
                    }
                }
            }
        }

        $io->newLine();

        return $result;
    }

    public function setDateTimeFormat(string $format): void
    {
        $this->dateTimeFormat = $format;
    }
}
