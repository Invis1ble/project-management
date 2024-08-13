<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Tests\Shared\Domain\Model\SourceCodeRepository\Diff;

use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Diff;

trait CompareResponseFixtureTrait
{
    public function compareResponseFixture(
        Diff\DiffList $diffs,
    ): array {
        $compare = file_get_contents(__DIR__ . '/fixture/response/compare.200.json');
        $compare = json_decode($compare, true);

        return [
            'diffs' => iterator_to_array($diffs->map(fn (Diff\Diff $diff): array => [
                'old_path' => (string) $diff->oldPath,
                'new_path' => (string) $diff->newPath,
                'a_mode' => null,
                'b_mode' => '100644',
                'diff' => (string) $diff->content,
                'new_file' => $diff->newFile,
                'renamed_file' => $diff->renamedFile,
                'deleted_file' => $diff->deletedFile,
            ])),
        ] + $compare;
    }
}
