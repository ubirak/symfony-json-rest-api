<?php

namespace Rezzza\SymfonyRestApiJson;

use JsonSchema\Validator;
use JsonSchema\RefResolver;

class PayloadValidator
{
    private $delegateValidator;

    private $refResolver;

    public function __construct(Validator $delegateValidator, RefResolver $refResolver)
    {
        $this->delegateValidator = $delegateValidator;
        $this->refResolver = $refResolver;
    }

    public function validate($payload, $jsonSchemaFilename)
    {
        if (false === file_exists($jsonSchemaFilename)) {
            throw new \UnexpectedValueException(sprintf('Cannot validate payload through "%s" json schema. File does not exist.', $jsonSchemaFilename));
        }

        $this->delegateValidator->check(
            json_decode($payload),
            $this->refResolver->resolve('file://' . realpath($jsonSchemaFilename))
        );

        if (!$this->delegateValidator->isValid()) {
            throw InvalidPayload::withErrors($payload, $jsonSchemaFilename, $this->delegateValidator->getErrors());
        }
    }
}
