<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\ReleasePublication\Domain\Model;

use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\SourceCodeRepository\Branch\Name;
use Invis1ble\ProjectManagement\Shared\Domain\Model\AbstractUuid;
use Symfony\Component\Uid\Uuid;

final readonly class ReleasePublicationId extends AbstractUuid
{
    public static function fromBranchName(Name $branchName): self
    {
        return new self(Uuid::v5(
            namespace: Uuid::fromString(Uuid::NAMESPACE_OID),
            name: (string) $branchName,
        ));
    }
}
