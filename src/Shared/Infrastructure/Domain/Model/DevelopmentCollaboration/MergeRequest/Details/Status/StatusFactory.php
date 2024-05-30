<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Status;

use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Status\Dictionary;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Status\StatusBlockedStatus;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Status\StatusChecking;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Status\StatusCiMustPass;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Status\StatusCiStillRunning;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Status\StatusConflict;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Status\StatusDiscussionsNotResolved;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Status\StatusDraftStatus;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Status\StatusExternalStatusChecks;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Status\StatusFactoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Status\StatusInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Status\StatusJiraAssociationMissing;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Status\StatusMergeable;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Status\StatusNeedRebase;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Status\StatusNotApproved;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Status\StatusNotOpen;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Status\StatusRequestedChanges;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Status\StatusUnchecked;

final readonly class StatusFactory implements StatusFactoryInterface
{
    public function createStatus(string $status): StatusInterface
    {
        return match ($status) {
            Dictionary::BlockedStatus->value => new StatusBlockedStatus(),
            Dictionary::Checking->value => new StatusChecking(),
            Dictionary::Unchecked->value => new StatusUnchecked(),
            Dictionary::CiMustPass->value => new StatusCiMustPass(),
            Dictionary::CiStillRunning->value => new StatusCiStillRunning(),
            Dictionary::DiscussionsNotResolved->value => new StatusDiscussionsNotResolved(),
            Dictionary::DraftStatus->value => new StatusDraftStatus(),
            Dictionary::ExternalStatusChecks->value => new StatusExternalStatusChecks(),
            Dictionary::Mergeable->value => new StatusMergeable(),
            Dictionary::NotApproved->value => new StatusNotApproved(),
            Dictionary::NotOpen->value => new StatusNotOpen(),
            Dictionary::JiraAssociationMissing->value => new StatusJiraAssociationMissing(),
            Dictionary::NeedRebase->value => new StatusNeedRebase(),
            Dictionary::Conflict->value => new StatusConflict(),
            Dictionary::RequestedChanges->value => new StatusRequestedChanges(),
        };
    }
}
