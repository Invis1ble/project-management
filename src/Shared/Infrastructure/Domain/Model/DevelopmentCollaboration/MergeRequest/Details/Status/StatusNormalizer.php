<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Status;

use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Status\StatusFactoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Status\StatusInterface;
use Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\Serializer\AbstractValueObjectNormalizer;

final class StatusNormalizer extends AbstractValueObjectNormalizer
{
    public function __construct(private StatusFactoryInterface $statusFactory)
    {
    }

    public function denormalize(
        mixed $data,
        string $type,
        ?string $format = null,
        array $context = [],
    ): StatusInterface {
        return $this->statusFactory->createStatus($data);
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
