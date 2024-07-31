<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\ReleasePublication\Infrastructure\Domain\Model\Status;

use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\Status\Dictionary;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\Status\StatusFactory;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\Status\StatusInterface;
use Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\Serializer\AbstractValueObjectNormalizer;

final class StatusNormalizer extends AbstractValueObjectNormalizer
{
    public function denormalize(
        mixed $data,
        string $type,
        ?string $format = null,
        array $context = [],
    ): StatusInterface {
        return StatusFactory::createStatus(
            name: Dictionary::from($data),
        );
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
        return StatusInterface::class;
    }
}
