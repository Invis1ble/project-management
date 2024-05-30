<?php

declare(strict_types=1);

namespace ProjectManagement\HotfixPublication\Domain\Model\Status;

use ProjectManagement\HotfixPublication\Domain\Event\HotfixPublicationStatusChanged;
use ProjectManagement\HotfixPublication\Domain\Model\HotfixPublication;
use ProjectManagement\HotfixPublication\Domain\Model\HotfixPublicationInterface;

abstract readonly class AbstractStatus implements StatusInterface
{
    protected function setPublicationStatus(
        HotfixPublicationInterface $publication,
        StatusInterface $status,
    ): void {
        $previousStatus = $publication->status();
        $this->setPublicationProperty($publication, 'status', $status);

        $reflection = new \ReflectionMethod($publication, 'raiseDomainEvent');
        $reflection->invoke($publication, new HotfixPublicationStatusChanged(
            id: $publication->id(),
            status: $publication->status(),
            previousStatus: $previousStatus,
            readyToMergeTasks: $publication->hotfixes(),
            createdAt: $publication->createdAt(),
        ));
    }

    protected function setPublicationProperty(
        HotfixPublicationInterface $publication,
        string $propertyName,
        $value,
    ): void {
        if (!$publication instanceof HotfixPublication) {
            throw new \InvalidArgumentException(sprintf(
                'Unsupported implementation %s: expected %s, got %s.',
                HotfixPublicationInterface::class,
                HotfixPublication::class,
                $publication::class,
            ));
        }

        $reflection = new \ReflectionClass($publication);
        $property = $reflection->getProperty($propertyName);
        $property->setValue($publication, $value);
    }
}
