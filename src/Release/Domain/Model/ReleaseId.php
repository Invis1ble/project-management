<?php

declare(strict_types=1);

namespace ReleaseManagement\Release\Domain\Model;

use ReleaseManagement\Shared\Domain\Model\AbstractUuid;
use Symfony\Component\Uid\Uuid;

final readonly class ReleaseId extends AbstractUuid
{
    public static function generate(ReleaseBranchName $branchName): self
    {
        return new self(Uuid::v5(
            namespace: Uuid::fromString(Uuid::NAMESPACE_OID),
            name: (string) $branchName,
        ));
    }
}
