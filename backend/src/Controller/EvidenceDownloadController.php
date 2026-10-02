<?php

namespace App\Controller;

use App\Entity\AccreditationCycle;
use App\Entity\Evidence;
use App\Entity\User;
use App\Repository\InternalAccreditorAssignmentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/evidence/{id}/download', methods: ['GET'])]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
class EvidenceDownloadController extends AbstractController
{
    public function __construct(private InternalAccreditorAssignmentRepository $assignmentRepository) {}

    public function __invoke(Evidence $evidence): BinaryFileResponse
    {
        $user = $this->getUser();
        $cycle = $evidence->getActivity()?->getAreaAssignment()?->getCycle();
        if (!$user instanceof User || !$cycle instanceof AccreditationCycle || !$this->canViewCycle($user, $cycle)) {
            throw new AccessDeniedHttpException('You are not allowed to download this evidence.');
        }

        $fileName = $evidence->getFileName();
        $path = $this->getParameter('kernel.project_dir') . '/var/uploads/evidence/' . basename((string) $fileName);
        if (!$fileName || !is_file($path)) {
            throw new NotFoundHttpException('Evidence file not found.');
        }

        $response = new BinaryFileResponse($path);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $evidence->getOriginalName() ?: $fileName
        );
        return $response;
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