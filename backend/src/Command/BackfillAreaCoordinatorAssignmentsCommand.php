<?php

namespace App\Command;

use App\Entity\AreaCoordinatorAssignment;
use App\Repository\AccreditationCycleRepository;
use App\Repository\AreaCoordinatorAssignmentRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:backfill-area-coordinator-assignments',
    description: 'Assign college Area Coordinators to every existing area assignment.',
)]
class BackfillAreaCoordinatorAssignmentsCommand extends Command
{
    public function __construct(
        private AccreditationCycleRepository $cycleRepository,
        private UserRepository $userRepository,
        private AreaCoordinatorAssignmentRepository $assignmentRepository,
        private EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $created = 0;

        foreach ($this->cycleRepository->findAll() as $cycle) {
            $college = $cycle->getProgram()->getCollege();
            $areaCoordinators = $this->userRepository->findBy([
                'college' => $college,
                'role' => 'ROLE_AREA_COORDINATOR',
            ]);

            foreach ($cycle->getAreaAssignments() as $areaAssignment) {
                foreach ($areaCoordinators as $areaCoordinator) {
                    $existing = $this->assignmentRepository->findOneBy([
                        'areaCoordinator' => $areaCoordinator,
                        'areaAssignment' => $areaAssignment,
                    ]);

                    if ($existing) {
                        continue;
                    }

                    $assignment = new AreaCoordinatorAssignment();
                    $assignment->setAreaCoordinator($areaCoordinator);
                    $assignment->setAreaAssignment($areaAssignment);
                    $this->entityManager->persist($assignment);
                    ++$created;
                }
            }
        }

        $this->entityManager->flush();
        $io->success(sprintf('Created %d missing Area Coordinator assignments.', $created));

        return Command::SUCCESS;
    }
}
