<?php

namespace Rezzza\SymfonyRestApiJson\Tests\Units;

use mageekguy\atoum;

class JsonExceptionHandler extends atoum\test
{
    public function test_uncaught_exception_should_not_alter_event_response()
    {
        $this
            ->given(
                $this->newTestedInstance(
                    new \Rezzza\SymfonyRestApiJson\ExceptionHttpCodeMap
                )
            )
            ->when(
                $event = $this->dispatchException(new \Exception('boum')),
                $this->testedInstance->onKernelException($event)
            )
            ->then
                ->variable($event->getResponse())
                    ->isNull()
        ;
    }

    public function test_mapped_exception_should_return_json_response_with_http_code_resolved()
    {
        $this
            ->given(
                $map = new \mock\Rezzza\SymfonyRestApiJson\ExceptionHttpCodeMap,
                $this->calling($map)->resolveHttpCodeFromException = 404,
                $this->newTestedInstance($map),
                $event = $this->dispatchException(new \Exception('BIM'))
            )
            ->when(
                $this->testedInstance->onKernelException($event)
            )
            ->then
                ->string($event->getResponse()->getContent())
                    ->isEqualTo('{"errors":{"message":"BIM"}}')
        ;
    }

    public function test_it_supports_invalid_command_exception()
    {
        $this
            ->given(
                $this->newTestedInstance(new \Rezzza\SymfonyRestApiJson\ExceptionHttpCodeMap),
                $exception = new \mock\League\Tactician\Bundle\Middleware\InvalidCommandException,
                $violations = new \mock\Symfony\Component\Validator\ConstraintViolationList([
                    $this->mockViolation('username', 'Username invalid'),
                    $this->mockViolation('password', 'password invalid'),
                ]),
                $this->calling($exception)->getViolations = $violations,
                $event = $this->dispatchException($exception)
            )
            ->when(
                $this->testedInstance->onKernelException($event)
            )
            ->then
                ->string($event->getResponse()->getContent())
                    ->isEqualTo('{"errors":[{"parameter":"username","message":"Username invalid"},{"parameter":"password","message":"password invalid"}]}')
                ->integer($event->getResponse()->getStatusCode())
                    ->isEqualTo(400)
        ;
    }

    public function test_it_supports_invalid_payload_exception()
    {
        $this
            ->given(
                $this->newTestedInstance(new \Rezzza\SymfonyRestApiJson\ExceptionHttpCodeMap),
                $exception = new \mock\Rezzza\SymfonyRestApiJson\InvalidPayload,
                $this->calling($exception)->getErrors = [
                    ['property' => 'username', 'message' => 'Username invalid'],
                    ['property' => 'password', 'message' => 'password invalid']
                ],
                $event = $this->dispatchException($exception)
            )
            ->when(
                $this->testedInstance->onKernelException($event)
            )
            ->then
                ->string($event->getResponse()->getContent())
                    ->isEqualTo('{"errors":[{"parameter":"username","message":"Username invalid"},{"parameter":"password","message":"password invalid"}]}')
                ->integer($event->getResponse()->getStatusCode())
                    ->isEqualTo(400)
        ;
    }

    private function dispatchException(\Exception $exception)
    {
        $this->mockGenerator->orphanize('__construct');
        $event = new \mock\Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
        $this->calling($event)->getException = $exception;

        return $event;
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
