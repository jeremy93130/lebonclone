<?php

namespace App\Entity;

use App\Repository\CommandRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommandRepository::class)]
class Command
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?DateTimeImmutable $createdAt = null;

    #[ORM\Column(length: 255)]
    private ?string $adress = null;

    #[ORM\Column]
    private ?int $zipCode = null;

    #[ORM\Column(length: 255)]
    private ?string $city = null;

    #[ORM\Column]
    private ?float $totalPrice = null;

    /**
     * @var Collection<int, ProductCommand>
     */
    #[ORM\OneToMany(targetEntity: ProductCommand::class, mappedBy: 'command')]
    private Collection $productCommand;

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable('now',new \DateTimeZone('Europe/Paris'));
        $this->productCommand = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getAdress(): ?string
    {
        return $this->adress;
    }

    public function setAdress(string $adress): static
    {
        $this->adress = $adress;

        return $this;
    }

    public function getZipCode(): ?int
    {
        return $this->zipCode;
    }

    public function setZipCode(int $zipCode): static
    {
        $this->zipCode = $zipCode;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getTotalPrice(): ?float
    {
        return $this->totalPrice;
    }

    public function setTotalPrice(float $totalPrice): static
    {
        $this->totalPrice = $totalPrice;

        return $this;
    }

    /**
     * @return Collection<int, ProductCommand>
     */
    public function getProductCommand(): Collection
    {
        return $this->productCommand;
    }

    public function addProductCommand(ProductCommand $productCommand): static
    {
        if (!$this->productCommand->contains($productCommand)) {
            $this->productCommand->add($productCommand);
            $productCommand->setCommand($this);
        }

        return $this;
    }

    public function removeProductCommand(ProductCommand $productCommand): static
    {
        if ($this->productCommand->removeElement($productCommand)) {
            // set the owning side to null (unless already changed)
            if ($productCommand->getCommand() === $this) {
                $productCommand->setCommand(null);
            }
        }

        return $this;
    }
}
