<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Tests\Shared\Domain\Model\SourceCodeRepository\Branch;

use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Branch;

trait BranchResponseFixtureTrait
{
    public function branchResponseFixture(
        Branch\Name $name,
    ): array {
        $branch = file_get_contents(__DIR__ . '/fixture/response/branch.200.json');
        $branch = json_decode($branch, true);

        return [
            'name' => (string) $name,
        ] + $branch;
    }
}
