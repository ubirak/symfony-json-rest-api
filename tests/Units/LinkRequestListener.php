<?php

namespace Rezzza\SymfonyRestApiJson\Tests\Units;

use mageekguy\atoum;
use Rezzza\SymfonyRestApiJson\Weblink;

class LinkRequestListener extends atoum
{
    public function test_it_changes_only_request_with_link_header()
    {
        $this
            ->given(
                $mockRequest = new \mock\Symfony\Component\HttpFoundation\Request,
                $mockKernelEvent = $this->mockKernelEventWithRequest($mockRequest),
                $sut = $this->newTestedInstance
            )
            ->when(
                $sut->onKernelRequest($mockKernelEvent)
            )
            ->then
                ->integer($mockRequest->attributes->count())->isEqualTo(0)
        ;
    }

    public function test_it_converts_simple_link_header_in_one_links_attribute()
    {
        $this
            ->given(
                $mockRequest = new \mock\Symfony\Component\HttpFoundation\Request,
                $mockRequest->headers->set('link', '<http://google.com/item/16789>; rel="copain"'),
                $mockKernelEvent = $this->mockKernelEventWithRequest($mockRequest),
                $sut = $this->newTestedInstance
            )
            ->when(
                $sut->onKernelRequest($mockKernelEvent)
            )
            ->then
                ->phpArray($mockRequest->attributes->all())
                    ->isEqualTo(['links' => [new Weblink('http://google.com/item/16789', 'copain')]])
        ;
    }

    public function test_it_converts_multiple_link_header_in_one_links_attribute()
    {
        $this
            ->given(
                $mockRequest = new \mock\Symfony\Component\HttpFoundation\Request,
                $mockRequest->headers->set('link', '<http://google.com/item/16789>; rel="copain", <http://google.com/item/hjuI89>; rel="copain"'),
                $mockKernelEvent = $this->mockKernelEventWithRequest($mockRequest),
                $sut = $this->newTestedInstance
            )
            ->when(
                $sut->onKernelRequest($mockKernelEvent)
            )
            ->then
                ->phpArray($mockRequest->attributes->all())
                    ->isEqualTo(
                        ['links' => [
                            new Weblink('http://google.com/item/16789', 'copain'),
                            new Weblink('http://google.com/item/hjuI89', 'copain'),
                        ]]
                    )
        ;
    }

    public function test_invalid_link_header_lead_to_bad_request_exception()
    {
        $this
            ->given(
                $mockRequest = new \mock\Symfony\Component\HttpFoundation\Request,
                $mockRequest->headers->set('link', '<http://google.com/item/16789; rel="copain", <http://google.com/item/hjuI89>; rel="copain"'),
                $mockKernelEvent = $this->mockKernelEventWithRequest($mockRequest)
            )
            ->exception(function () use ($mockKernelEvent) {
                $sut = $this->newTestedInstance;
                $sut->onKernelRequest($mockKernelEvent);
            })
                ->isInstanceOf('Symfony\Component\HttpKernel\Exception\BadRequestHttpException')
        ;
    }

    private function mockKernelEventWithRequest($request)
    {
        $this->mockGenerator->orphanize('__construct');
        $mockKernelEvent = new \mock\Symfony\Component\HttpKernel\Event\GetResponseEvent;
        $this->calling($mockKernelEvent)->getRequest = $request;

        return $mockKernelEvent;
    }
}
