<?php

namespace App\Entity;

use App\Repository\OrderLineRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderLineRepository::class)]
class OrderLine
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Embedded(class:"Price")]
    private Price $price;

    #[ORM\ManyToOne(targetEntity: "Product")]
    #[ORM\JoinColumn(onDelete:"Cascade")]
    private Product $product;

    #[ORM\ManyToOne(targetEntity: "Order")]
    #[ORM\JoinColumn(onDelete:"Cascade")]
    private Order $order;

    public function __construct()
    {
        $this->price = new Price();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): self
    {
        $this->product = $product;

        return $this;
    }

    public function getOrder(): ?Order
    {
        return $this->order;
    }

    public function setOrder(?Order $order): self
    {
        $this->order = $order;

        return $this;
    }

    public function getPrice(): Price
    {
        return $this->price;
    }

    public function setPrice(Price $price): self
    {
        $this->price = $price;

        return $this;
    }

}
