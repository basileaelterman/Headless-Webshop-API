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
        if (!$this->validator) {
            throw new \LogicException('Validator not set.');
        }

        $violations = $this->validator->validate($this);

        if (count($violations) > 0) {
            $messages = [];

            foreach ($violations as $violation) {
                $messages[$violation->getPropertyPath()][] = $violation->getMessage();
            }

            return $messages;
        }

        return null;
    }
}
