<?php

declare(strict_types=1);

namespace App\Validator;

use App\Infrastructure\Lang;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class NoteBlocksValidator extends ConstraintValidator
{
    private const array ALLOWED_TYPES = ['paragraph', 'table', 'code', 'alert', 'header', 'list', 'image', 'attaches'];

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof NoteBlocks) {
            return;
        }

        if (!is_array($value)) {
            $this->addError($constraint);
            return;
        }

        foreach ($value as $block) {
            if (!is_array($block)) {
                $this->addError($constraint);
                return;
            }
            if (
                !array_key_exists('type', $block)
                || !array_key_exists('data', $block)
            ) {
                $this->addError($constraint);
                return;
            }
            if (!in_array($block['type'], self::ALLOWED_TYPES, true)) {
                $this->addError($constraint);
                return;
            }
        }
    }

    private function addError(NoteBlocks $constraint): void
    {
        $this->context
            ->buildViolation(Lang::t($constraint->errorKey))
            ->addViolation();
    }
}
