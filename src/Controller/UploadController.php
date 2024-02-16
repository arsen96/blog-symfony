<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Form\ProductType;
use App\Service\MyService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class UploadController extends AbstractController
{
    #[Route('/upload', name: 'app_upload')]
    public function index(MyService $myService,Request $request,EntityManagerInterface $entityManager,SluggerInterface $slugger): Response
    {

        $produit = new Produit();
        $form = $this->createForm(ProductType::class,$produit);

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if($form->isValid()){
                 /** @var UploadedFile $brochureFile */
            $brochureFile = $form->get('image')->getData();

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($brochureFile) {
                $originalFilename = pathinfo($brochureFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$brochureFile->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $brochureFile->move(
                        $this->getParameter('image_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $produit->setImage($newFilename);
            }

            // ... persist the $product variable or any other work

            return $this->redirectToRoute('app_upload');
            }
        }
        $message = $myService->hello();
        return $this->render('upload/index.html.twig', [
            'controller_name' => $message,
            'form'=>$form
        ]);
    }
}
