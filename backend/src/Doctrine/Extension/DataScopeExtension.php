<?php

namespace App\Doctrine\Extension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\AccreditationCycle;
use App\Entity\AreaAssignment;
use App\Entity\ComplianceActivity;
use App\Entity\Evidence;
use App\Entity\Program;
use App\Entity\User;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;

class DataScopeExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    public function __construct(private Security $security) {}

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, Operation $operation = null, array $context = []): void
    {
        $this->addWhere($queryBuilder, $resourceClass);
    }

    public function applyToItem(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, array $identifiers, Operation $operation = null, array $context = []): void
    {
        $this->addWhere($queryBuilder, $resourceClass);
    }

    private function addWhere(QueryBuilder $queryBuilder, string $resourceClass): void
    {
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            return;
        }

        // QUAMC Admin, President, Campus Director, VPAA can see everything
        if ($this->security->isGranted('ROLE_QUAMC_ADMIN') || 
            $this->security->isGranted('ROLE_PRESIDENT') || 
            $this->security->isGranted('ROLE_CAMPUS_DIRECTOR') || 
            $this->security->isGranted('ROLE_VPAA')) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];

        // --- PROGRAM HEAD SCOPE ---
        // Program Heads only see data related to their assigned program.
        if ($this->security->isGranted('ROLE_PROGRAM_HEAD') && $user->getProgram()) {
            $programId = $user->getProgram()->getId();

            if (Program::class === $resourceClass) {
                $queryBuilder->andWhere(sprintf('%s.id = :programId', $rootAlias));
                $queryBuilder->setParameter('programId', $programId);
            } elseif (AccreditationCycle::class === $resourceClass) {
                $queryBuilder->andWhere(sprintf('%s.program = :programId', $rootAlias));
                $queryBuilder->setParameter('programId', $programId);
            } elseif (AreaAssignment::class === $resourceClass) {
                $queryBuilder->join(sprintf('%s.cycle', $rootAlias), 'cycle_ph');
                $queryBuilder->andWhere('cycle_ph.program = :programId');
                $queryBuilder->setParameter('programId', $programId);
            } elseif (ComplianceActivity::class === $resourceClass) {
                $queryBuilder->join(sprintf('%s.areaAssignment', $rootAlias), 'aa_ph');
                $queryBuilder->join('aa_ph.cycle', 'cycle_ph2');
                $queryBuilder->andWhere('cycle_ph2.program = :programId');
                $queryBuilder->setParameter('programId', $programId);
            } elseif (Evidence::class === $resourceClass) {
                $queryBuilder->join(sprintf('%s.activity', $rootAlias), 'act_ph');
                $queryBuilder->join('act_ph.areaAssignment', 'aa_ph2');
                $queryBuilder->join('aa_ph2.cycle', 'cycle_ph3');
                $queryBuilder->andWhere('cycle_ph3.program = :programId');
                $queryBuilder->setParameter('programId', $programId);
            }
        }

        // --- DEAN SCOPE ---
        // Deans only see data related to their assigned college.
        if ($this->security->isGranted('ROLE_DEAN') && $user->getCollege()) {
            $collegeId = $user->getCollege()->getId();

            if (Program::class === $resourceClass) {
                $queryBuilder->andWhere(sprintf('%s.college = :collegeId', $rootAlias));
                $queryBuilder->setParameter('collegeId', $collegeId);
            } elseif (AccreditationCycle::class === $resourceClass) {
                $queryBuilder->join(sprintf('%s.program', $rootAlias), 'prog_dean');
                $queryBuilder->andWhere('prog_dean.college = :collegeId');
                $queryBuilder->setParameter('collegeId', $collegeId);
            } elseif (AreaAssignment::class === $resourceClass) {
                $queryBuilder->join(sprintf('%s.cycle', $rootAlias), 'cycle_dean');
                $queryBuilder->join('cycle_dean.program', 'prog_dean2');
                $queryBuilder->andWhere('prog_dean2.college = :collegeId');
                $queryBuilder->setParameter('collegeId', $collegeId);
            } elseif (ComplianceActivity::class === $resourceClass) {
                $queryBuilder->join(sprintf('%s.areaAssignment', $rootAlias), 'aa_dean');
                $queryBuilder->join('aa_dean.cycle', 'cycle_dean2');
                $queryBuilder->join('cycle_dean2.program', 'prog_dean3');
                $queryBuilder->andWhere('prog_dean3.college = :collegeId');
                $queryBuilder->setParameter('collegeId', $collegeId);
            }
        }

        // --- INTERNAL ACCREDITOR SCOPE ---
        // IAs only see their assigned AreaAssignments, and activities within them.
        if ($this->security->isGranted('ROLE_INTERNAL_ACCREDITOR')) {
            if (AreaAssignment::class === $resourceClass) {
                $queryBuilder->join('App\Entity\InternalAccreditorAssignment', 'iaa', 'WITH', sprintf('iaa.areaAssignment = %s.id', $rootAlias));
                $queryBuilder->andWhere('iaa.internalAccreditor = :userId');
                $queryBuilder->setParameter('userId', $user->getId());
            } elseif (ComplianceActivity::class === $resourceClass) {
                $queryBuilder->join('App\Entity\InternalAccreditorAssignment', 'iaa2', 'WITH', sprintf('iaa2.areaAssignment = %s.areaAssignment', $rootAlias));
                $queryBuilder->andWhere('iaa2.internalAccreditor = :userId');
                $queryBuilder->setParameter('userId', $user->getId());
            }
        }
    }
}
