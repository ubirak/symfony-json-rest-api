<?php

namespace Rezzza\SymfonyRestApiJson;

use JsonSchema\Validator;
use JsonSchema\RefResolver;

/**
 * Used as factory because JsonSchema\Validator and JsonSchema\RefResolver are not stateless
 */
class JsonSchemaTools
{
    public function createValidator()
    {
        return new Validator;
    }

    public function createRefResolver()
    {
        return new RefResolver;
    }
}
