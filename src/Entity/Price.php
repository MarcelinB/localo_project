<?php

namespace App\Entity;

use App\Repository\PriceRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\NotBlank;

#[ORM\Embeddable()]
class Price
{
    #[ORM\Column(type: 'integer')]
    #[NotBlank()]
    #[GreaterThan(0)]
    private $unitPrice = 0;

    #[ORM\Column(type: 'float')]
    #[NotBlank()]
    private $vat = 0;

    public function getUnitPrice(): ?int
    {
        return $this->unitPrice;
    }

    public function setUnitPrice(int $unitPrice): self
    {
        $this->price = $unitPrice;

        return $this;
    }

    public function getVat(): ?float
    {
        return $this->vat;
    }

    public function setVat(float $vat): self
    {
        $this->vat = $vat;

        return $this;
    }
}
