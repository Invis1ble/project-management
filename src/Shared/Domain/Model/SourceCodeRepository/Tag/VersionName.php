<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Tag;

use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Ref;

readonly class VersionName extends Name
{
    private string $date;

    private int $i;

    public function __construct(string $value)
    {
        parent::__construct($value);

        preg_match('/^v\.([0-9]{2}-[0-9]{2}-[0-9]{2})\.([0-9]+)$/', $value, $matches);

        $this->date = $matches[1];
        $this->i = (int) $matches[2];
    }

    public static function create(): self
    {
        $now = new \DateTimeImmutable();

        return new self('v.' . $now->format('y-m-d') . '.0');
    }

    public static function fromRef(Ref $ref): self
    {
        return new self((string) $ref);
    }

    public function bumpVersion(): self
    {
        $i = $this->i + 1;

        return new self("v.$this->date.$i");
    }

    protected function validate(string $value): void
    {
        parent::validate($value);

        if (!preg_match('/^v\.[0-9]{2}-[0-9]{2}-[0-9]{2}\.[0-9]+$/', $value)) {
            throw new \InvalidArgumentException("Invalid version tag name format: $value");
        }
    }
}
