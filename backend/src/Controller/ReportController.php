<?php

namespace App\Controller;

use App\Entity\AccreditationCycle;
use App\Entity\MonitoringReport;
use App\Entity\User;
use App\Repository\InternalAccreditorAssignmentRepository;
use Dompdf\Dompdf;
use Dompdf\Options;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

#[Route('/api/report/{id}', name: 'api_report', methods: ['GET'])]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
class ReportController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private InternalAccreditorAssignmentRepository $assignmentRepository
    ) {}

    public function __invoke(AccreditationCycle $cycle): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User || !$this->canViewCycle($user, $cycle)) {
            throw new AccessDeniedHttpException('You are not allowed to view this cycle report.');
        }

        // 1. Log the report generation
        $report = new MonitoringReport();
        $report->setCycle($cycle);
        $report->setGeneratedBy($this->getUser());
        $report->setReportType('pdf');
        
        $this->entityManager->persist($report);
        $this->entityManager->flush();

        // 2. Render Twig template to HTML
        $html = $this->renderView('report/cycle_report.html.twig', [
            'cycle' => $cycle,
            'generatedAt' => clone $report->getGeneratedAt(),
            'generatedBy' => $this->getUser(),
        ]);

        // 3. Generate PDF without an external binary dependency.
        $options = new Options();
        $options->setDefaultFont('DejaVu Sans');
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $pdfContent = $dompdf->output();

        return new Response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => sprintf('attachment; filename="AACCUP_Report_%s.pdf"', $cycle->getProgram()->getCode())
        ]);
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
