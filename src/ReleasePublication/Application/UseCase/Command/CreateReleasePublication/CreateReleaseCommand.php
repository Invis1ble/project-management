<?php

declare(strict_types=1);

namespace ProjectManagement\ReleasePublication\Application\UseCase\Command\CreateReleasePublication;

use Invis1ble\Messenger\Command\CommandInterface;
use ProjectManagement\ReleasePublication\Domain\Model\SourceCodeRepository\Branch\Name;

final readonly class CreateReleaseCommand implements CommandInterface
{
    public function __construct(public ?Name $branchName)
    {
    }
}
