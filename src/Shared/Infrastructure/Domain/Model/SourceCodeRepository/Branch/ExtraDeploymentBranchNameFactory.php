<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\Model\SourceCodeRepository\Branch;

use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Branch\ExtraDeploymentBranchNameFactoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Branch\Name;

final readonly class ExtraDeploymentBranchNameFactory implements ExtraDeploymentBranchNameFactoryInterface
{
    public function __construct(private ?string $branchName)
    {
    }

    public function createExtraDeploymentBranchName(): ?Name
    {
        if (null === $this->branchName || '' === $this->branchName) {
            return null;
        }

        return Name::fromString($this->branchName);
    }
}
