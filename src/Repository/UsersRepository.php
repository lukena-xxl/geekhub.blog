<?php

namespace App\Repository;

use App\Entity\Users;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method Users|null find($id, $lockMode = null, $lockVersion = null)
 * @method Users|null findOneBy(array $criteria, array $orderBy = null)
 * @method Users[]    findAll()
 * @method Users[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UsersRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Users::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     * @param UserInterface $user
     * @param string $newEncodedPassword
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function upgradePassword(UserInterface $user, string $newEncodedPassword): void
    {
        if (!$user instanceof Users) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newEncodedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
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

            $qb->andWhere('(SELECT COUNT(a1.id) FROM App\Entity\Articles a1 WHERE a1.user=u.id AND a1.category=a.category) ' . $arrSymbol[$arguments['symbol']] . ' :num');
        } else {
            $qb->andWhere('(SELECT COUNT(a.id) FROM App\Entity\Articles a WHERE a.user=u.id) ' . $arrSymbol[$arguments['symbol']] . ' :num');
        }

        $qb->setParameter('num', $arguments['num']);

        $qb->addOrderBy('u.id', 'ASC');
        $query = $qb->getQuery();

        return $query->execute();
    }

    public function resetTargetUser()
    {
        $qb = $this->createQueryBuilder('u');
        $qb->update()
            ->set('u.target', ':target')
            ->setParameter('target', 0);

        $query = $qb->getQuery();

        return $query->execute();
    }
}
