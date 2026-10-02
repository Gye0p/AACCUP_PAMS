<?php

namespace App\Controller;

use App\Repository\AccreditationCycleRepository;
use App\Repository\InternalAccreditorAssignmentRepository;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/dashboard', name: 'api_dashboard', methods: ['GET'])]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
class DashboardController extends AbstractController
{
    public function __construct(
        private AccreditationCycleRepository $cycleRepository
        , private InternalAccreditorAssignmentRepository $assignmentRepository
    ) {}

    public function __invoke(): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->json([]);
        }

        $cycles = $this->cycleRepository->findAll();
        
        $canViewAll = $this->isGranted('ROLE_QUAMC_ADMIN')
            || $this->isGranted('ROLE_PRESIDENT')
            || $this->isGranted('ROLE_CAMPUS_DIRECTOR')
            || $this->isGranted('ROLE_VPAA');

        if (!$canViewAll && $this->isGranted('ROLE_PROGRAM_HEAD') && $user->getProgram()) {
            $cycles = array_filter($cycles, fn($c) => $c->getProgram() === $user->getProgram());
        } elseif (!$canViewAll && $this->isGranted('ROLE_DEAN') && $user->getCollege()) {
            $cycles = array_filter($cycles, fn($c) => $c->getProgram()->getCollege() === $user->getCollege());
        } elseif (!$canViewAll && $this->isGranted('ROLE_INTERNAL_ACCREDITOR')) {
            $cycles = array_filter($cycles, function ($cycle) use ($user): bool {
                foreach ($cycle->getAreaAssignments() as $assignment) {
                    if ($this->assignmentRepository->findOneBy([
                        'internalAccreditor' => $user,
                        'areaAssignment' => $assignment,
                    ])) {
                        return true;
                    }
                }
                return false;
            });
        } elseif (!$canViewAll) {
            $cycles = [];
        }

        $dashboardData = [];
        $now = new \DateTime();

        foreach ($cycles as $cycle) {
            $deadline = $cycle->getComplianceDeadline();
            $daysRemaining = $deadline ? $now->diff($deadline)->days : 0;
            $isPast = $deadline && $deadline < $now;
            
            if ($isPast) {
                $daysRemaining = -$daysRemaining;
            }

            // Calculate urgency
            $urgency = 'green';
            if ($daysRemaining < 0 || $daysRemaining < 8) {
                $urgency = 'red';
            } elseif ($daysRemaining <= 30) {
                $urgency = 'amber';
            }

            // Check if validity is expired (Overdue for Renewal)
            $validityEnd = $cycle->getProgram()->getValidityEnd();
            $isOverdue = $validityEnd && $validityEnd < $now;

            $dashboardData[] = [
                'cycleId' => $cycle->getId(),
                'programCode' => $cycle->getProgram()->getCode(),
                'programName' => $cycle->getProgram()->getName(),
                'collegeCode' => $cycle->getProgram()->getCollege()->getCode(),
                'academicYear' => $cycle->getAcademicYear(),
                'deadline' => $deadline?->format('Y-m-d'),
                'daysRemaining' => $daysRemaining,
                'urgency' => $urgency,
                'isOverdueForRenewal' => $isOverdue,
                'status' => $cycle->getStatus()
            ];
        }

        return $this->json($dashboardData);
    }
}
