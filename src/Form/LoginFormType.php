<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LoginFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', EmailType::class, [
                'label' => 'Email',
                'attr' => ['autocomplete' => 'email', 'class' => 'form-control'],
                'required' => true,
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Password',
                'attr' => ['autocomplete' => 'current-password', 'class' => 'form-control'],
                'required' => true,
            ])
            ->add('remember_me', CheckboxType::class, [
                'label' => 'Remember me',
                'required' => false,
            ])
            ->add('csrf_token', HiddenType::class, [
                'data' => $options['csrf_token'],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Sign in',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // CSRF protection is an important security feature
            'csrf_token' => null,
        ]);
    }
}
