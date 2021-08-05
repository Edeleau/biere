<?php

namespace App\Form;

use App\Entity\Country;
use App\Entity\Product;
use App\Data\SearchData;
use Doctrine\ORM\EntityRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\RangeType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class SearchType extends AbstractType
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    private function getProductRepository(): ProductRepository
    {
        return $this->entityManager->getRepository(Product::class);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $this->classification = $options['classification'];
        $country = $this->getProductRepository()->getCountry($this->classification);
        $brands = $this->getProductRepository()->getBrands($this->classification);
        $categories = $this->getProductRepository()->getCategories($this->classification);

        $builder
            ->add('q', TextType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                    'placeholder' => 'Chercher un produit',
                ],
            ])
            ->add('minCapacity', NumberType::class, [
                'required' => false,
                'label' => false,
                'attr' => [
                    'placeholder' => 'Prix min',
                    'value' => 0
                ]
            ])
            ->add('maxCapacity', NumberType::class, [
                'required' => false,
                'label' => false,
                'attr' => [
                    'placeholder' => 'Prix max',
                    'value' => ceil($this->getProductRepository()->getMaxCapacity($this->classification))
                ]
            ])
            ->add('minPrice', NumberType::class, [
                'required' => false,
                'label' => false,
                'attr' => [
                    'placeholder' => 'Prix min',
                    'value' => 0
                ]
            ])
            ->add('maxPrice', NumberType::class, [
                'required' => false,
                'label' => false,
                'attr' => [
                    'placeholder' => 'Prix max',
                    'value' => ceil($this->getProductRepository()->getMaxPrice($this->classification))
                ]
            ])
            ->add('brand', ChoiceType::class, [
                'required' => false,
                'label' => false,
                'placeholder' => 'Marques',
                'choices' => $brands
            ])
            ->add('category', ChoiceType::class, [
                'required' => false,
                'label' => false,
                'placeholder' => 'Type de bière',
                'choices' => $categories
            ])
            ->add('country', ChoiceType::class, [
                'required' => false,
                'label' => false,
                'placeholder' => 'Pays',
                'choices' => $country
            ])
            ->add('order', ChoiceType::class, [
                'required' => false,
                'label' => false,
                'placeholder' => 'Ordonner par : (prix par défault)',
                'choices' => [
                    'Title' => 'title',
                    'Capacité' => 'capacity',
                    'Prix' => 'price',
                    'Marque' => 'brand',
                    'Catégorie' => 'category',
                ],
                'constraints' => [
                    new Choice([
                        'choices' => [
                            'title',
                            'capacity',
                            'price',
                            'brand',
                            'category',
                        ],
                        'message' => 'Choix non existant',
                    ]),
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SearchData::class,
            'method' => 'GET',
            'crsf_protection' => false,
            'classification' => null
        ]);
    }

    public function getBlockPrefix()
    {
        return '';
    }
}
