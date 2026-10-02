<?php

namespace App\Controller;

use App\Entity\AccreditationCycle;
use App\Entity\ComplianceActivity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/gantt/{id}', name: 'api_gantt', methods: ['GET'])]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
class GanttController extends AbstractController
{
    public function __invoke(AccreditationCycle $cycle): JsonResponse
    {
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
}
