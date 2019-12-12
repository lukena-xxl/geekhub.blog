<?php

namespace App\Repository;

use App\Entity\Categories;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Categories|null find($id, $lockMode = null, $lockVersion = null)
 * @method Categories|null findOneBy(array $criteria, array $orderBy = null)
 * @method Categories[]    findAll()
 * @method Categories[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategoriesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Categories::class);
    }

    public function findBySort($arguments)
    {
        $arrSymbol = [
            'more' => '>',
            'less' => '<',
            'equally' => '='
        ];

        $qb = $this->createQueryBuilder('c');

        if (!empty($arguments['num'])) {
            $qb->andWhere('(SELECT COUNT(a.id) FROM App\Entity\Articles a WHERE a.category=c.id) ' . $arrSymbol[$arguments['symbol']] . ' :num')
                ->setParameter('num', $arguments['num']);
        }

        $qb->addOrderBy('c.id', 'ASC');
        $query = $qb->getQuery();

        return $query->execute();
    }
}
