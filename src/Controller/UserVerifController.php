<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserVerifController extends AbstractController
{
    #[Route('/user/verif', name: 'app_user_verif')]
    public function index(): Response
    {
        return $this->render('user_verif/index.html.twig', [
            'controller_name' => 'UserVerifController',
        ]);
    }
}
