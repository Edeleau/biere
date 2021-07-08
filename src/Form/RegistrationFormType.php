<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Rollerworks\Component\PasswordStrength\Validator\Constraints\PasswordStrength;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, [
                'constraints' => [
                    new Email([
                        'message' => '{{value}} is not valid.'
                    ]),
                    new Length([
                        'max' => 180,
                        'maxMessage' => 'Your mail must have {{ limit }} characters max.',

                    ]),
                ]
            ])
            ->add('password', PasswordType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password.',
                    ]),
                    new PasswordStrength([
                        'minLength'       => 6,
                        'tooShortMessage' => 'Your password should be at least 6 characters.',
                        'minStrength' => 3,
                        'message'     => 'Your password should contain a characters upper and lower and a number.',
                    ]),
                ],
            ])
            ->add('name',  TextType::class, [
                'constraints' => [
                    new Length([
                        'max' => 50,
                        'maxMessage' => 'Your name must have {{ limit }} characters max.',
                        'min' => 2,
                        'minMessage' => 'Your name must have {{ limit }} characters min.',

                    ]),
                    new NotBlank([
                        'message' => 'Please enter a name',
                    ]),
                ]
            ])
            ->add('firstname', TextType::class, [
                'constraints' => [
                    new Length([
                        'max' => 50,
                        'maxMessage' => 'Your firstname must have {{ limit }} characters max.',
                        'min' => 2,
                        'minMessage' => 'Your firstname must have {{ limit }} characters min.',

                    ]),
                    new NotBlank([
                        'message' => 'Please enter a firstname.',
                    ]),
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}