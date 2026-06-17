<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UploadedDriveFileValidator extends ConstraintValidator
{
    public function __construct(
        private readonly ValidatorInterface $validator,
    ) {
    }

    public function validate($value, Constraint $constraint): void
    {
        $violations = $this->validator->validate(
            $value,
            [
                new File(
                    maxSize: '64M',
                ),
            ]
        );

        foreach ($violations as $violation) {
            $this->context->buildViolation($violation->getMessage())
                ->setParameters($violation->getParameters())
                ->addViolation();
        }
    }
}
