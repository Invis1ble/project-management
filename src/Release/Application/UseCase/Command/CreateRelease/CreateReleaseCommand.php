<?php

declare(strict_types=1);

namespace ReleaseManagement\Release\Application\UseCase\Command\CreateRelease;

use Invis1ble\Messenger\Command\CommandInterface;
use ReleaseManagement\Release\Domain\Model\ReleaseBranchName;

final readonly class CreateReleaseCommand implements CommandInterface
{
    public function __construct(public ?ReleaseBranchName $branchName)
    {
    }
}
