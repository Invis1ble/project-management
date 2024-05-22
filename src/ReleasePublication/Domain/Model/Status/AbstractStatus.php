<?php

declare(strict_types=1);

namespace ProjectManagement\ReleasePublication\Domain\Model\Status;

use ProjectManagement\ReleasePublication\Domain\Event\ReleasePublicationStatusChanged;
use ProjectManagement\ReleasePublication\Domain\Model\ReleasePublication;
use ProjectManagement\ReleasePublication\Domain\Model\ReleasePublicationInterface;

abstract readonly class AbstractStatus implements StatusInterface
{
    protected function setReleaseStatus(ReleasePublicationInterface $release, StatusInterface $status): void
    {
        $previousStatus = $release->status();
        $this->setReleaseProperty($release, 'status', $status);

        $reflection = new \ReflectionMethod($release, 'raiseDomainEvent');
        $reflection->invoke($release, new ReleasePublicationStatusChanged(
            id: $release->id(),
            branchName: $release->branchName(),
            status: $release->status(),
            previousStatus: $previousStatus,
            readyToMergeTasks: $release->readyToMergeTasks(),
            createdAt: $release->createdAt(),
        ));
    }

    protected function setReleaseProperty(ReleasePublicationInterface $release, string $propertyName, $value): void
    {
        if (!$release instanceof ReleasePublication) {
            throw new \InvalidArgumentException(sprintf(
                'Unsupported implementation %s: expected %s, got %s.',
                ReleasePublicationInterface::class,
                ReleasePublication::class,
                $release::class,
            ));
        }

        $reflection = new \ReflectionClass($release);
        $property = $reflection->getProperty($propertyName);
        $property->setValue($release, $value);
    }
}
