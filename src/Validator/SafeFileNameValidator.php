<?php

declare(strict_types=1);

namespace App\Validator;

use App\Infrastructure\Lang;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SafeFileNameValidator extends ConstraintValidator
{
    public function __construct(
        private readonly ValidatorInterface $validator,
        private readonly ParameterBagInterface $params,
    ) {
    }

    public function validate($value, Constraint $constraint): void
    {
        if ($value instanceof UploadedFile) {
            $filename = $value->getClientOriginalName();

            if (
                str_contains($filename, '..')
                || str_contains($filename, '/')
                || str_contains($filename, '\\')
            ) {
                $this->context
                    ->buildViolation(Lang::t('error_file_not_safe_filename'))
                    ->addViolation();
            }
        }
    }
}
