<?php

namespace App\Controller\Admin;

use App\Entity\Comment;
use App\Entity\Status;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\RedirectResponse;

class CommentCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Comment::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('description')->hideOnForm(),
            TextEditorField::new('description')->hideOnIndex(),
            AssociationField::new('user')->hideOnForm()->formatValue(function ($value, $entity) {
                return $entity->getUser()->getFirstname() . ' ' .$entity->getUser()->getLastname();
            })->setFormTypeOptions([
                'choice_label' => 'name',
            ]),
            AssociationField::new('status')
            ->formatValue(function ($value, $entity) {
                return $entity->getStatus()->getName();
            })->setFormTypeOptions([
                'choice_label' => 'name',
            ]),
            DateTimeField::new('createdAt')->hideOnForm()
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        $publishCommentBTN = Action::new('publishComment', 'Publier', 'fa fa-icon')
            ->linkToCrudAction('publishComment') 
            ->setCssClass('btn btn-primary'); 
        
        $unPublishCommentBTN = Action::new('unpublishComment', 'Dépublier', 'fa fa-icon')
            ->linkToCrudAction('unpublishComment') 
            ->setCssClass('btn btn-danger'); 

        return $actions
            ->add(Crud::PAGE_INDEX, $unPublishCommentBTN)
            ->add(Crud::PAGE_INDEX, $publishCommentBTN);
    }


    public function publishComment(AdminContext $context,EntityManagerInterface $entityManager)
    {

        $currentEntity = $this->getContext()->getEntity()->getInstance();
        $comment = $entityManager->getRepository(Comment::class)->findOneBy(["id"=> $currentEntity->getId()]);
        if($comment){
            $status = $entityManager->getRepository(Status::class)->findOneBy(["name"=> 'published']);
            $comment->setStatus($status);
            $entityManager->flush();
        }
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        $url = $adminUrlGenerator
        ->setController(self::class)
        ->setAction('index') 
        ->setEntityId($currentEntity->getId())
        ->generateUrl();
    
        return new RedirectResponse($url);
    }

    
    public function unpublishComment(AdminContext $context,EntityManagerInterface $entityManager)
    {

        $currentEntity = $this->getContext()->getEntity()->getInstance();
        $comment = $entityManager->getRepository(Comment::class)->findOneBy(["id"=> $currentEntity->getId()]);
        if($comment){
            $status = $entityManager->getRepository(Status::class)->findOneBy(["name"=> 'unpublished']);
            $comment->setStatus($status);
            $entityManager->flush();
        }
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        $url = $adminUrlGenerator
        ->setController(self::class)
        ->setAction('index') 
        ->setEntityId($currentEntity->getId())
        ->generateUrl();
    
        return new RedirectResponse($url);
    }
}
