<?php
// src/Form/CommentType.php
namespace App\Form;

use Doctrine\DBAL\Types\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('password', RepeatedType::class, [
            'help' => 'The ZIP/Postal code for your credit card\'s billing address.',
            'type' => PasswordType::class,
            'invalid_message' => 'Les champs du mot de passe doivent correspondre.',
            'error_bubbling' => true,
            'options' => ['attr' => ['class' => 'form-control']],
            'required' => true,
            'first_options'  => ['label' => 'Choisissez un nouveau mot de passe',],
            'second_options' => ['label' => 'Répéter le nouveau mot de passe'],
        ])
        ->add('token', HiddenType::class, [
            'data' => 'abcdef',
            'mapped' => false
        ])
        ->add('submit', SubmitType::class, ['attr' => ['class' => 'btn btn-primary'],
            'label' => 'Confirmer' 
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'error_mapping' => [
                '.' => 'password',
            ],
        ]);
        // $resolver->setDefaults([
        //     'error_mapping' => [
        //         'matchingCityAndZipCode' => 'city',
        //     ],
        // ]);
    }

    // Optional: Define a getBlockPrefix() method to set the form's name
    public function getBlockPrefix()
    {
        return 'user_profile'; // This sets the form's name
    }
}

?>