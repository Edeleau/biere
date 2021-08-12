<?php

namespace App\Form;

use App\Entity\Contact;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ContactFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('mail', EmailType::class, [
                'constraints' => [
                    new Email([
                        'message' => "L'email n'est pas valide."
                    ]),
                    new Length([
                        'max' => 180,
                        'maxMessage' => 'Votre e-mail doit avoir {{ limit }} caractères max.',

                    ]),
                ],
                'required' => true,
                'label' => false,
                'attr' => [
                    'placeholder' => 'E-mail',
                ]
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
                ],
                'required' => true,
                'label' => false,
                'attr' => [
                    'placeholder' => 'Nom et prénom',
                ]
            ])
            ->add('message', TextareaType::class, [
                'constraints' => [
                    new Length([
                        'min' => 5,
                        'minMessage' => 'Entrez un message.',

                    ]),
                    new NotBlank([
                        'message' => 'Entrez un message',
                    ]),
                ],
                'required' => true,
                'label' => false,
                'attr' => [
                    'placeholder' => 'Votre message ici',
                ]
            ])
            ->add('Envoyer', SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-brown'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Contact::class,
        ]);
    }
}
