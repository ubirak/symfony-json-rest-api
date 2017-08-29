<?php

namespace Rezzza\SymfonyRestApiJson;

use JsonSchema\Validator;
use JsonSchema\SchemaStorage;
use JsonSchema\Uri;

/**
 * Used as factory because JsonSchema\Validator and JsonSchema\SchemaStorage are not stateless
 */
class JsonSchemaTools
{
    public function createValidator()
    {
        return new Validator;
    }

    public function createSchemaStorage()
    {
        return new SchemaStorage(
            new Uri\UriRetriever(),
            new Uri\UriResolver()
        );
    }
}
