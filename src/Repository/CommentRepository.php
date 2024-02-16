<?php

namespace App\Repository;

use App\Entity\Comment;
use App\Entity\Users;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * @extends ServiceEntityRepository<Comment>
 *
 * @method Comment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Comment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Comment[]    findAll()
 * @method Comment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommentRepository extends ServiceEntityRepository
{
    private $entityManager;
    public function __construct(ManagerRegistry $registry,EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, Comment::class);
        $this->entityManager = $entityManager;
    }

    public function findCommentsWithUserByPostId($postId,?Users $user)
    {
        if($this->isAdmin($user)){
            return $this->createQueryBuilder('c')
            ->innerJoin('c.user', 'u')
            ->where('c.post = :postId')
            ->setParameter('postId', $postId)
            ->orderBy('c.created_at', 'DESC')
            ->getQuery()
            ->getResult();
        }else{
            return $this->createQueryBuilder('c')
            ->innerJoin('c.user', 'u')
            ->innerJoin('c.status', 's')
            ->where('c.post = :postId AND s.name = :statusName')
            ->setParameter('postId', $postId)
            ->setParameter('statusName', 'published')
            ->orderBy('c.created_at', 'DESC')
            ->getQuery()
            ->getResult();
        }
    }

    public function countNoPublishedComments($user){
        $totalComments = 0;
        if($user){
            $query = $this->getEntityManager()->createQuery(
                "SELECT COUNT(c.id) AS totalComment, u.id 
                FROM App\Entity\Comment c 
                JOIN c.user u 
                JOIN c.status s
                WHERE s.name = 'unpublished'
                GROUP BY u.id");
                $entities = $query->getResult();
                foreach($entities as $numberCommentsByUser){
                        $numberCommentsByUser = (object)$numberCommentsByUser;
                        if($this->isAdmin($user)){
                            $totalComments += $numberCommentsByUser->totalComment;
                        }else{
                            if($numberCommentsByUser->id == $user->getId()){
                                $totalComments =  $numberCommentsByUser->totalComment;
                                break;
                             }
                        }
                       
                }
        }
        return $totalComments;
    }

    public function isAdmin($user){
        if($user){
            return in_array('ROLE_ADMIN',$user->getRoles());
        }
        return false;
    }

//    /**
//     * @return Comment[] Returns an array of Comment objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Comment
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
