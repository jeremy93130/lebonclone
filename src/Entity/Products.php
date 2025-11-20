<?php

namespace App\Entity;

use App\Repository\ProductsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductsRepository::class)]
class Products
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private ?float $price = null;

    #[ORM\Column(length: 255)]
    private ?string $mainImage = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    private ?string $category = null;

    /**
     * @var Collection<int, ProductCommand>
     */
    #[ORM\OneToMany(targetEntity: ProductCommand::class, mappedBy: 'product')]
    private Collection $productcommand;

    #[ORM\ManyToOne(inversedBy: 'product')]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $user = null;

    #[ORM\Column]
    private ?bool $sold = false;

    #[ORM\Column]
    private ?bool $shown = true;

    public function __construct()
    {
        $this->productcommand = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getMainImage(): ?string
    {
        return $this->mainImage;
    }

    public function setMainImage(string $mainImage): static
    {
        $this->mainImage = $mainImage;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(string $category): static
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return Collection<int, ProductCommand>
     */
    public function getProductcommand(): Collection
    {
        return $this->productcommand;
    }

    public function addProductcommand(ProductCommand $productcommand): static
    {
        if (!$this->productcommand->contains($productcommand)) {
            $this->productcommand->add($productcommand);
            $productcommand->setProduct($this);
        }

        return $this;
    }

    public function removeProductcommand(ProductCommand $productcommand): static
    {
        if ($this->productcommand->removeElement($productcommand)) {
            // set the owning side to null (unless already changed)
            if ($productcommand->getProduct() === $this) {
                $productcommand->setProduct(null);
            }
        }

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

    public function isSold(): ?bool
    {
        return $this->sold;
    }

    public function setSold(bool $sold): static
    {
        $this->sold = $sold;

        return $this;
    }

    public function isShown(): ?bool
    {
        return $this->shown;
    }

    public function setShown(bool $shown): static
    {
        $this->shown = $shown;

        return $this;
    }
}
