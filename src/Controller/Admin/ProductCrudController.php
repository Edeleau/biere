<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use App\Repository\CountryRepository;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use Vich\UploaderBundle\Form\Type\VichImageType;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CountryField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class ProductCrudController extends AbstractCrudController
{
    private $countryRepository;

    public function __construct(CountryRepository $countryRepository)
    {
        $this->countryRepository = $countryRepository;
    }

    public static function getEntityFqcn(): string
    {
        return Product::class;
    }


    public function configureFields(string $pageName): iterable
    {
        $rq = $this->countryRepository->findAll();
        $country = [];
        $classification = [
            'Bières' => Product::CLASSIFICATION_BEER,
            'Goodies' => Product::CLASSIFICATION_GOODIES
        ];
        foreach ($rq as $key => $entity) {
            // Pour le formulaire avec la relation il me faut l'entité
            $country += [$entity->getCountry() => $entity];
        }

        return [
            NumberField::new('id')->onlyOnIndex(),
            TextField::new('title'),
            ChoiceField::new('category')->setChoices([
                'Witbier' => 'witbier',
                'Rosé'    => 'rose',
                'Brown'   => 'brown',
                'Blond'   => 'blond',
                'Amber'   => 'amber',
                'Ruby'    => 'ruby',
                'Black'   => 'black',
                'Glass'   => 'glass'
            ]),
            ChoiceField::new('classification')
                ->setChoices($classification),
            NumberField::new('capacity'),
            NumberField::new('degree'),
            TextField::new('brand'),
            ChoiceField::new('country')
                ->setChoices($country)
                ->onlyOnForms(),
            AssociationField::new('country')
                ->setTemplatePath('admin/customCountry.html.twig')
                ->hideOnForm(),
            NumberField::new('price')
                ->setNumDecimals(2),
            TextField::new('imgFile')
                ->setFormType(VichImageType::class)
                ->setLabel('Image')
                ->setRequired(true)
                ->onlyOnForms(),
            ImageField::new('img')
                ->setBasePath('/img/product')
                ->hideOnForm(),
            TextareaField::new('description'),
            IntegerField::new('stock')
        ];
    }
}
