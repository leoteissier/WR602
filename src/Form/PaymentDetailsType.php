<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Validator\Constraints as Assert;

class PaymentDetailsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('cardNumber', TextType::class, [
                'label' => 'Numéro de carte',
                'attr' => ['placeholder' => '1234 5678 1234 5678', 'class' => 'form-control'],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['min' => 19, 'max' => 19]),
                    new Assert\Regex([
                        'pattern' => '/^\d{16}$/',
                        'message' => 'The card number must be a 16-digit number.',
                    ]),
                ],
            ])
            ->add('expiryDate', TextType::class, [
                'label' => 'Date d\'expiration',
                'attr' => ['placeholder' => 'MM/YY', 'pattern' => '^(0[1-9]|1[0-2])\/[0-9]{2}$', 'class' => 'form-control'],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['min' => 5, 'max' => 5]),
                    new Assert\Regex([
                        'pattern' => '/^(0[1-9]|1[0-2])\/[0-9]{2}$/',
                        'message' => 'The expiry date should be in the format MM/YY.',
                    ]),
                ],
            ])
            ->add('cvv', PasswordType::class, [
                'label' => 'CVV',
                'attr' => ['placeholder' => '123', 'pattern' => '\d{3}', 'class' => 'form-control'],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['min' => 3, 'max' => 3]),
                    new Assert\Regex([
                        'pattern' => '/^\d{3}$/',
                        'message' => 'The CVV must be a 3-digit number.',
                    ]),
                ],
            ]);
    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
        ]);
    }
}
