<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\Serializer\ContinuousIntegration\Job\Status;

use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Job\Status;
use Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\Serializer\AbstractValueObjectNormalizer;

final class StatusNormalizer extends AbstractValueObjectNormalizer
{
    public function __construct(private readonly Status\StatusFactoryInterface $statusFactory)
    {
    }

    public function denormalize(
        mixed $data,
        string $type,
        ?string $format = null,
        array $context = [],
    ): Status\StatusInterface {
        return $this->statusFactory->createStatus(Status\Dictionary::from($data));
    }

    public function normalize(
        mixed $object,
        ?string $format = null,
        array $context = [],
    ): string {
        return (string) $object;
    }

    protected function getSupportedType(): string
    {
        return Status\StatusInterface::class;
    }
}
