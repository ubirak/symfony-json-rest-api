<?php

namespace Rezzza\SymfonyRestApiJson;

class InvalidPayload extends \Exception
{
    private $errors;

    public static function withErrors($payload, $schema, array $errors)
    {
        $exception = new static(
            sprintf('Payload "%s" does not validate the schema "%s". %s errors found.', $payload, $schema, count($errors))
        );
        $exception->errors = $errors;

        return $exception;
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
