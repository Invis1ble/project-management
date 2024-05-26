<?php

declare(strict_types=1);

namespace ProjectManagement\HotfixPublication\Domain\Model\Status;

use ProjectManagement\HotfixPublication\Domain\Event\HotfixPublicationStatusChanged;
use ProjectManagement\HotfixPublication\Domain\Model\HotfixPublication;
use ProjectManagement\HotfixPublication\Domain\Model\HotfixPublicationInterface;

abstract readonly class AbstractStatus implements StatusInterface
{
    public function prepared(): bool
    {
        return false;
    }

    protected function setReleaseStatus(HotfixPublicationInterface $release, StatusInterface $status): void
    {
        $previousStatus = $release->status();
        $this->setReleaseProperty($release, 'status', $status);

        $reflection = new \ReflectionMethod($release, 'raiseDomainEvent');
        $reflection->invoke($release, new HotfixPublicationStatusChanged(
            id: $release->id(),
            branchName: $release->branchName(),
            status: $release->status(),
            previousStatus: $previousStatus,
            readyToMergeTasks: $release->readyToMergeHotfixes(),
            createdAt: $release->createdAt(),
        ));
    }

    protected function setReleaseProperty(HotfixPublicationInterface $release, string $propertyName, $value): void
    {
        if (!$release instanceof HotfixPublication) {
            throw new \InvalidArgumentException(sprintf(
                'Unsupported implementation %s: expected %s, got %s.',
                HotfixPublicationInterface::class,
                HotfixPublication::class,
                $release::class,
            ));
        }

        $reflection = new \ReflectionClass($release);
        $property = $reflection->getProperty($propertyName);
        $property->setValue($release, $value);
    }
}
