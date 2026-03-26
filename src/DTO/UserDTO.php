<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class UserDTO {
    #[Assert\Email(message: 'Invalid email format')]
    #[Assert\NotBlank(message: 'An email is required')]
    private ?string $email = null;

    #[Assert\NotBlank(message: 'A password is required')]
    private ?string $password = null;

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): static
    {
        $this->password = $password;

        return $this;
    }
}
