<?php

namespace App\Entity;

use App\Repository\ProductCommandRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductCommandRepository::class)]
class ProductCommand
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'productcommand')]
    #[ORM\JoinColumn(nullable: true, name: 'product_id', referencedColumnName: 'id')]
    private ?Products $product = null;

    #[ORM\ManyToOne(inversedBy: 'productCommand')]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'productCommand')]
    private ?Command $command = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProduct(): ?Products
    {
        return $this->product;
    }

    public function setProduct(?Products $product): static
    {
        $this->product = $product;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getCommand(): ?Command
    {
        return $this->command;
    }

    public function setCommand(?Command $command): static
    {
        $this->command = $command;

        return $this;
    }
}
