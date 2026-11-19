<?php

namespace App\State;

use App\Entity\Evidence;
use App\Entity\User;
use App\Repository\AreaCoordinatorAssignmentRepository;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Bundle\SecurityBundle\Security;

#[AsDecorator('api_platform.doctrine.orm.state.persist_processor', priority: 40)]
class EvidenceProcessor implements ProcessorInterface
{
    public function __construct(
        private ProcessorInterface $innerProcessor,
        private Security $security,
        private AreaCoordinatorAssignmentRepository $assignmentRepository,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if ($data instanceof Evidence) {
            $user = $this->security->getUser();
            if (!$user instanceof User) {
                throw new AccessDeniedHttpException('A valid authenticated user is required.');
            }

            $areaAssignment = $data->getAreaAssignment()
                ?? $data->getActivity()?->getAreaAssignment();

            if (!$areaAssignment) {
                throw new AccessDeniedHttpException('Evidence must belong to an activity or an area assignment.');
            }

            if ($this->security->isGranted('ROLE_AREA_COORDINATOR')
                && !$this->security->isGranted('ROLE_QUAMC_ADMIN')) {
                $assigned = $this->assignmentRepository->findOneBy([
                    'areaCoordinator' => $user,
                    'areaAssignment' => $areaAssignment,
                ]);
                if (!$assigned) {
                    throw new AccessDeniedHttpException('You may only upload evidence to assigned areas.');
                }
            }

            if ($this->security->isGranted('ROLE_PROGRAM_HEAD')
                && !$this->security->isGranted('ROLE_QUAMC_ADMIN')) {
                $cycleProgram = $areaAssignment->getCycle()->getProgram();
                if ($user->getProgram() !== $cycleProgram) {
                    throw new AccessDeniedHttpException('You may only upload evidence to your assigned program.');
                }
            }
        }

        return $this->innerProcessor->process($data, $operation, $uriVariables, $context);
    }
}
