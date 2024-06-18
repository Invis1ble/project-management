<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\HotfixPublication\Infrastructure\Domain\Model\Status;

use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\Status\Dictionary;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\Status\StatusFactory;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\Status\StatusInterface;
use Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\Serializer\AbstractValueObjectNormalizer;

final class StatusNormalizer extends AbstractValueObjectNormalizer
{
    public function denormalize(
        mixed $data,
        string $type,
        ?string $format = null,
        array $context = [],
    ): StatusInterface {
        if (is_string($data)) {
            $name = $data;
            $context = null;
        } else {
            $name = $data['name'];
            $context = $data['context'] ?? null;
        }

        return StatusFactory::createStatus(
            name: Dictionary::from($name),
            context: $context,
        );
    }

    public function normalize(
        mixed $object,
        ?string $format = null,
        array $context = [],
    ): array|string {
        $context = $object->context()
            ->toArray();

        if (null === $context) {
            return (string) $object;
        }

        return [
            'name' => (string) $object,
            'context' => $context,
        ];
    }

    protected function getSupportedType(): string
    {
        return StatusInterface::class;
    }
}
