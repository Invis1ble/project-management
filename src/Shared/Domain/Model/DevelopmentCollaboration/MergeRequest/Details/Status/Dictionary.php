<?php

declare(strict_types=1);

namespace ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Status;

enum Dictionary: string
{
    case BlockedStatus = 'blocked_status';

    case Checking = 'checking';

    case Unchecked = 'unchecked';

    case CiMustPass = 'ci_must_pass';

    case CiStillRunning = 'ci_still_running';

    case DiscussionsNotResolved = 'discussions_not_resolved';

    case DraftStatus = 'draft_status';

    case ExternalStatusChecks = 'external_status_checks';

    case Mergeable = 'mergeable';

    case NotApproved = 'not_approved';

    case NotOpen = 'not_open';

    case JiraAssociationMissing = 'jira_association_missing';

    case NeedRebase = 'need_rebase';

    case Conflict = 'conflict';

    case RequestedChanges = 'requested_changes';
}
