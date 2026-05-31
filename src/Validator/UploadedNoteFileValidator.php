<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UploadedNoteFileValidator extends ConstraintValidator
{
    public function __construct(
        private readonly ValidatorInterface $validator,
        private readonly ParameterBagInterface $params,
    ) {
    }

    public function validate($value, Constraint $constraint): void
    {
        $violations = $this->validator->validate(
            $value,
            [
                new File(
                    maxSize: $this->params->get('file.uploadMaxSize') . 'M',
                    extensions: ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'zip', 'doc', 'docx', 'xls', 'xlsx'],
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
