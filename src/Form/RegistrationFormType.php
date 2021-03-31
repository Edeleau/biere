<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Country;
use Symfony\Component\Form\AbstractType;
use Vich\UploaderBundle\Form\Type\VichFileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
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
            ])
            ->add('address', TextType::class, [
                'constraints' => [
                    new Length([
                        'max' => 255,
                        'maxMessage' => 'Your address must have {{ limit }} characters max.',
                        'min' => 2,
                        'minMessage' => 'Your address must have {{ limit }} characters min.',
                    ]),
                    new NotBlank([
                        'message' => 'Please enter an address',
                    ]),
                ]
            ])
            ->add('cp', TextType::class , [
                'constraints' => [
                    new Length([
                        'max' => 5,
                        'maxMessage' => 'Your postal code must have {{ limit }} characters max.',
                        'min' => 4,
                        'minMessage' => 'Your postal code must have {{ limit }} characters min.',
                    ]),
                    new NotBlank([
                        'message' => 'Please enter a postal code',
                    ]),
                ]
            ])
            ->add('city', TextType::class , [
                'constraints' => [
                    new Length([
                        'max' => 60,
                        'maxMessage' => 'Your city must have {{ limit }} characters max.',
                        'min' => 1,
                        'minMessage' => 'Your city must have {{ limit }} characters min.',
                    ]),
                    new NotBlank([
                        'message' => 'Please enter a city',
                    ]),
                ]
            ])
            ->add('imgFile', VichFileType::class)
            ->add('country', EntityType::class ,[
                'class' => Country::class,
                'choice_label' => 'country'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
