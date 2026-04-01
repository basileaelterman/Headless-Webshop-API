<?php

namespace App\DTO;

use Symfony\Component\Validator\Validator\ValidatorInterface;

class AbstractDTO
{
    private ?ValidatorInterface $validator = null;

    public function setValidator(ValidatorInterface $validator): void
    {
        $this->validator = $validator;
    }

    public function getViolations(): ?array
    {
        // TODO: replace with proper DI once wired up — temporary null-guard
        if (!$this->validator) {
            return null;
        }

        $violations = $this->validator->validate($this);

        if (count($violations) === 0) {
            return null;
        }

        $messages = [];
        foreach ($violations as $violation) {
            $messages[$violation->getPropertyPath()][] = $violation->getMessage();
        }

        return $messages;
    }
}