<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\Status;

use Invis1ble\ProjectManagement\HotfixPublication\Domain\Event\HotfixPublicationStatusChanged;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\HotfixPublication;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\HotfixPublicationInterface;

abstract readonly class AbstractStatus implements StatusInterface
{
    protected Context $context;

    public function __construct(?array $context = null)
    {
        $this->context = new Context($context);
    }

    /**
     * @todo remove this method
     */
    public function reset(HotfixPublicationInterface $publication): void
    {
        $this->setPublicationStatus($publication, new StatusCreated());
    }

    public function published(): bool
    {
        return false;
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

    protected function setPublicationStatus(
        HotfixPublicationInterface $publication,
        StatusInterface $status,
    ): void {
        $previousStatus = $publication->status();
        $this->setPublicationProperty($publication, 'status', $status);

        $reflection = new \ReflectionMethod($publication, 'raiseDomainEvent');
        $reflection->invoke($publication, new HotfixPublicationStatusChanged(
            id: $publication->id(),
            tagName: $publication->tagName(),
            tagMessage: $publication->tagMessage(),
            status: $publication->status(),
            previousStatus: $previousStatus,
            hotfixes: $publication->hotfixes(),
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
