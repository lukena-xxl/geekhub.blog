<?php

namespace App\Repository;

use App\Entity\Tags;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr\Join;

/**
 * @method Tags|null find($id, $lockMode = null, $lockVersion = null)
 * @method Tags|null findOneBy(array $criteria, array $orderBy = null)
 * @method Tags[]    findAll()
 * @method Tags[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TagsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tags::class);
    }

    public function findBySort($arguments)
    {
        $qb = $this->createQueryBuilder('t');

        if (!empty($arguments['article'])) {
            $qb->innerJoin('t.articles', 'a', 'WITH', 'a.id = :article')
                ->setParameter('article', $arguments['article']);
        }

        $qb->addOrderBy('t.id', 'ASC');
        $query = $qb->getQuery();

        return $query->execute();
    }
}
