<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\Serializer\DevelopmentCollaboration\MergeRequest;

use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequest;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequestList;
use Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\Serializer\AbstractListNormalizer;

final class MergeRequestListNormalizer extends AbstractListNormalizer
{
    protected function getSupportedType(): string
    {
        return MergeRequestList::class;
    }

    protected function getElementType(mixed $data): string
    {
        return MergeRequest::class;
    }
}
