<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\ReleasePublication\Domain\Repository;

use Invis1ble\ProjectManagement\ReleasePublication\Domain\Exception\ReleasePublicationNotFoundException;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\ReleasePublicationId;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\ReleasePublicationInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Tag;

interface ReleasePublicationRepositoryInterface
{
    public function contains(ReleasePublicationId $id): bool;

    /**
     * @throws ReleasePublicationNotFoundException
     */
    public function get(ReleasePublicationId $id): ReleasePublicationInterface;

    /**
     * @throws ReleasePublicationNotFoundException
     */
    public function getLatest(): ReleasePublicationInterface;

    /**
     * @throws ReleasePublicationNotFoundException
     */
    public function getLatestByTagName(Tag\VersionName $tagName): ReleasePublicationInterface;

    public function store(ReleasePublicationInterface $releasePublication): void;
}
