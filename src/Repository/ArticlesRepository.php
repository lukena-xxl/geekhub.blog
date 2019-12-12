<?php

namespace App\Repository;

use App\Entity\Articles;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Articles|null find($id, $lockMode = null, $lockVersion = null)
 * @method Articles|null findOneBy(array $criteria, array $orderBy = null)
 * @method Articles[]    findAll()
 * @method Articles[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArticlesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Articles::class);
    }

    public function findBySort($arguments)
    {
        $qb = $this->createQueryBuilder('a');

        if (!empty($arguments['category'])) {
            $qb->andWhere('a.category = :category')
                ->setParameter('category', $arguments['category']);
        }

        if (!empty($arguments['user'])) {
            $qb->andWhere('a.user = :user')
                ->setParameter('user', $arguments['user']);
        }

        if (!empty($arguments['create_from'])) {
            $qb->andWhere('a.create_date >= :from')
                ->setParameter('from', $arguments['create_from']);
        }

        if (!empty($arguments['create_to'])) {
            $qb->andWhere('a.create_date <= :to')
                ->setParameter('to', $arguments['create_to']);
        }

        if (!empty($arguments['tag'])) {
            $qb->join('a.tag', 't')
                ->andWhere('t.id = :tag')
                ->setParameter('tag', $arguments['tag']);
        }

        $qb->addOrderBy('a.id', 'ASC');
        $query = $qb->getQuery();

        return $query->execute();
    }
}
