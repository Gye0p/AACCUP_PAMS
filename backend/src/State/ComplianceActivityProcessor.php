<?php

namespace App\State;

use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\ComplianceActivity;
use App\Entity\User;
use App\Repository\InternalAccreditorAssignmentRepository;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Workflow\WorkflowInterface;

#[AsDecorator('api_platform.doctrine.orm.state.persist_processor', priority: 20)]
class ComplianceActivityProcessor implements ProcessorInterface
{
    public function __construct(
        private ProcessorInterface $innerProcessor,
        private WorkflowInterface $complianceActivityStateMachine,
        private Security $security,
        private InternalAccreditorAssignmentRepository $assignmentRepository
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if ($data instanceof ComplianceActivity) {
            $this->authorize($data, $operation);

            // Enforce deadline rule
            $cycle = $data->getAreaAssignment()->getCycle();
            $deadline = $cycle->getComplianceDeadline();
            
            // Allow end date to be exactly on the deadline
            if ($deadline) {
                // Set time to end of day for deadline comparison
                $deadlineComparison = new \DateTimeImmutable($deadline->format('Y-m-d') . ' 23:59:59');
                if ($data->getEndDate() > $deadlineComparison) {
                    throw new UnprocessableEntityHttpException('Activity end date cannot exceed the cycle compliance deadline.');
                }
            }

            // Handle Workflow Transitions
            if ($transition = $data->getTransition()) {
                if ($this->complianceActivityStateMachine->can($data, $transition)) {
                    $this->complianceActivityStateMachine->apply($data, $transition);
                } else {
                    throw new UnprocessableEntityHttpException(sprintf('Cannot apply transition "%s" from state "%s".', $transition, $data->getState()));
                }
            }
        }

        return $this->innerProcessor->process($data, $operation, $uriVariables, $context);
    }

    private function authorize(ComplianceActivity $activity, Operation $operation): void
    {
        $user = $this->security->getUser();
        $assignment = $activity->getAreaAssignment();

        if (!$user instanceof User || $assignment === null) {
            throw new AccessDeniedHttpException('A valid authenticated user and area assignment are required.');
        }

        if ($this->security->isGranted('ROLE_QUAMC_ADMIN')) {
            return;
        }

        $transition = $activity->getTransition();
        if ($this->security->isGranted('ROLE_PROGRAM_HEAD')) {
            if ($assignment->getCycle()->getProgram() !== $user->getProgram()) {
                throw new AccessDeniedHttpException('You can only manage activities in your assigned program.');
            }

            if (!$operation instanceof Post && !in_array($activity->getState(), [ComplianceActivity::STATE_DRAFT, ComplianceActivity::STATE_NEEDS_REVISION], true)) {
                throw new AccessDeniedHttpException('Only draft activities or activities needing revision can be edited.');
            }

            if ($transition !== null && $transition !== 'submit') {
                throw new AccessDeniedHttpException('Program Heads may only submit activities.');
            }
            return;
        }

        if ($this->security->isGranted('ROLE_INTERNAL_ACCREDITOR')) {
            $assigned = $this->assignmentRepository->findOneBy([
                'internalAccreditor' => $user,
                'areaAssignment' => $assignment,
            ]);

            if ($assigned === null || $transition === null || !in_array($transition, ['start_review', 'request_revision', 'approve'], true)) {
                throw new AccessDeniedHttpException('You may only review activities assigned to you.');
            }
            return;
        }

        throw new AccessDeniedHttpException('You are not allowed to modify this activity.');
    }
}
