<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Post>
 *
 * @method Post|null find($id, $lockMode = null, $lockVersion = null)
 * @method Post|null findOneBy(array $criteria, array $orderBy = null)
 * @method Post[]    findAll()
 * @method Post[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    public function sortedByDate()
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.created_at', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function getPostsByCategory($categoryId){
            return $this->createQueryBuilder('p')
            ->innerJoin('p.category', 'c') 
            ->where('c.id = :categoryId')
            ->setParameter('categoryId', $categoryId)
            ->orderBy('p.created_at', 'DESC')
            ->getQuery();
    }



   public function paginationQuery()
   {
       return $this->createQueryBuilder('p')
           ->orderBy('p.id', 'ASC')
           ->getQuery();
   }

   public function findArticlesByName(string $query)
   {
       $qb = $this->createQueryBuilder('p');
       $qb
           ->where(
               $qb->expr()->andX(
                   $qb->expr()->orX(
                       $qb->expr()->like('p.title', ':query'),
                       $qb->expr()->like('p.description', ':query'),
                   ),
                   $qb->expr()->isNotNull('p.created_at')
               )
           )
           ->setParameter('query', '%' . $query . '%');
       return $qb
           ->getQuery();
   }


//    public function findOneBySomeField($value): ?Post
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
