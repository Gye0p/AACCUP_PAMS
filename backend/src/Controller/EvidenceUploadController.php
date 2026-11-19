<?php

namespace App\Controller;

use App\Entity\Evidence;
use App\Entity\User;
use App\Repository\AreaCoordinatorAssignmentRepository;
use App\Repository\AreaAssignmentRepository;
use App\Repository\ComplianceActivityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/evidence/upload', name: 'api_evidence_upload', methods: ['POST'])]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
class EvidenceUploadController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ComplianceActivityRepository $activityRepository,
        private AreaAssignmentRepository $areaAssignmentRepository,
        private AreaCoordinatorAssignmentRepository $coordinatorAssignmentRepository,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw new AccessDeniedHttpException('A valid authenticated user is required.');
        }

        /** @var UploadedFile|null $file */
        $file = $request->files->get('file');
        if (!$file instanceof UploadedFile) {
            throw new BadRequestHttpException('A file is required.');
        }

        if (!$file->isValid()) {
            throw new BadRequestHttpException('The uploaded file is invalid.');
        }

        if ($file->getSize() > 20 * 1024 * 1024) {
            throw new BadRequestHttpException('The file exceeds the 20 MB limit.');
        }

        $allowedTypes = [
            'application/pdf',
            'image/jpeg',
            'image/png',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        ];
        if (!in_array($file->getMimeType(), $allowedTypes, true)) {
            throw new BadRequestHttpException('Only PDF, JPEG, PNG, Word, Excel, and PowerPoint files are allowed.');
        }

        $activityId = $request->request->get('activityId');
        $areaAssignmentId = $request->request->get('areaAssignmentId');
        $areaAssignment = null;

        $evidence = new Evidence();
        if ($activityId) {
            $activity = $this->activityRepository->find((int) $activityId);
            if (!$activity) {
                throw new NotFoundHttpException('Compliance activity not found.');
            }
            $areaAssignment = $activity->getAreaAssignment();
            $evidence->setActivity($activity);
        } elseif ($areaAssignmentId) {
            $areaAssignment = $this->areaAssignmentRepository->find((int) $areaAssignmentId);
            if (!$areaAssignment) {
                throw new NotFoundHttpException('Area assignment not found.');
            }
            $evidence->setAreaAssignment($areaAssignment);
        } else {
            throw new BadRequestHttpException('An activity or area assignment is required.');
        }

        if ($this->isGranted('ROLE_AREA_COORDINATOR') && !$this->isGranted('ROLE_QUAMC_ADMIN')) {
            $assigned = $this->coordinatorAssignmentRepository->findOneBy([
                'areaCoordinator' => $user,
                'areaAssignment' => $areaAssignment,
            ]);
            if (!$assigned) {
                throw new AccessDeniedHttpException('You may only upload evidence to assigned areas.');
            }
        } elseif ($this->isGranted('ROLE_PROGRAM_HEAD') && !$this->isGranted('ROLE_QUAMC_ADMIN')) {
            if ($user->getProgram() !== $areaAssignment->getCycle()->getProgram()) {
                throw new AccessDeniedHttpException('You may only upload evidence to your assigned program.');
            }
        } elseif (!$this->isGranted('ROLE_QUAMC_ADMIN')) {
            throw new AccessDeniedHttpException('You are not allowed to upload evidence.');
        }

        $evidence->setFile($file);
        $evidence->setOriginalName((string) ($request->request->get('originalName') ?: $file->getClientOriginalName()));
        $evidence->setUploadedBy($user);

        $this->entityManager->persist($evidence);
        $this->entityManager->flush();

        return $this->json([
            'id' => $evidence->getId(),
            'fileName' => $evidence->getFileName(),
            'originalName' => $evidence->getOriginalName(),
        ], 201);
    }
}
