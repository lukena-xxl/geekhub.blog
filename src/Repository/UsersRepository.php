<?php

namespace App\Repository;

use App\Entity\Users;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr\Join;

/**
 * @method Users|null find($id, $lockMode = null, $lockVersion = null)
 * @method Users|null findOneBy(array $criteria, array $orderBy = null)
 * @method Users[]    findAll()
 * @method Users[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UsersRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Users::class);
    }

    public function findBySort($arguments)
    {
        $arrSymbol = [
            'more' => '>',
            'less' => '<',
            'equally' => '='
        ];

        $qb = $this->createQueryBuilder('u');

        if (!empty($arguments['category'])) {
            $qb->join('u.articles', 'a')
                ->andWhere('a.category = :category')
                ->setParameter('category', $arguments['category']);
        }

        if (!empty($arguments['num'])) {
            if (!empty($arguments['category'])) {
                $qb->andWhere('(SELECT COUNT(a1.id) FROM App\Entity\Articles a1 WHERE a1.user=u.id AND a1.category=a.category) ' . $arrSymbol[$arguments['symbol']] . ' :num');
            } else {
                $qb->andWhere('(SELECT COUNT(a.id) FROM App\Entity\Articles a WHERE a.user=u.id) ' . $arrSymbol[$arguments['symbol']] . ' :num');
            }

            $qb->setParameter('num', $arguments['num']);
        }

        $qb->addOrderBy('u.login', 'ASC');
        $query = $qb->getQuery();

        return $query->execute();
    }
}
