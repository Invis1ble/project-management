<?php

declare(strict_types=1);

namespace ReleaseManagement\ReleasePublication\Domain\Model;

use ReleaseManagement\ReleasePublication\Domain\Model\SourceCodeRepository\Branch\Name;
use ReleaseManagement\Shared\Domain\Model\AbstractUuid;
use Symfony\Component\Uid\Uuid;

final readonly class ReleasePublicationId extends AbstractUuid
{
    public static function generate(Name $branchName): self
    {
        return new self(Uuid::v5(
            namespace: Uuid::fromString(Uuid::NAMESPACE_OID),
            name: (string) $branchName,
        ));
    }
}
