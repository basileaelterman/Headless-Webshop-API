<?php

namespace App\Entity;

use App\Repository\ShoppingCartItemRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ShoppingCartItemRepository::class)]
#[ORM\Table(name: 'shopping_cart_items')]
class ShoppingCartItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Product $product = null;

    #[ORM\Column]
    private ?int $quantity = null;

    #[ORM\ManyToOne(targetEntity: ShoppingCart::class, inversedBy: 'products')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?ShoppingCart $shoppingCart = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): static
    {
        $this->product = $product;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getShoppingCart(): ?ShoppingCart
    {
        return $this->shoppingCart;
    }

    public function setShoppingCart(?ShoppingCart $shoppingCart): static
    {
        $this->shoppingCart = $shoppingCart;

        return $this;
    }
}
