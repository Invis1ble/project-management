<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\ReleasePublication\Ui\Command;

use Invis1ble\Messenger\Query\QueryInterface;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Exception\ReleasePublicationNotFoundException;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\ReleasePublicationInterface;
use Invis1ble\ProjectManagement\Shared\Ui\Command\PublicationAwareCommand;

abstract class ReleasePublicationAwareCommand extends PublicationAwareCommand
{
    protected function getPublication(QueryInterface $query): ReleasePublicationInterface
    {
        $startTime = new \DateTimeImmutable();
        $untilTime = $startTime->add($this->pipelineMaxAwaitingTime);

        $getPublicationMaxTries = 3;
        $retryCounter = 0;

        while (new \DateTimeImmutable() <= $untilTime) {
            try {
                return $this->queryBus->ask($query);
            } catch (ReleasePublicationNotFoundException $exception) {
                // publication is not created, await async handlers
                sleep(3);
                ++$retryCounter;

                if ($retryCounter >= $getPublicationMaxTries) {
                    throw $exception;
                }

                continue;
            }
        }

        throw new ReleasePublicationNotFoundException();
    }
}
