<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')]
class Order
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'datetime_immutable', nullable:true)]
    private $orderedAt;

    #[ORM\Column(type: 'datetime_immutable', nullable:true)]
    private $acceptedAt;

    #[ORM\Column(type: 'datetime_immutable', nullable:true)]
    private $canceledAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private $refusedAt;

    #[ORM\Column(type: 'string', length: 255)]
    private $state;

    #[ORM\Column(type: 'decimal', precision:5, scale:2)]
    private $price = 0;

    #[ORM\ManyToOne(targetEntity: "User")]
    #[ORM\JoinColumn(onDelete:"Cascade")]
    private User $customer;

    #[ORM\ManyToOne(targetEntity: "Farm")]
    #[ORM\JoinColumn(onDelete:"Cascade")]
    private Farm $farm;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrderedAt(): ?\DateTimeImmutable
    {
        return $this->orderedAt;
    }

    public function setOrderedAt(\DateTimeImmutable $orderedAt): self
    {
        $this->orderedAt = $orderedAt;

        return $this;
    }

    public function getAcceptedAt(): ?\DateTimeImmutable
    {
        return $this->acceptedAt;
    }

    public function setAcceptedAt(\DateTimeImmutable $acceptedAt): self
    {
        $this->acceptedAt = $acceptedAt;

        return $this;
    }

    public function getCanceledAt(): ?\DateTimeImmutable
    {
        return $this->canceledAt;
    }

    public function setCanceledAt(\DateTimeImmutable $canceledAt): self
    {
        $this->canceledAt = $canceledAt;

        return $this;
    }

    public function getRefusedAt(): ?\DateTimeImmutable
    {
        return $this->refusedAt;
    }

    public function setRefusedAt(?\DateTimeImmutable $refusedAt): self
    {
        $this->refusedAt = $refusedAt;

        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(string $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getCustomer(): ?User
    {
        return $this->customer;
    }

    public function setCustomer(?User $customer): self
    {
        $this->customer = $customer;

        return $this;
    }

    public function getFarm(): ?Farm
    {
        return $this->farm;
    }

    public function setFarm(?Farm $farm): self
    {
        $this->farm = $farm;

        return $this;
    }

    public function getPrice()
    {
        return $this->price;
    }

    public function setPrice($price): self
    {
        $this->price = $price;

        return $this;
    }

}
