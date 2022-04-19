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

    public function getId(): ?int
    {
        return $this->id;
    }
}
