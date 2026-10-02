<?php

namespace App\Security\Voter;

use App\Entity\ComplianceActivity;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ComplianceActivityVoter extends Voter
{
    public const EDIT = 'EDIT_ACTIVITY';
    public const SUBMIT = 'SUBMIT_ACTIVITY';
    public const REVIEW = 'REVIEW_ACTIVITY';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::SUBMIT, self::REVIEW])
            && $subject instanceof ComplianceActivity;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        /** @var ComplianceActivity $activity */
        $activity = $subject;

        // QUAMC Admin can do anything
        if (in_array('ROLE_QUAMC_ADMIN', $user->getRoles())) {
            return true;
        }

        switch ($attribute) {
            case self::EDIT:
                // Only Program Heads of the specific program can edit, and only if draft/needs_revision
                if (in_array('ROLE_PROGRAM_HEAD', $user->getRoles()) && $user->getProgram()) {
                    if ($activity->getAreaAssignment()->getCycle()->getProgram()->getId() === $user->getProgram()->getId()) {
                        return in_array($activity->getState(), [ComplianceActivity::STATE_DRAFT, ComplianceActivity::STATE_NEEDS_REVISION]);
                    }
                }
                break;

            case self::SUBMIT:
                // Only Program Heads can submit
                if (in_array('ROLE_PROGRAM_HEAD', $user->getRoles()) && $user->getProgram()) {
                    if ($activity->getAreaAssignment()->getCycle()->getProgram()->getId() === $user->getProgram()->getId()) {
                        return true; // Actual state transitions are handled by Workflow
                    }
                }
                break;

            case self::REVIEW:
                // Only Internal Accreditors can review, and only if assigned to this area
                if (in_array('ROLE_INTERNAL_ACCREDITOR', $user->getRoles())) {
                    return true; // We rely on the DataScopeExtension for read-level scoping, but here we just grant the capability.
                }
                break;
        }

        return false;
    }
}
