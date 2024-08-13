<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Diff;

interface CompareResultFactoryInterface
{
    public function createCompareResult(
        ?array $commit,
        array $commits,
        array $diffs,
        bool $compareTimout,
        bool $compareSameRef,
        string $guiUrl,
    ): CompareResult;
}
