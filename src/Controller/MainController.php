<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Post;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{    
   
    #[Route('/', name: 'app_main')]
    public function index(): Response
    {
        return $this->render('main/index.html.twig', [
            'controller_name' => 'Main',
        ]);
    }

    #[Route('/post', name: 'app_post')]
    public function get_posts(Request $request,EntityManagerInterface $entityManager): Response
    {
       
        $postRepository = $entityManager->getRepository(Post::class);
        $category = $entityManager->getRepository(Category::class)->findAll();
        $categoryId = $request->query->get('cat_id');

        if(!$categoryId){
            $categoryId = $category[0]->getId();
        }

        $posts = $postRepository->createQueryBuilder('p')
        ->innerJoin('p.category', 'c') 
        ->where('c.id = :categoryId')
        ->setParameter('categoryId', $categoryId)
        ->getQuery()
        ->getResult();

        return $this->render('main/post.html.twig', [
            'posts' => $posts,
            'category' => $category,
            'categoryId' => $categoryId
        ]);
    }


    #[Route('/post_detail', name: 'app_post_detail')]
    public function get_post_detail(Request $request,EntityManagerInterface $entityManager): Response
    {
        $postRepository = $entityManager->getRepository(Post::class);
        $postId = $request->query->get('post_id');
        $post = $postRepository->findOneBy(["id" => $postId]);
        return $this->render('main/post-detail.html.twig', [
            'post' => $post
        ]);
    }


    

}
