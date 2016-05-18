<?php

namespace Rezzza\SymfonyRestApiJson\Tests\Units;

use mageekguy\atoum;

class Weblink extends atoum\test
{
    /**
     * @dataProvider validHeaders
     */
    public function test_it_parse_url($givenHeader, $expectedUrl)
    {
        $this
            ->given(
                $sutClass = $this->testedClass->getClass()
            )
            ->when(
                $sut = $sutClass::fromHeaderString($givenHeader)
            )
            ->then
                ->variable($sut->getUrl())
                    ->isEqualTo($expectedUrl)
        ;
    }

    public function validHeaders()
    {
        return [
            ['<http://google.fr>; rel="customer"', 'http://google.fr'],
            ['<mailto:coucou@google.com>; rel="friend"', 'mailto:coucou@google.com'],
            ['<tel:0666666666>; rel="contact"', 'tel:0666666666'],
            ['<fax:0666666666>; rel="contact"', 'fax:0666666666'],
        ];
    }

    public function test_it_replace_host()
    {
        $this
            ->given(
                $givenWeblink = $this->newTestedInstance('https://www.yahoo.fr/search?foo=bar#anchor', 'Customer', ['title' => 'hello you', 'description' => 'customer description'])
            )
            ->and(
                $givenHost = 'http://google.com',
                $sutClass = $this->testedClass->getClass()
            )
            ->when(
                $sut = $sutClass::fromWeblinkWithHost($givenWeblink, $givenHost)
            )
            ->then
                ->variable($sut->getUrl())
                    ->isEqualTo('http://google.com/search?foo=bar#anchor')
        ;
    }

    public function test_it_detects_wrong_url()
    {
        $this
            ->exception(function () {
                $sut = $this->newTestedInstance('http:/woot');
            })
                ->hasMessage('"http:/woot" is not a valid url')
        ;
    }

    public function test_it_parse_rel_attribute()
    {
        $this
            ->given(
                $givenHeader = '<http://google.fr>; rel="customer"',
                $sutClass = $this->testedClass->getClass()
            )
            ->when(
                $sut = $sutClass::fromHeaderString($givenHeader)
            )
            ->then
                ->variable($sut->getRel())
                    ->isEqualTo('customer')
        ;
    }

    public function test_it_parse_others_attributes()
    {
        $this
            ->given(
                $givenHeader = '<http://google.fr>; rel="customer"; title="my link";media="mobile"',
                $sutClass = $this->testedClass->getClass()
            )
            ->when(
                $sut = $sutClass::fromHeaderString($givenHeader)
            )
            ->then
                ->phpArray($sut->getAttributes())
                    ->isEqualTo([
                        'title' => 'my link',
                        'media' => 'mobile'
                    ])
        ;
    }

    public function test_it_is_related_to()
    {
        $this
            ->given(
                $sut = $this->newTestedInstance('http://google.fr', 'customer')
            )
            ->then
                ->boolean($sut->isRelatedTo('Customer'))
                    ->isTrue()
        ;
    }
}
