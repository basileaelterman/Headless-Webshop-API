<?php

namespace App\Entity;

use App\Entity\Product;
use App\Repository\ShoppingCartRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ShoppingCartRepository::class)]
#[ORM\Table(name: 'shopping_carts')]
class ShoppingCart
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @var Collection<int, ShoppingCartItem>
     */
    #[ORM\OneToMany(mappedBy: 'shoppingCart', targetEntity: ShoppingCartItem::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $products;

    public function __construct()
    {
        $this->products = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProducts(): ArrayCollection
    {   
        $products = [];

        foreach ($this->products as $product) {
            $p = $product->getProduct();

            $products[] = [
                'name'     => $p->getName(),
                'slug'     => $p->getSlug(),
                'price'    => $p->getPrice(),
                'quantity' => $product->getQuantity(),
            ];
        }

        return new ArrayCollection($products);
    }

    public function addProduct(Product $product, ?int $quantity = 1): static
    {
        $shoppingCartItem = new ShoppingCartItem();
        $shoppingCartItem->setProduct($product);
        $shoppingCartItem->setQuantity($quantity);
        $shoppingCartItem->setShoppingCart($this);

        if (!$this->products->contains($shoppingCartItem)) {
            $this->products->add($shoppingCartItem);
        } else {
            // TODO: Updating product should happen in controller.
            // ADD: throw error here
            foreach ($this->products as $item) {
                if ($item->getProduct() === $product) {
                    $oldQuantity = $item->getQuantity();
                    $item->setQuantity($oldQuantity + $quantity);

                    break;
                }
            }
        }

        return $this;
    }

    public function removeProduct(Product $product, ?int $quantity = 1): static
    {
        foreach ($this->products as $item) {
            if ($item->getProduct() === $product) {
                $oldQuantity = $item->getQuantity();

                if (!$quantity || $oldQuantity - $quantity <= 0) {
                    $this->products->removeElement($item);
                } else {
                    $item->setQuantity($oldQuantity - $quantity);
                }

                break;
            }
        }

        return $this;
    }
}
