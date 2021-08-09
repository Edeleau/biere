<?php

namespace App\Controller\Admin;

use App\Entity\PromoCode;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\PercentField;

class PromoCodeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return PromoCode::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('code'),
            DateTimeField::new('eligibilityDate', 'Date d\'éligibilité')
                ->setFormat('dd.MM.yyyy  HH:mm:ss'),
            DateTimeField::new('expirationDate', 'Date d\'expiration')
                ->setFormat('dd.MM.yyyy  HH:mm:ss'),
            PercentField::new('reduction', 'Réduction')
                ->setStoredAsFractional(false),
        ];
    }
}
