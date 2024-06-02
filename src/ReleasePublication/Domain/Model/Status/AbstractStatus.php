<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\Status;

use Invis1ble\ProjectManagement\ReleasePublication\Domain\Event\ReleasePublicationStatusChanged;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\ReleasePublication;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\ReleasePublicationInterface;

abstract readonly class AbstractStatus implements StatusInterface
{
    public function prepared(): bool
    {
        return false;
    }

    protected function setPublicationStatus(
        ReleasePublicationInterface $publication,
        StatusInterface $status,
    ): void {
        $previousStatus = $publication->status();
        $this->setPublicationProperty($publication, 'status', $status);

        $reflection = new \ReflectionMethod($publication, 'raiseDomainEvent');
        $reflection->invoke($publication, new ReleasePublicationStatusChanged(
            id: $publication->id(),
            branchName: $publication->branchName(),
            status: $publication->status(),
            previousStatus: $previousStatus,
            readyToMergeTasks: $publication->readyToMergeTasks(),
            createdAt: $publication->createdAt(),
        ));
    }

    protected function setPublicationProperty(
        ReleasePublicationInterface $publication,
        string $propertyName,
        $value,
    ): void {
        if (!$publication instanceof ReleasePublication) {
            throw new \InvalidArgumentException(sprintf(
                'Unsupported implementation %s: expected %s, got %s.',
                ReleasePublicationInterface::class,
                ReleasePublication::class,
                $publication::class,
            ));
        }

        $reflection = new \ReflectionClass($publication);
        $property = $reflection->getProperty($propertyName);
        $property->setValue($publication, $value);
    }
}
