<?php



namespace App\EventSubscriber;

use App\Entity\User;
use App\Entity\Order;
use App\Entity\OrderDetails;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\OrderDetailsRepository;
use Doctrine\Common\Collections\Expr\Value;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityDeletedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityDeletedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class EasyAdminSubscriber implements EventSubscriberInterface
{

    private $entityManager;
    private $passwordEncoder;
    private $orderDetailsRepository;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordEncoderInterface $passwordEncoder,  OrderDetailsRepository $orderDetailsRepository)
    {
        $this->orderDetailsRepository = $orderDetailsRepository;
        $this->entityManager = $entityManager;
        $this->passwordEncoder = $passwordEncoder;
    }

    public static function getSubscribedEvents()
    {

        return [
            BeforeEntityPersistedEvent::class => [
                ['addOrderDetails', 10],
                ['addOrder', 0],
            ],
            BeforeEntityUpdatedEvent::class => [
                ['updateOrderDetails', 10],
                ['updateOrder', 0],
            ],
            AfterEntityDeletedEvent::class=>[
                ['deleteOrderDetails',0]
            ]
        ];
    }

    public function updateOrder(BeforeEntityUpdatedEvent $event)
    {
        $entity = $event->getEntityInstance();
        if (!($entity instanceof Order)) {
            return;
        }
        $this->setOrder($entity);
    }

    public function addOrder(BeforeEntityPersistedEvent $event)
    {
        $entity = $event->getEntityInstance();
        if (!($entity instanceof Order)) {
            return;
        }

        $this->setOrder($entity);
    }
    public function updateOrderDetails(BeforeEntityUpdatedEvent $event)
    {
        $entity = $event->getEntityInstance();

        if (!($entity instanceof OrderDetails)) {
            return;
        }
        

        $this->setOrderDetails($entity);
        //Modifiication de prix total de la commande
        $this->setOrder($entity->getOrdered());
    }
    public function deleteOrderDetails(AfterEntityDeletedEvent $event)
    {
        $entity = $event->getEntityInstance();

        if (!($entity instanceof OrderDetails)) {
            return;
        }
        

        //Modifiication de prix total de la commande
        $this->setOrder($entity->getOrdered());
    }

    public function addOrderDetails(BeforeEntityPersistedEvent $event)
    {
        $entity = $event->getEntityInstance();
        if (!($entity instanceof OrderDetails)) {
            return;
        }

        $this->setOrderDetails($entity);
        //Modifiication de prix total de la commande
        $this->setOrder($entity->getOrdered());

    }


    /**
     * @param Order $entity
     */
    public function setOrder(Order $order): void
    {

        $id = $order->getId();
        $price = 0;
        $details = $this->orderDetailsRepository->findBy(['ordered' => $id]);
        foreach ($details as $key => $value) {
            $price += ($value->getPrice() * $value->getQuantity());
        }
        $order->setPrice($price);
        $this->entityManager->persist($order);
        $this->entityManager->flush();
    }
    /**
     * @param OrderDetails $entity
     */
    public function setOrderDetails(OrderDetails $orderDetails): void
    {
        $price = $orderDetails->getProduct()->getPrice();
        $orderDetails->setPrice($price);
        $this->entityManager->persist($orderDetails);
        $this->entityManager->flush();
    }
}
