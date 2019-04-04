<?php

namespace App\Repository;

use App\Entity\URLPacks;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method URLPacks|null find($id, $lockMode = null, $lockVersion = null)
 * @method URLPacks|null findOneBy(array $criteria, array $orderBy = null)
 * @method URLPacks[]    findAll()
 * @method URLPacks[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class URLPacksRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, URLPacks::class);
    }

    // /**
    //  * @return URLPacks[] Returns an array of URLPacks objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?URLPacks
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
