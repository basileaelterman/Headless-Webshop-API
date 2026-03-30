<?php

namespace App\DTO;

use App\DTO\AbstractDTO;
use Symfony\Component\Validator\Constraints as Assert;

class PayloadDTO extends AbstractDTO {
    #[Assert\Text(message: 'Category must be a string')]
    private ?string $category;

    #[Assert\GreaterThan(
        value: 0,
        message: 'Minimum price must be greater than 0',
    )]
    private ?float $minPrice;

    #[Assert\GreaterThan(
        value: 0,
        message: 'Maximum price must be greater than minPrice',
    )]
    private ?float $maxPrice;

    #[Assert\GreaterThan(
        value: 0,
        message: 'Minimum price must be greater than 0',
    )]
    private ?int $quantity;

    #[Assert\Integer(message: 'Token is invalid')]
    // Not false, is truthy
    #[Assert\GreaterThan(
        value: 0,
        message: 'Token is invalid',
    )]
    private ?int $token;

    public function setCategory(?string $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function setMinPrice(?float $minPrice): static
    {
        $this->minPrice = $minPrice;

        return $this;
    }

    public function setMaxPrice(?float $maxPrice): static
    {
        $this->maxPrice = $maxPrice;

        return $this;
    }

    public function setQuantity(?int $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function setToken(?string $token): static
    {
        $this->token = base64_decode($token, true);

        return $this;
    }
}
