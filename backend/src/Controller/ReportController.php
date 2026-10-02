<?php

namespace App\Controller;

use App\Entity\AccreditationCycle;
use App\Entity\MonitoringReport;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Snappy\Pdf;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/report/{id}', name: 'api_report', methods: ['GET'])]
#[IsGranted('ROLE_QUAMC_ADMIN')]
class ReportController extends AbstractController
{
    public function __construct(
        private Pdf $pdf,
        private EntityManagerInterface $entityManager
    ) {}

    public function __invoke(AccreditationCycle $cycle): Response
    {
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

        // 3. Generate PDF
        $pdfContent = $this->pdf->getOutputFromHtml($html);

        return new Response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => sprintf('attachment; filename="AACCUP_Report_%s.pdf"', $cycle->getProgram()->getCode())
        ]);
    }
}
