<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\Model\SourceCodeRepository\Branch;

use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Branch\ExtraDeployBranchNameFactoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Branch\Name;

final readonly class ExtraDeployBranchNameFactory implements ExtraDeployBranchNameFactoryInterface
{
    public function __construct(private string $branchName)
    {
    }

    public function createExtraDeployBranchName(): ?Name
    {
        if (null === $this->branchName || '' === $this->branchName) {
            return null;
        }

        return Name::fromString($this->branchName);
    }
}
