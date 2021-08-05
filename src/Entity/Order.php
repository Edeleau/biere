<?php

namespace App\Entity;

use DateTimeZone;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\OrderRepository;

/**
 * @ORM\Entity(repositoryClass=OrderRepository::class)
 * @ORM\Table(name="`order`")
 * @ORM\HasLifecycleCallbacks()
 */
class Order
{

    const PROCESSING      = 'Préparation en cours';
    const COMPLETED       = 'Commande prête';
    const PAID            = 'Payé';
    const PAYMENT_PENDING = 'Paiement en attente';
    const SEND            = 'Commande envoyé';
    const REFOUNDED       = 'Renvoyé';
    const FAILED          = 'Commande échoué';
    const CANCELLED       = 'commande annulé';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="orders")
     */
    private $user;

    /**
     * @ORM\Column(type="float")
     */
    private $price;

    /**
     * @ORM\Column(type="datetime")
     */
    private $registration_date;

    /**
     * @ORM\Column(type="string", length=120)
     */
    private $status;

    /**
     * @ORM\OneToMany(targetEntity=OrderDetails::class, mappedBy="ordered", orphanRemoval=true)
     */
    private $orderDetails;

    public function __construct()
    {
        $this->orderDetails = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getRegistrationDate(): ?\DateTimeInterface
    {
        return $this->registration_date;
    }

    /**
     * @ORM\PrePersist
     */
    public function setRegistrationDate(): self
    {
        $this->registration_date = new DateTimeImmutable('now', new DateTimeZone('Europe/Paris'));

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection|OrderDetails[]
     */
    public function getOrderDetails(): Collection
    {
        return $this->orderDetails;
    }

    public function addOrderDetail(OrderDetails $orderDetail): self
    {
        if (!$this->orderDetails->contains($orderDetail)) {
            $this->orderDetails[] = $orderDetail;
            $orderDetail->setOrdered($this);
        }

        return $this;
    }

    public function removeOrderDetail(OrderDetails $orderDetail): self
    {
        if ($this->orderDetails->removeElement($orderDetail)) {
            // set the owning side to null (unless already changed)
            if ($orderDetail->getOrdered() === $this) {
                $orderDetail->setOrdered(null);
            }
        }

        return $this;
    }
}
