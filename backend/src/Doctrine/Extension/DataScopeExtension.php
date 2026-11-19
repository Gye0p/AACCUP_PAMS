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
use App\Entity\MonitoringReport;
use App\Entity\Notification;
use App\Entity\Program;
use App\Entity\User;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;

class DataScopeExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    public function __construct(private Security $security) {}

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        $this->addWhere($queryBuilder, $resourceClass);
    }

    public function applyToItem(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, array $identifiers, ?Operation $operation = null, array $context = []): void
    {
        $this->addWhere($queryBuilder, $resourceClass);
    }

    private function addWhere(QueryBuilder $queryBuilder, string $resourceClass): void
    {
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            return;
        }

        // Notifications are ALWAYS scoped to the logged-in recipient, regardless of role.
        if (Notification::class === $resourceClass) {
            $rootAlias = $queryBuilder->getRootAliases()[0];
            $queryBuilder->andWhere(sprintf('%s.recipient = :currentUserId', $rootAlias));
            $queryBuilder->setParameter('currentUserId', $user->getId());
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
            } elseif (MonitoringReport::class === $resourceClass) {
                $queryBuilder->join(sprintf('%s.cycle', $rootAlias), 'cycle_report_ph');
                $queryBuilder->andWhere('cycle_report_ph.program = :programId');
                $queryBuilder->setParameter('programId', $programId);
            }
        }

        // --- AREA COORDINATOR SCOPE ---
        // Area Coordinators see only the AreaAssignments (and activities) they are assigned to.
        if ($this->security->isGranted('ROLE_AREA_COORDINATOR')) {
            if (AreaAssignment::class === $resourceClass) {
                $queryBuilder->join('App\Entity\AreaCoordinatorAssignment', 'aca', 'WITH', sprintf('aca.areaAssignment = %s.id', $rootAlias));
                $queryBuilder->andWhere('aca.areaCoordinator = :userId');
                $queryBuilder->setParameter('userId', $user->getId());
            } elseif (ComplianceActivity::class === $resourceClass) {
                $queryBuilder->join('App\Entity\AreaCoordinatorAssignment', 'aca2', 'WITH', sprintf('aca2.areaAssignment = %s.areaAssignment', $rootAlias));
                $queryBuilder->andWhere('aca2.areaCoordinator = :userId');
                $queryBuilder->setParameter('userId', $user->getId());
            } elseif (Evidence::class === $resourceClass) {
                $queryBuilder->leftJoin(sprintf('%s.activity', $rootAlias), 'ev_activity_ac');
                $queryBuilder->leftJoin('ev_activity_ac.areaAssignment', 'ev_activity_aa_ac');
                $queryBuilder->leftJoin(sprintf('%s.areaAssignment', $rootAlias), 'ev_area_aa_ac');
                $queryBuilder->join(
                    'App\Entity\AreaCoordinatorAssignment',
                    'ev_aca',
                    'WITH',
                    '(ev_aca.areaAssignment = ev_activity_aa_ac.id OR ev_aca.areaAssignment = ev_area_aa_ac.id)'
                );
                $queryBuilder->andWhere('ev_aca.areaCoordinator = :userId');
                $queryBuilder->setParameter('userId', $user->getId());
            } elseif (AccreditationCycle::class === $resourceClass) {
                $queryBuilder->join(sprintf('%s.areaAssignments', $rootAlias), 'aa_ac');
                $queryBuilder->join('App\Entity\AreaCoordinatorAssignment', 'aca3', 'WITH', 'aca3.areaAssignment = aa_ac.id');
                $queryBuilder->andWhere('aca3.areaCoordinator = :userId');
                $queryBuilder->setParameter('userId', $user->getId());
            }
        }

        // --- DEAN SCOPE ---
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
            } elseif (MonitoringReport::class === $resourceClass) {
                $queryBuilder->join(sprintf('%s.cycle', $rootAlias), 'cycle_report_dean');
                $queryBuilder->join('cycle_report_dean.program', 'prog_report_dean');
                $queryBuilder->andWhere('prog_report_dean.college = :collegeId');
                $queryBuilder->setParameter('collegeId', $collegeId);
            }
        }

        // --- INTERNAL ACCREDITOR SCOPE ---
        if ($this->security->isGranted('ROLE_INTERNAL_ACCREDITOR')) {
            if (AccreditationCycle::class === $resourceClass) {
                $queryBuilder->join(
                    'App\Entity\InternalAccreditorAssignment',
                    'iaa_cycle',
                    'WITH',
                    'iaa_cycle.program = ' . $rootAlias . '.program'
                );
                $queryBuilder->andWhere('iaa_cycle.internalAccreditor = :userId');
                $queryBuilder->setParameter('userId', $user->getId());
            } elseif (AreaAssignment::class === $resourceClass) {
                $queryBuilder->join(sprintf('%s.cycle', $rootAlias), 'ia_cycle');
                $queryBuilder->join('ia_cycle.program', 'ia_program');
                $queryBuilder->join('App\Entity\InternalAccreditorAssignment', 'iaa', 'WITH', 'iaa.program = ia_program');
                $queryBuilder->andWhere('iaa.internalAccreditor = :userId');
                $queryBuilder->setParameter('userId', $user->getId());
            } elseif (ComplianceActivity::class === $resourceClass) {
                $queryBuilder->join(sprintf('%s.areaAssignment', $rootAlias), 'ia_aa');
                $queryBuilder->join('ia_aa.cycle', 'ia_cycle2');
                $queryBuilder->join('ia_cycle2.program', 'ia_program2');
                $queryBuilder->join('App\Entity\InternalAccreditorAssignment', 'iaa2', 'WITH', 'iaa2.program = ia_program2');
                $queryBuilder->andWhere('iaa2.internalAccreditor = :userId');
                $queryBuilder->setParameter('userId', $user->getId());
            } elseif (MonitoringReport::class === $resourceClass) {
                $queryBuilder->join(sprintf('%s.cycle', $rootAlias), 'cycle_report_ia');
                $queryBuilder->join('cycle_report_ia.program', 'ia_report_program');
                $queryBuilder->join('App\\Entity\\InternalAccreditorAssignment', 'iaa3', 'WITH', 'iaa3.program = ia_report_program');
                $queryBuilder->andWhere('iaa3.internalAccreditor = :userId');
                $queryBuilder->setParameter('userId', $user->getId());
            }
        }
    }
}
