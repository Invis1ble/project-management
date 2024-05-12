<?php

declare(strict_types=1);

namespace ReleaseManagement\Shared\Infrastructure\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Status;

use ReleaseManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Status\Dictionary;
use ReleaseManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Status\StatusBlockedStatus;
use ReleaseManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Status\StatusChecking;
use ReleaseManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Status\StatusCiMustPass;
use ReleaseManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Status\StatusCiStillRunning;
use ReleaseManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Status\StatusConflict;
use ReleaseManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Status\StatusDiscussionsNotResolved;
use ReleaseManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Status\StatusDraftStatus;
use ReleaseManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Status\StatusExternalStatusChecks;
use ReleaseManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Status\StatusFactoryInterface;
use ReleaseManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Status\StatusInterface;
use ReleaseManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Status\StatusJiraAssociationMissing;
use ReleaseManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Status\StatusMergeable;
use ReleaseManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Status\StatusNeedRebase;
use ReleaseManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Status\StatusNotApproved;
use ReleaseManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Status\StatusNotOpen;
use ReleaseManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Status\StatusRequestedChanges;
use ReleaseManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Status\StatusUnchecked;

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
