<?php

namespace App\Repository;

use App\Entity\Statuses;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Statuses|null find($id, $lockMode = null, $lockVersion = null)
 * @method Statuses|null findOneBy(array $criteria, array $orderBy = null)
 * @method Statuses[]    findAll()
 * @method Statuses[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StatusesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Statuses::class);
    }
}
