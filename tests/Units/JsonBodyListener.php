<?php

namespace Rezzza\SymfonyRestApiJson\Tests\Units;

use mageekguy\atoum;

class JsonBodyListener extends atoum\test
{
    /**
     * @dataProvider allowedHttpMethod
     */
    public function test_it_convert_raw_json_to_request_parameters($method)
    {
        $this
            ->given(
                $request = $this->requestWithContent($method, 'application/json', '{"foo": "bar"}'),
                $mockEvent = $this->eventOccuredByRequest($request),
                $this->newTestedInstance($this->mockPayloadValidator())
            )
            ->when(
                $this->testedInstance->onKernelRequest($mockEvent)
            )
            ->then
                ->phpArray($request->request->all())
                    ->isEqualTo(['foo' => 'bar'])
        ;
    }

    public function allowedHttpMethod()
    {
        return [
            ['POST'],
            ['PUT'],
            ['PATCH'],
            ['DELETE'],
            ['LINK'],
            ['UNLINK'],
        ];
    }

    /**
     * @dataProvider unsupportedHttpMethod
     */
    public function test_it_stop_for_unsupported_method($method)
    {
        $this
            ->given(
                $request = $this->requestWithContent($method, 'application/json', '{"foo": "bar"}'),
                $mockEvent = $this->eventOccuredByRequest($request),
                $this->newTestedInstance($this->mockPayloadValidator())
            )
            ->when(
                $this->testedInstance->onKernelRequest($mockEvent)
            )
            ->then
                ->phpArray($request->request->all())
                    ->isEmpty()
        ;
    }

    public function unsupportedHttpMethod()
    {
        return [
            ['GET'],
            ['OPTIONS'],
        ];
    }

    /**
     * @dataProvider unsupportedContentType
     */
    public function test_it_accept_only_json_content_type($contentType)
    {
        $this
            ->given(
                $request = $this->requestWithContent('POST', $contentType, '{"foo": "bar"}'),
                $mockEvent = $this->eventOccuredByRequest($request),
                $this->newTestedInstance($this->mockPayloadValidator())
            )
            ->when(
                $this->testedInstance->onKernelRequest($mockEvent)
            )
            ->then
                ->phpArray($request->request->all())
                    ->isEmpty()
        ;
    }

    public function unsupportedContentType()
    {
        return [
            ['text/html'],
            ['text/css'],
            ['application/javascript'],
            ['text/json'],
            ['application/pdf'],
        ];
    }

    public function test_it_need_content()
    {
        $this
            ->given(
                $request = $this->requestWithContent('POST', 'application/json', null),
                $mockEvent = $this->eventOccuredByRequest($request),
                $this->newTestedInstance($this->mockPayloadValidator())
            )
            ->when(
                $this->testedInstance->onKernelRequest($mockEvent)
            )
            ->then
                ->phpArray($request->request->all())
                    ->isEmpty()
        ;
    }

    public function test_it_need_valid_json()
    {
        $this
            ->given(
                $request = $this->requestWithContent('POST', 'application/json', '{"foo'),
                $mockEvent = $this->eventOccuredByRequest($request)
            )
            ->exception(function () use ($mockEvent) {
                $this->newTestedInstance($this->mockPayloadValidator());
                $this->testedInstance->onKernelRequest($mockEvent);
            })
            ->isInstanceOf('Symfony\Component\HttpKernel\Exception\BadRequestHttpException')
        ;
    }

    public function test_it_try_to_validate_payload_when_jsonSchema_is_present()
    {
        $this
            ->given(
                $request = $this->requestWithContent('POST', 'application/json', '{"foo": "bar"}'),
                $request->attributes->set('_jsonSchema', ['request' => 'mySchema.json']),
                $mockEvent = $this->eventOccuredByRequest($request),
                $mockPayloadValidator = $this->mockPayloadValidator(),
                $this->calling($mockPayloadValidator)->validate = null,
                $this->newTestedInstance($mockPayloadValidator)
            )
            ->when(
                $this->testedInstance->onKernelRequest($mockEvent)
            )
            ->then
                ->mock($mockPayloadValidator)
                    ->call('validate')
                    ->withArguments('{"foo": "bar"}', 'mySchema.json')
                    ->once()
        ;
    }

    private function requestWithContent($method, $contentType, $content)
    {
        return \Symfony\Component\HttpFoundation\Request::create(
            '/test.json',
            $method,
            [],
            [],
            [],
            ['CONTENT_TYPE' => $contentType],
            $content
        );
    }

    private function eventOccuredByRequest($request)
    {
        $this->mockGenerator->orphanize('__construct');
        $mockEvent = new \mock\Symfony\Component\HttpKernel\Event\GetResponseEvent;
        $this->calling($mockEvent)->getRequest = $request;

        return $mockEvent;
    }

    private function mockPayloadValidator()
    {
        $this->mockGenerator->orphanize('__construct');

        return new \mock\Rezzza\SymfonyRestApiJson\PayloadValidator;
    }
}
