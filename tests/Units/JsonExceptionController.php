<?php

namespace Rezzza\SymfonyRestApiJson\Tests\Units;

use mageekguy\atoum;

class JsonExceptionController extends atoum\test
{
    private $faker;

    public function beforeTestMethod($testMethod)
    {
        $this->faker = \Faker\Factory::create();
    }

    public function test_it_returns_json_response_with_500_http_code()
    {
        $this
            ->given(
                $this->newTestedInstance($this->faker->boolean(), 'token')
            )
            ->when(
                $response = $this->testedInstance->showException(
                    $this->requestWithHeaders(),
                    new \Symfony\Component\Debug\Exception\FlattenException
                )
            )
            ->then
                ->object($response)
                    ->isInstanceOf('Symfony\Component\HttpFoundation\JsonResponse')
                ->integer($response->getStatusCode())
                    ->isEqualTo(500)
        ;
    }

    public function test_exception_payload_should_have_exception_details_in_debug()
    {
        $this
            ->given(
                $this->newTestedInstance(true, 'token'),
                $flattenException = new \Symfony\Component\Debug\Exception\FlattenException,
                $flattenException->setMessage('BOOM')
            )
            ->when(
                $response = $this->testedInstance->showException(
                    $this->requestWithHeaders(),
                    $flattenException
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
                $this->newTestedInstance(false, 'token')
            )
            ->when(
                $response = $this->testedInstance->showException(
                    $this->requestWithHeaders(),
                    new \Symfony\Component\Debug\Exception\FlattenException
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
                $this->newTestedInstance(false, 'token'),
                $flattenException = new \Symfony\Component\Debug\Exception\FlattenException,
                $flattenException->setMessage('BOOM')
            )
            ->when(
                $response = $this->testedInstance->showException(
                    $this->requestWithHeaders(['HTTP_X-Show-Exception-Token' => 'token']),
                    $flattenException
                )
            )
            ->then
                ->string($response->getContent())
                    ->isEqualTo('{"exception":[{"message":"BOOM","class":null,"trace":null}]}')
        ;
    }

    public function test_it_returns_exception_status_code_if_present()
    {
        $this
            ->given(
                $this->newTestedInstance(false, 'token'),
                $flattenException = new \Symfony\Component\Debug\Exception\FlattenException,
                $flattenException->setStatusCode(400)
            )
            ->when(
                $response = $this->testedInstance->showException(
                    $this->requestWithHeaders(),
                    $flattenException
                )
            )
            ->then
                ->integer($response->getStatusCode())
                    ->isEqualTo(400)
        ;
    }

    private function requestWithHeaders(array $headers = [])
    {
        return \Symfony\Component\HttpFoundation\Request::create('/test.json', 'GET', [], [], [], $headers, null);
    }
}
