<?php

namespace App\DTO;

use App\DTO\AbstractDTO;
use Symfony\Component\Validator\Constraints as Assert;

class UserDTO extends AbstractDTO {
    #[Assert\Email(message: 'Invalid email format')]
    #[Assert\NotNull(message: 'An email is required')]
    private ?string $email = null;

    #[Assert\NotNull(message: 'A password is required')]
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
