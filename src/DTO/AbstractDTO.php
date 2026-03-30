<?php

namespace App\Helper;

use Symfony\Component\Validator\Validator\ValidatorInterface;

class AbstractDTO {
    public function getViolations(ValidatorInterface $validatorInterface): ?array
    {
        $violations = $validatorInterface->validate($this);

        if (count($violations) > 0) {
            $messages = [];

            // Store and return violation messages
            foreach ($violations as $violation) {
                $messages[$violation->getPropertyPath()][] = $violation->getMessage();
            }

            return $messages;
        }

        return null;
    }
}
