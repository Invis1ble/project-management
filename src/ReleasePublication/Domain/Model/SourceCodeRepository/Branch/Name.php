<?php

declare(strict_types=1);

namespace ReleaseManagement\ReleasePublication\Domain\Model\SourceCodeRepository\Branch;

use ReleaseManagement\Shared\Domain\Model\SourceCodeRepository\Branch\Name as BasicName;

final readonly class Name extends BasicName
{
    private int $majorVersion;

    private int $minorVersion;

    private int $patch;

    public function __construct(string $value)
    {
        parent::__construct($value);

        preg_match("/^v-([0-9]+)-([0-9]+)-([0-9]+)$/", $value, $matches);

        $this->majorVersion = (int) $matches[1];
        $this->minorVersion = (int) $matches[2];
        $this->patch = (int) $matches[3];
    }

    public function versionNewerThan(self $name): bool
    {
        return 1 === $this->versionCompare($name);
    }

    public function versionCompare(self $name): int
    {
        return version_compare($this->value, $name->value);
    }

    public function bumpVersion(): self
    {
        $patch = $this->patch + 1;

        return new self("v-$this->majorVersion-$this->minorVersion-$patch");
    }

    protected function validate(string $value): void
    {
        parent::validate($value);

        if (!preg_match("/^v(?:-[0-9]+){3}$/", $value)) {
            throw new \InvalidArgumentException("Invalid release branch name format: $value");
        }
    }
}
