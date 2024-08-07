<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\Status;

use Invis1ble\ProjectManagement\ReleasePublication\Domain\Event\ReleasePublicationStatusChanged;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\ReleasePublication;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\ReleasePublicationInterface;

abstract readonly class AbstractStatus implements StatusInterface
{
    protected Context $context;

    public function __construct(?array $context = null)
    {
        $this->context = new Context($context);
    }

    public function equals(StatusInterface $status): bool
    {
        return static::class === $status::class
            && $this->context->equals($status->context)
        ;
    }

    public function context(): Context
    {
        return $this->context;
    }

    public function prepared(): bool
    {
        return false;
    }

    public function published(): bool
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
            tagName: $publication->tagName(),
            tagMessage: $publication->tagMessage(),
            previousStatus: $previousStatus,
            status: $publication->status(),
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
