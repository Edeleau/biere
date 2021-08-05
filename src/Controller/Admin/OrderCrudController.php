<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use App\Repository\UserRepository;
use App\Repository\OrderRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class OrderCrudController extends AbstractCrudController
{
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public static function getEntityFqcn(): string
    {
        return Order::class;
    }


    public function configureFields(string $pageName): iterable
    {
        $rqUser = $this->userRepository->findAll();
        $user = [];
        foreach ($rqUser as $key => $entity) {
            // Pour le formulaire avec la relation il me faut l'entité
            $user[$entity->getId() . '-' . $entity->getName() . ' ' . $entity->getFirstname()] = $entity;
        }

        return [
            IdField::new('id')
                ->hideOnForm(),
            ChoiceField::new('user')
                ->setChoices($user)
                ->onlyOnForms(),
            AssociationField::new('user')
                ->setTemplatePath('admin/customUser.html.twig')
                ->hideOnForm(),
            AssociationField::new('orderDetails')
                ->onlyOnIndex(),
            CollectionField::new('orderDetails')
                ->setTemplatePath('/admin/orderDetailsInOrder.html.twig')
                ->onlyOnDetail(),
            NumberField::new('price')
                ->setNumDecimals(2)
                ->hideOnForm(),
            ChoiceField::new('status')
                ->setChoices([
                    Order::COMPLETED        => Order::COMPLETED,
                    Order::FAILED           => Order::FAILED,
                    Order::PAID             => Order::PAID,
                    Order::PAYMENT_PENDING  => Order::PAYMENT_PENDING,
                    Order::PROCESSING       => Order::PROCESSING,
                    Order::REFOUNDED        => Order::REFOUNDED,
                    Order::SEND             => Order::SEND,
                    Order::CANCELLED        => Order::CANCELLED
                ]),
            DateTimeField::new('registration_date')
                ->setFormat('dd.MM.yyyy HH:mm:ss')
                ->hideOnForm(),

        ];
    }
    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }
}
