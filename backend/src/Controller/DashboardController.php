<?php

namespace App\Controller;

use App\Repository\AccreditationCycleRepository;
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
    ) {}

    public function __invoke(): JsonResponse
    {
        $user = $this->getUser();
        $cycles = $this->cycleRepository->findAll();
        
        // Basic filtering based on role for dashboard view
        if ($this->isGranted('ROLE_PROGRAM_HEAD') && $user->getProgram()) {
            $cycles = array_filter($cycles, fn($c) => $c->getProgram() === $user->getProgram());
        } elseif ($this->isGranted('ROLE_DEAN') && $user->getCollege()) {
            $cycles = array_filter($cycles, fn($c) => $c->getProgram()->getCollege() === $user->getCollege());
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
