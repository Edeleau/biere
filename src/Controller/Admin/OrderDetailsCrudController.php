<?php

namespace App\Controller\Admin;

use DateTime;
use App\Entity\OrderDetails;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class OrderDetailsCrudController extends AbstractCrudController
{
    private $orderRepository;
    private $productRepository;

    public function __construct(OrderRepository $orderRepository, ProductRepository $productRepository)
    {
        $this->orderRepository = $orderRepository;
        $this->productRepository = $productRepository;
    }

    public static function getEntityFqcn(): string
    {
        return OrderDetails::class;
    }


    public function configureFields(string $pageName): iterable
    {
        $rqOrder = $this->orderRepository->findAll();
        $rqProduct = $this->productRepository->findAll();
        $order = [];
        $product = [];
        foreach ($rqOrder as $key => $entity) {
            $registrationDate = DateTime::createFromFormat("d-m-Y H:i:s", $entity->getRegistrationDate()->format('d-m-Y H:i:s'));
            $registrationDate = $registrationDate->format('d-m-Y H:i:s');
            // Pour le formulaire avec la relation il me faut l'entité
            $order[$entity->getId() . '-' . $entity->getUser()->getName() . ' ' . $entity->getUser()->getFirstname() . ' / ' . $registrationDate] = $entity;
        }
        foreach ($rqProduct as $key => $entity) {
            $product[$entity->getId() . '-' . $entity->getTitle() . ' ' . $entity->getCapacity() . 'cl / stock : ' . $entity->getStock(). ' / price : '.$entity->getPrice().'€/u'] =  $entity;
        }
        return [
            IdField::new('id')
                ->onlyOnIndex(),
            AssociationField::new('ordered')
                ->onlyOnIndex(),
            ChoiceField::new('ordered')
                ->setChoices($order)
                ->onlyOnForms(),
            AssociationField::new('product')
                ->onlyOnIndex(),
            ChoiceField::new('product')
                ->setChoices($product)
                ->onlyOnForms(),
            IntegerField::new('quantity'),
            NumberField::new('price')
                ->setNumDecimals(2)
                ->onlyOnIndex(),
        ];
    }
}
