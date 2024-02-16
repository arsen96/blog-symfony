<?php
// src/Form/CommentType.php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class CommentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('description', TextareaType::class, [
                'attr' => ['class' => 'form-control', 'rows' => '6'],
            ])
            ->add('submit', SubmitType::class, ['attr' => ['class' => 'btn btn-dark']]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        // You can set default data class here if needed
    }

    // Optional: Define a getBlockPrefix() method to set the form's name
    public function getBlockPrefix()
    {
        return 'comment_form'; // This sets the form's name
    }
}

?>