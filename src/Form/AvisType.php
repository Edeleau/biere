<?php

namespace App\Form;

use App\Entity\Avis;
use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class AvisType extends AbstractType
{


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->product = $options['product'];
        $builder
            ->add('message', TextareaType::class, ['label' => 'Laissez votre avis sur ce produit'])
            ->add('product', HiddenType::class, [
                'attr' => [
                    'value' =>  $this->product
                ]
            ])
            ->add('envoyer', SubmitType::class, [
                'label' => 'Envoyer mon avis',
                'attr' => [
                    'class' => 'btn btn-brown'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Avis::class,
            'product' => null,
        ]);
    }
}
