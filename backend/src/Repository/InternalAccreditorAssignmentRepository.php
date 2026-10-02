<?php
namespace App\Repository;
use App\Entity\InternalAccreditorAssignment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
class InternalAccreditorAssignmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry) { parent::__construct($registry, InternalAccreditorAssignment::class); }
}
