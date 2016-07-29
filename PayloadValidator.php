<?php

namespace Rezzza\SymfonyRestApiJson;

class PayloadValidator
{
    private $jsonSchemaTools;

    public function __construct(JsonSchemaTools $jsonSchemaTools)
    {
        $this->jsonSchemaTools = $jsonSchemaTools;
    }

    public function validate($payload, $jsonSchemaFilename)
    {
        if (false === file_exists($jsonSchemaFilename)) {
            throw new \UnexpectedValueException(sprintf('Cannot validate payload through "%s" json schema. File does not exist.', $jsonSchemaFilename));
        }

        $delegateValidator = $this->jsonSchemaTools->createValidator();
        $refResolver = $this->jsonSchemaTools->createRefResolver();

        $delegateValidator->check(
            json_decode($payload),
            $refResolver->resolve('file://' . realpath($jsonSchemaFilename))
        );

        if (!$delegateValidator->isValid()) {
            throw InvalidPayload::withErrors($payload, $jsonSchemaFilename, $delegateValidator->getErrors());
        }
    }
}
