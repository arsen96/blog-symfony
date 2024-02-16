<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\Status;
use App\Entity\Users;
use App\Form\CommentResponse;
use App\Form\CommentType;
use App\Form\ProfileType;
use App\Repository\PostRepository;
use DateTimeImmutable;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Twig\TwigFilter;

class MainController extends AbstractController
{
    public function __construct(
        private HttpClientInterface $client,
        private EntityManagerInterface $manager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    #[Route('/', name: 'app_post')]
    public function index(Request $request, EntityManagerInterface $entityManager,PaginatorInterface $paginator): Response
    {
        $category = $entityManager->getRepository(Category::class)->findAll();
        $categoryId = $request->query->get('cat_id');
        $query = $request->query->get('query');
        $paginationRequest = '';
      
        if (!$categoryId) {
            $categoryId = $category[0]->getId();
        }
        $paginationRequest = $query 
        ? $entityManager->getRepository(Post::class)->findArticlesByName($query)
        : $entityManager->getRepository(Post::class)->getPostsByCategory($categoryId);
      
        $pagination = $paginator->paginate(
            $paginationRequest,
            $request->query->getInt('page', 1), 
            10 
        );
        return $this->render('main/post.html.twig', [
            'category' => $category,
            'categoryId' => $categoryId,
            'pagination' => $pagination
        ]);
    }

    #[Route('/search', name: 'app_search')]
    public function search(Request $request)
    {
        $result = array();
        $form =  $this->createFormBuilder($result,['attr' => ['id' => 'formSearch']])
        ->setAction($this->generateUrl('handleSearch'))
        ->add('query', TextType::class, [
            'label' => false,
            'attr' => [
                'class' => 'form-control',
                'placeholder' => 'Entrez un mot-clé'
            ]
        ])
        ->add('recherche', SubmitType::class, [
            'attr' => [
                'class' => 'btn btn-primary'
            ]
        ])
        ->getForm();
        $form->handleRequest($request);
        return $this->render(
            'main/search.html.twig',
            [
                'form' => $form,
            ]
        );
    }

    #[Route('/handleSearch', name: 'handleSearch')]
    public function handleSearch(Request $request, PostRepository $repo)
    {
        $allRequestData = $request->request->all();
        $query = $allRequestData['form']['query'];
        return $this->redirectToRoute('app_post',['query' => $query]);
    }
    

    #[Route('/post_detail', name: 'app_post_detail')]
    public function get_post_detail(Request $request, EntityManagerInterface $entityManager,UrlGeneratorInterface $urlGenerator): Response
    {
        $commentRepository = $entityManager->getRepository(Comment::class);
        $postId = $request->query->get('post_id');
        $commentId = $request->query->get('delete_com_id');
        $postRepository = $entityManager->getRepository(Post::class);
        $post = $postRepository->findOneBy(["id" => $postId]);
        if($commentId){
            $comment = $commentRepository->findOneBy(['id' => $commentId]);
            $entityManager->remove($comment);
            $entityManager->flush(); 
            return $this->redirectToRoute('app_post_detail', ['post_id' => $postId]);
        }

        $errors = [];
        $post_comments = $commentRepository->findCommentsWithUserByPostId($postId,$this->getUser());

        $totalComments = $commentRepository->countNoPublishedComments($this->getUser());
        $statusName = 'published';
        if($this->getUser()){
           $isAdmin = in_array('ROLE_ADMIN',$this->getUser()->getRoles());
           if($isAdmin){
            $statusName = 'published';
           }
        } 
        $comment = new Comment();
        $status = $entityManager->getRepository(Status::class)->findOneBy(['name' => $statusName]);
        $comment->setCreatedAt(DateTimeImmutable::createFromMutable(new \DateTime('now')));
        $comment->setUser($this->getUser());
        $comment->setStatus($status);
        $comment->setPost($post);
        $form = $this->createForm(CommentType::class, $comment);
    
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if($form->isValid()){
                $this->manager->persist($comment);
                $this->manager->flush();
                return $this->redirectToRoute('app_post_detail', ['post_id' => $postId]);
            }else{
                $errors = $form->getErrors();
            }
        }

        $index = 0;
        $formsAnswerTwig = [];

        $postsTotal = array_filter($post_comments,function($eachComm){
            return $eachComm->getParent() == null;
        });

        foreach ($postsTotal as $eachComment){
            $index++;
            $commentResponse = new Comment();
            $status = $entityManager->getRepository(Status::class)->findOneBy(['name' => $statusName]);
            $commentResponse->setCreatedAt(new \DateTimeImmutable('now'));
            $commentResponse->setUser($this->getUser());
            $commentResponse->setStatus($status);
            $commentResponse->setPost($post);

            $formName = 'custom_form_name_' . $index;
            $formAnswer = $this->createForm(CommentResponse::class, $commentResponse, [
                'action' => $this->generateUrl('app_post_detail', ['post_id' => $postId, 'comment_id' => $eachComment->getId()]), // Adjust 'target_route' as necessary
                'method' => 'POST',
            ]);
      
            $formFactory = $this->container->get('form.factory');
            $formAnswer = $formFactory->createNamed($formName, CommentResponse::class);
            $formAnswer->handleRequest($request);
                if ($formAnswer->isSubmitted() && $formAnswer->isValid()) {
                        $allRequestData = $request->request->all();
                        if($allRequestData[$formName]['description']){
                            $commentResponse->setDescription($allRequestData[$formName]['description']);
                            $eachComment->addChild($commentResponse);
                            $this->manager->persist($eachComment);
                            $this->manager->persist($commentResponse);
                            $this->manager->flush();
                            return $this->redirectToRoute('app_post_detail', ['post_id' => $postId]);
                        }
                     
                }
        
            $formsAnswerTwig[$eachComment->getId()] = $formAnswer->createView();
        }

        $urlAdminDashboard = $urlGenerator->generate('admin', [
            'crudAction' => 'index',
            'crudControllerFqcn' => \App\Controller\Admin\CommentCrudController::class,
        ]);
    

        return $this->render('main/post-detail.html.twig', [
            'form' => $form,
            'formAnswer' => $formsAnswerTwig,
            'errors'=> $errors,
            'post' => $post,
            'totalComments'=> $totalComments,
            'urlAdminDashboard' => $urlAdminDashboard,
            'post_comments' => $post_comments
        ]);
    }



