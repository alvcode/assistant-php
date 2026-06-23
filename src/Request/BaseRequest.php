<?php

namespace App\Request;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class BaseRequest
{
    protected const INT_2_MAX = 32_767;
    protected const INT_4_MAX = 2_147_483_647;
    protected const INT_8_MAX = 9_223_372_036_854_775_807;

    protected array $validation_errors = [];

    public function __construct(
        private readonly ValidatorInterface $validator,
        private readonly DenormalizerInterface $serializer
    ) {}

    protected function prepareForValidation(): void
    {
    }

    public function validate(): bool
    {
        $this->validation_errors = [];
        $this->prepareForValidation();

        $errors = $this->validator->validate($this);

        foreach ($errors as $message) {
            $this->validation_errors[$message->getPropertyPath()] = $message->getMessage();
        }

        return empty($this->validation_errors);
    }

    public function populateByRequest(Request $request): static
    {
        return $request->isMethod('GET')
                ? $this->populateByQueryParams($request) : $this->populateByRequestBody($request);
    }

    public function populateByQueryParams(Request $request): static
    {
        return $this->populateByArray($request->query->all());
    }

    public function populateByRequestBody(Request $request): static
    {
        return $this->populateByArray($request->toArray());
    }

    public function populateByArray(array $data): static
    {
        $this->serializer->denormalize(
            $this->trimArrayValues($data),
            static::class,
            null,
            [
                AbstractNormalizer::OBJECT_TO_POPULATE => $this,
                AbstractNormalizer::IGNORED_ATTRIBUTES => ['request', 'validation_errors']
            ]
        );
        return $this;
    }

    public function getErrors(): array
    {
        return $this->validation_errors;
    }

    public function getErrorsWithoutKeys(): array
    {
        $result = [];
        foreach ($this->validation_errors as $key => $val) {
            $result[] = sprintf("%s: %s", $key, $val);
        }
        return $result;
    }

    public function getFirstError(): ?string
    {
        if (empty($this->validation_errors)) {
            return null;
        }

        return sprintf(
            "%s: %s",
            array_key_first($this->validation_errors),
            $this->validation_errors[array_key_first($this->validation_errors)]
        );
    }

    private function trimArrayValues(array $data): array
    {
        array_walk_recursive($data, function (&$value) {
            if (is_string($value)) {
                $value = trim($value);
            }
        });
        return $data;
    }
}
