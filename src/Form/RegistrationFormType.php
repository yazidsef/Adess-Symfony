<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', TextType::class ,['attr'=>['placeholder'=>'Email'], 'label'=>false]) 
            ->add('firstName',TextType::class ,['attr'=>['placeholder'=>'Nom'], 'label'=>false])
            ->add('lastName',TextType::class ,['attr'=>['placeholder'=>'Prénom'], 'label'=>false])
            ->add('region',TextType::class ,['attr'=>['placeholder'=>'region'], 'label'=>false])
            ->add('adresse',TextType::class ,['attr'=>['placeholder'=>'adresse'], 'label'=>false])
            ->add('code_postal',TextType::class ,['attr'=>['placeholder'=>'code postal'], 'label'=>false])
            ->add('status',TextType::class ,['attr'=>['placeholder'=>'status'], 'label'=>false])
            ->add('profession',TextType::class ,['attr'=>['placeholder'=>'profession'], 'label'=>false])
            ->add('plainPassword', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password','placeholder'=>'Mot de passe'],
                'label'=>false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'You should agree to our terms.',
                    ]),
                ],
            ])
          
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