    #[Route('/auth', name: 'app_auth')]
    public function user_auth(Request $request, ValidatorInterface $validator, AuthenticationUtils $authenticationUtils): Response
    {
        $userConnected = $this->getUser();
        if ($userConnected) {
            return $this->redirectToRoute('app_post');
        }
        $formI = null;
        $user = new Users();
        $user->setRoles(['ROLE_USER']);
        $errors = [];
        $isLogin = $request->query->get('login');
        if (!$isLogin) {
            $form = $this->createFormBuilder($user)
            ->add('firstname', TextType::class, [
                'attr' => ['class' => 'form-control']
            ])->add('lastname', TextType::class, [
                'attr' => ['class' => 'form-control'],
                'constraints' => new NotBlank(),
            ])->add('email', EmailType::class, [
                'attr' => ['class' => 'form-control'],
                'constraints' => new NotBlank(),
            ])->add('password', PasswordType::class, [
                'attr' => ['class' => 'form-control'],
                'constraints' => new NotBlank(),
            ])->add('submit', SubmitType::class, ['attr' => ['class' => 'btn btn-dark']]);
            $formI = $form->getForm();

            $formI->handleRequest($request);

            if ($formI->isSubmitted() && $formI->isValid()) {
                if (!$isLogin) {
                    $userExist = $this->manager->getRepository(Users::class)->findOneBy(['email' => $user->getEmail()]);
                    $all = $this->manager->getRepository(Users::class)->findAll();
                    // dump($all);
                    // dd($userExist);
                    if ($userExist) {
                        $errors[] = ["message" => "Un compte avec cette adresse email existe déjà"];
                    } else {
                        $user->setPassword($user->hashPassword($this->passwordHasher));
                        $this->manager->persist($user);
                        $this->manager->flush();
                        return $this->redirectToRoute('app_auth', ['login' => 'true']);
                    }
                }
            } else if ($formI->isSubmitted() && !$formI->isValid()) {
                $errors = $validator->validate($user);
                // dd($errors);
            }
        }
        //  dd($form->get('firstname'));   
        return $this->render('main/auth.html.twig', [
            'form' => $formI,
            'errors' => $errors,
            'isLogin' => $isLogin
        ]);
    }

    #[Route('/profil', name: 'app_profil')]
    public function profil_utilisateur(Request $request, EntityManagerInterface $entityManager,UrlGeneratorInterface $urlGenerator): Response
    {
        $isUpdated = $request->query->get('updated');
        $selectedMenu = $request->query->get('selected_menu');
        $autorizedParams = ['password','comments'];
        if(!$selectedMenu || !in_array($selectedMenu,$autorizedParams)){
            $selectedMenu = 'password';
        }
        $successPasswordMessage = '';
        if($isUpdated){
            $successPasswordMessage = 'Votre mot de passe a bien été mis à jour';
        }
        $currentUser = (object)$this->getUser();
        $conn = $entityManager->getConnection();
        $sql = '
            SELECT p.id as postId,c.user_id as user,c.description as comment, p.title as post FROM comment c
            JOIN post p ON p.id = c.post_id
            WHERE c.user_id = :userId
            GROUP BY user,postId
            ';
        $stmt = $conn->prepare($sql);
        $stmt->executeStatement(['userId' => $currentUser->getId()]);
        $result = $stmt->executeQuery();
        $commentsPerArticle = $result->fetchAllAssociative();
        $commentsPerArticleWithLink = [];

        function cut($text,$limit):string{
            $numberCaracterComment = strlen($text);
            if($numberCaracterComment >  $limit){
                $text = substr($text,0, $limit). '...';
            }
            return $text;
        }
        foreach($commentsPerArticle as $commentPost){
            $post = $entityManager->getRepository(Post::class)->findOneBy(["id" => $commentPost['postId']]);
            $commentPost['comment'] = cut($commentPost['comment'],60);
            $commentPost['post'] = cut($commentPost['post'],40);
            $urlAdminDashboard = $urlGenerator->generate('app_post_detail', [
                'post_id' => $post->getId(),
            ]);
            $commentPost['link'] = $urlAdminDashboard;
            $commentsPerArticleWithLink[] = $commentPost;
        }

        $user = new Users();
        $form = $this->createForm(ProfileType::class, $user);
    
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if($form->isValid()){
                $currentUser = $entityManager->getRepository(Users::class)->findOneBy(["id" => $currentUser->getId()]);
                $currentUser->setPassword($user->getPassword());
                $currentUser->setPassword($currentUser->hashPassword($this->passwordHasher));
                $this->manager->persist($currentUser);
                $this->manager->flush();
                return $this->redirectToRoute('app_profil', ['updated' => '1']);
            }else{
                $errors = $form->getErrors();
                // dd($errors);
            }
        }

        return $this->render('main/profil.html.twig', [
            'commentPerArticle' => $commentsPerArticleWithLink,
            'form' => $form,
            'successPasswordMessage'=>$successPasswordMessage,
            'selectedMenu' => $selectedMenu
        ]);
    }
}
