<?php

namespace Rezzza\SymfonyRestApiJson\Tests\Units;

use mageekguy\atoum;

class JsonExceptionHandler extends atoum\test
{
    private $faker;

    public function beforeTestMethod($testMethod)
    {
        $this->faker = \Faker\Factory::create();
    }
    public function test_it_handles_only_exception()
    {
        $this
            ->given(
                $this->newTestedInstance(
                    $this->faker->boolean(),
                    'token',
                    new \Rezzza\SymfonyRestApiJson\ExceptionHttpCodeMap
                )
            )
            ->when(
                $result = $this->testedInstance->handleExceptionOfRequest(new \stdClass, $this->requestWithHeaders())
            )
            ->then
                ->variable($result)
                    ->isNull()
        ;
    }

    public function test_uncaught_exception_should_be_thrown()
    {
        $this
            ->given(
                $this->newTestedInstance(
                    $this->faker->boolean(),
                    'token',
                    new \Rezzza\SymfonyRestApiJson\ExceptionHttpCodeMap
                )
            )
            ->exception(function () {
                $this->testedInstance->handleExceptionOfRequest(new \Exception('boum'), $this->requestWithHeaders());
            })
                ->hasMessage('boum')
                ->isInstanceOf('Exception')
        ;
    }

    public function test_flatten_exception_returns_json_response()
    {
        $this
            ->given(
                $this->newTestedInstance(
                    $this->faker->boolean(),
                    'token',
                    new \Rezzza\SymfonyRestApiJson\ExceptionHttpCodeMap
                )
            )
            ->when(
                $response = $this->testedInstance->handleExceptionOfRequest(
                    new \Symfony\Component\Debug\Exception\FlattenException,
                    $this->requestWithHeaders()
                )
            )
            ->then
                ->object($response)
                    ->isInstanceOf('Symfony\Component\HttpFoundation\JsonResponse')
        ;
    }

    public function test_exception_should_have_exception_details_in_debug()
    {
        $this
            ->given(
                $this->newTestedInstance(
                    true,
                    'token',
                    new \Rezzza\SymfonyRestApiJson\ExceptionHttpCodeMap
                ),
                $flattenException = new \Symfony\Component\Debug\Exception\FlattenException,
                $flattenException->setMessage('BOOM')
            )
            ->when(
                $response = $this->testedInstance->handleExceptionOfRequest(
                    $flattenException,
                    $this->requestWithHeaders()
                )
            )
            ->then
                ->string($response->getContent())
                    ->isEqualTo('{"exception":[{"message":"BOOM","class":null,"trace":null}]}')
        ;
    }

    public function test_exception_should_not_have_exception_details_in_not_debug()
    {
        $this
            ->given(
                $this->newTestedInstance(
                    false,
                    'token',
                    new \Rezzza\SymfonyRestApiJson\ExceptionHttpCodeMap
                )
            )
            ->when(
                $response = $this->testedInstance->handleExceptionOfRequest(
                    new \Symfony\Component\Debug\Exception\FlattenException,
                    $this->requestWithHeaders()
                )
            )
            ->then
                ->string($response->getContent())
                    ->isEqualTo('[]')
        ;
    }

    public function test_exception_should_have_exception_details_if_header_provided_even_if_not_debug()
    {
        $this
            ->given(
                $this->newTestedInstance(
                    false,
                    'token',
                    new \Rezzza\SymfonyRestApiJson\ExceptionHttpCodeMap
                ),
                $flattenException = new \Symfony\Component\Debug\Exception\FlattenException,
                $flattenException->setMessage('BOOM')
            )
            ->when(
                $response = $this->testedInstance->handleExceptionOfRequest(
                    $flattenException,
                    $this->requestWithHeaders(['HTTP_X-Show-Exception-Token' => 'token'])
                )
            )
            ->then
                ->string($response->getContent())
                    ->isEqualTo('{"exception":[{"message":"BOOM","class":null,"trace":null}]}')
        ;
    }

    public function test_mapped_exception_should_return_json_response_with_http_code_resolved()
    {
        $this
            ->given(
                $map = new \mock\Rezzza\SymfonyRestApiJson\ExceptionHttpCodeMap,
                $this->calling($map)->resolveHttpCodeFromException = 404,
                $this->newTestedInstance(false, 'token', $map)
            )
            ->when(
                $response = $this->testedInstance->handleExceptionOfRequest(
                    new \Exception('BIM'),
                    $this->requestWithHeaders()
                )
            )
            ->then
                ->string($response->getContent())
                    ->isEqualTo('{"errors":{"message":"BIM"}}')
        ;
    }

    public function test_it_supports_invalid_command_exception()
    {
        $this
            ->given(
                $this->newTestedInstance(false, 'token', new \Rezzza\SymfonyRestApiJson\ExceptionHttpCodeMap),
                $exception = new \mock\League\Tactician\Bundle\Middleware\InvalidCommandException,
                $violations = new \mock\Symfony\Component\Validator\ConstraintViolationList([
                    $this->mockViolation('username', 'Username invalid'),
                    $this->mockViolation('password', 'password invalid'),
                ]),
                $this->calling($exception)->getViolations = $violations
            )
            ->when(
                $response = $this->testedInstance->handleExceptionOfRequest(
                    $exception,
                    $this->requestWithHeaders()
                )
            )
            ->then
                ->string($response->getContent())
                    ->isEqualTo('{"errors":[{"parameter":"username","message":"Username invalid"},{"parameter":"password","message":"password invalid"}]}')
        ;
    }

    public function test_it_supports_invalid_payload_exception()
    {
        $this
            ->given(
                $this->newTestedInstance(false, 'token', new \Rezzza\SymfonyRestApiJson\ExceptionHttpCodeMap),
                $exception = new \mock\Rezzza\SymfonyRestApiJson\InvalidPayload,
                $this->calling($exception)->getErrors = [
                    ['property' => 'username', 'message' => 'Username invalid'],
                    ['property' => 'password', 'message' => 'password invalid']
                ]
            )
            ->when(
                $response = $this->testedInstance->handleExceptionOfRequest(
                    $exception,
                    $this->requestWithHeaders()
                )
            )
            ->then
                ->string($response->getContent())
                    ->isEqualTo('{"errors":[{"parameter":"username","message":"Username invalid"},{"parameter":"password","message":"password invalid"}]}')
                ->integer($response->getStatusCode())
                    ->isEqualTo(400)
        ;
    }

    private function requestWithHeaders(array $headers = [])
    {
        return \Symfony\Component\HttpFoundation\Request::create('/test.json', 'GET', [], [], [], $headers, null);
    }

    private function mockViolation($property, $message)
    {
        $this->mockGenerator->orphanize('__construct');
        $violation = new \mock\Symfony\Component\Validator\ConstraintViolation;
        $this->calling($violation)->getPropertyPath = $property;
        $this->calling($violation)->getMessage = $message;

        return $violation;
    }
}
