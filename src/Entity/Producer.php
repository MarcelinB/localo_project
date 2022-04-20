<?php

namespace App\Entity;

use App\Repository\ProducerRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProducerRepository::class)]
class Producer extends User
{

    
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;
    
    public const ROLE = 'producer';

    public function getId(): ?int
    {
        return $this->id;
    }
}