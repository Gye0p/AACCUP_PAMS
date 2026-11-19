<?php

namespace App\State;

use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\ComplianceActivity;
use App\Entity\Notification;
use App\Entity\User;
use App\Repository\InternalAccreditorAssignmentRepository;
use App\Repository\AreaCoordinatorAssignmentRepository;
use App\Service\NotificationService;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Workflow\WorkflowInterface;
use Doctrine\ORM\EntityManagerInterface;

#[AsDecorator('api_platform.doctrine.orm.state.persist_processor', priority: 20)]
class ComplianceActivityProcessor implements ProcessorInterface
{
    public function __construct(
        private ProcessorInterface $innerProcessor,
        private WorkflowInterface $complianceActivityStateMachine,
        private Security $security,
        private InternalAccreditorAssignmentRepository $assignmentRepository,
        private AreaCoordinatorAssignmentRepository $acAssignmentRepository,
        private NotificationService $notificationService,
        private EntityManagerInterface $entityManager
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if ($data instanceof ComplianceActivity) {
            $this->authorize($data, $operation);

            // Enforce deadline rule
            $cycle = $data->getAreaAssignment()->getCycle();
            $deadline = $cycle->getComplianceDeadline();
            
            if ($deadline) {
                $deadlineComparison = new \DateTimeImmutable($deadline->format('Y-m-d') . ' 23:59:59');
                if ($data->getEndDate() > $deadlineComparison) {
                    throw new UnprocessableEntityHttpException('Activity end date cannot exceed the cycle compliance deadline.');
                }
            }

            $previousState = $data->getState();
            $transition = $data->getTransition();

            // Handle Workflow Transitions
            if ($transition) {
                if ($this->complianceActivityStateMachine->can($data, $transition)) {
                    $this->complianceActivityStateMachine->apply($data, $transition);
                } else {
                    throw new UnprocessableEntityHttpException(sprintf('Cannot apply transition "%s" from state "%s".', $transition, $data->getState()));
                }
            }

            $result = $this->innerProcessor->process($data, $operation, $uriVariables, $context);

            // Fire notifications after persist
            if ($transition) {
                $this->sendActivityNotifications($data, $transition);
                $this->entityManager->flush();
            }

            return $result;
        }

        return $this->innerProcessor->process($data, $operation, $uriVariables, $context);
    }

    private function sendActivityNotifications(ComplianceActivity $activity, string $transition): void
    {
        $assignment = $activity->getAreaAssignment();
        $cycle = $assignment->getCycle();
        $link = '/cycles/' . $cycle->getId();

        if ($transition === 'submit') {
            // Notify assigned Internal Accreditors
            $iaAssignments = $this->assignmentRepository->findBy([
                'program' => $cycle->getProgram(),
            ]);
            $ias = array_map(fn($a) => $a->getInternalAccreditor(), $iaAssignments);
            $this->notificationService->notifyMany(
                $ias,
                Notification::TYPE_ACTIVITY_SUBMITTED,
                sprintf('Activity "%s" has been submitted for review.', $activity->getTitle()),
                $link
            );
        } elseif ($transition === 'request_revision') {
            // Notify Area Coordinators of this area
            $acAssignments = $this->acAssignmentRepository->findBy(['areaAssignment' => $assignment]);
            $acs = array_map(fn($a) => $a->getAreaCoordinator(), $acAssignments);
            $this->notificationService->notifyMany(
                $acs,
                Notification::TYPE_REVISION_REQUESTED,
                sprintf('Revision requested for activity "%s".', $activity->getTitle()),
                $link
            );
        } elseif ($transition === 'approve') {
            // Notify Area Coordinators
            $acAssignments = $this->acAssignmentRepository->findBy(['areaAssignment' => $assignment]);
            $acs = array_map(fn($a) => $a->getAreaCoordinator(), $acAssignments);
            $this->notificationService->notifyMany(
                $acs,
                Notification::TYPE_ACTIVITY_APPROVED,
                sprintf('Activity "%s" has been approved.', $activity->getTitle()),
                $link
            );
        }
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

        if ($this->security->isGranted('ROLE_AREA_COORDINATOR')) {
            $acAssigned = $this->acAssignmentRepository->findOneBy([
                'areaCoordinator' => $user,
                'areaAssignment' => $assignment,
            ]);
            if ($acAssigned === null) {
                throw new AccessDeniedHttpException('You may only manage activities in your assigned areas.');
            }
            if (!$operation instanceof Post && !in_array($activity->getState(), [ComplianceActivity::STATE_DRAFT, ComplianceActivity::STATE_NEEDS_REVISION], true)) {
                throw new AccessDeniedHttpException('Only draft activities or activities needing revision can be edited.');
            }
            if ($transition !== null && $transition !== 'submit') {
                throw new AccessDeniedHttpException('Area Coordinators may only submit activities.');
            }
            return;
        }

        if ($this->security->isGranted('ROLE_INTERNAL_ACCREDITOR')) {
            $assigned = $this->assignmentRepository->findOneBy([
                'internalAccreditor' => $user,
                'program' => $assignment->getCycle()->getProgram(),
            ]);
            if ($assigned === null || $transition === null || !in_array($transition, ['start_review', 'request_revision', 'approve'], true)) {
                throw new AccessDeniedHttpException('You may only review activities assigned to you.');
            }
            return;
        }

        throw new AccessDeniedHttpException('You are not allowed to modify this activity.');
    }
}
