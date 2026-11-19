<?php
namespace App\Repository;

use App\Entity\AreaCoordinatorAssignment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AreaCoordinatorAssignmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AreaCoordinatorAssignment::class);
    }
}
