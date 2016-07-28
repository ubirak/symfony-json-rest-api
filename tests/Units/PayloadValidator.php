<?php

namespace Rezzza\SymfonyRestApiJson\Tests\Units;

use mageekguy\atoum;

class PayloadValidator extends atoum
{
    public function test_validation_should_be_delegated_to_internal_validator()
    {
        $this
            ->given(
                $validator = $this->mockJsonSchemaValidator(),
                $this->calling($validator)->check = null,
                $refResolver = $this->mockJsonSchemaRefResolver(),
                $this->calling($refResolver)->resolve = 'resolvedJsonSchema',
                $this->newTestedInstance($validator, $refResolver)
            )
            ->when(
                $this->testedInstance->validate('{"json"}', __DIR__.'/../Fixtures/mySchema.json')
            )
            ->then
                ->mock($validator)
                    ->call('check')
                    ->withArguments('{"json"}', 'resolvedJsonSchema')
                    ->once()
        ;
    }

    public function test_unknown_json_schema_lead_to_exception()
    {
        $this
            ->given(
                $this->newTestedInstance($this->mockJsonSchemaValidator(), $this->mockJsonSchemaRefResolver())
            )
            ->exception(function () {
                $this->testedInstance->validate('{"json"}', 'hello.json');
            })
                ->isInstanceOf(\UnexpectedValueException::class)
        ;
    }

    public function test_invalid_internal_validation_lead_to_exception()
    {
        $this
            ->given(
                $validator = $this->mockJsonSchemaValidator(),
                $this->calling($validator)->check = null,
                $this->calling($validator)->isValid = false,
                $this->calling($validator)->getErrors = ['error1', 'error2'],
                $refResolver = $this->mockJsonSchemaRefResolver(),
                $this->calling($refResolver)->resolve = 'resolvedJsonSchema',
                $this->newTestedInstance($validator, $refResolver)
            )
            ->exception(function () {
                $this->testedInstance->validate('{"json"}', __DIR__.'/../Fixtures/mySchema.json');
            })
                ->isInstanceOf(\Rezzza\SymfonyRestApiJson\InvalidPayload::class)
                ->phpArray($this->exception->getErrors())
                    ->isEqualTo(['error1', 'error2'])
        ;
    }

    private function mockJsonSchemaValidator()
    {
        $this->mockGenerator->orphanize('__construct');

        return new \mock\JsonSchema\Validator;
    }

    private function mockJsonSchemaRefResolver()
    {
        $this->mockGenerator->orphanize('__construct');

        return new \mock\JsonSchema\RefResolver;
    }
}
