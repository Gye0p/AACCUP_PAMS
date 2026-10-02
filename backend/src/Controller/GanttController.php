<?php

namespace App\Controller;

use App\Entity\AccreditationCycle;
use App\Entity\ComplianceActivity;
use App\Entity\User;
use App\Repository\InternalAccreditorAssignmentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

#[Route('/api/gantt/{id}', name: 'api_gantt', methods: ['GET'])]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
class GanttController extends AbstractController
{
    public function __construct(private InternalAccreditorAssignmentRepository $assignmentRepository) {}

    public function __invoke(AccreditationCycle $cycle): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User || !$this->canViewCycle($user, $cycle)) {
            throw new AccessDeniedHttpException('You are not allowed to view this cycle.');
        }

        $ganttData = [];

        foreach ($cycle->getAreaAssignments() as $assignment) {
            foreach ($assignment->getComplianceActivities() as $activity) {
                // Only return approved or completed activities for the final Gantt chart
                if (in_array($activity->getState(), [ComplianceActivity::STATE_APPROVED, ComplianceActivity::STATE_COMPLETED])) {
                    $ganttData[] = [
                        'id' => 'Task ' . $activity->getId(),
                        'name' => sprintf('[Area %s] %s', $assignment->getArea()->getAreaNumber(), $activity->getTitle()),
                        'start' => $activity->getStartDate()->format('Y-m-d'),
                        'end' => $activity->getEndDate()->format('Y-m-d'),
                        'progress' => $activity->getState() === ComplianceActivity::STATE_COMPLETED ? 100 : 80,
                        'dependencies' => '',
                        'custom_class' => 'bar-milestone'
                    ];
                }
            }
        }

        return $this->json($ganttData);
    }

    private function canViewCycle(User $user, AccreditationCycle $cycle): bool
    {
        if ($this->isGranted('ROLE_QUAMC_ADMIN') || $this->isGranted('ROLE_PRESIDENT')
            || $this->isGranted('ROLE_CAMPUS_DIRECTOR') || $this->isGranted('ROLE_VPAA')) {
            return true;
        }

        if ($this->isGranted('ROLE_PROGRAM_HEAD')) {
            return $user->getProgram() !== null && $cycle->getProgram() === $user->getProgram();
        }

        if ($this->isGranted('ROLE_DEAN')) {
            return $user->getCollege() !== null && $cycle->getProgram()->getCollege() === $user->getCollege();
        }

        if ($this->isGranted('ROLE_INTERNAL_ACCREDITOR')) {
            foreach ($cycle->getAreaAssignments() as $assignment) {
                if ($this->assignmentRepository->findOneBy([
                    'internalAccreditor' => $user,
                    'areaAssignment' => $assignment,
                ])) {
                    return true;
                }
            }
        }

        return false;
    }
}
