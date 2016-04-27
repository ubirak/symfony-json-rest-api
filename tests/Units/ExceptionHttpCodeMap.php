<?php

namespace Rezzza\SymfonyRestApiJson\Tests\Units;

use mageekguy\atoum;

class ExceptionHttpCodeMap extends atoum\test
{
    public function test_it_returns_http_code_mapped_on_exception_class()
    {
        $this
            ->given(
                $this->newTestedInstance(['Rezzza\SymfonyRestApiJson\Tests\Fixtures\MyException' => 404])
            )
            ->when(
                $httpCode = $this->testedInstance->resolveHttpCodeFromException(new \Rezzza\SymfonyRestApiJson\Tests\Fixtures\MyException)
            )
            ->then
                ->variable($httpCode)
                    ->isEqualTo(404)
        ;
    }

    public function test_it_returns_http_code_mapped_on_exception_class_even_for_child_class()
    {
        $this
            ->given(
                $this->newTestedInstance(['Rezzza\SymfonyRestApiJson\Tests\Fixtures\MyException' => 404])
            )
            ->when(
                $httpCode = $this->testedInstance->resolveHttpCodeFromException(new \Rezzza\SymfonyRestApiJson\Tests\Fixtures\MyOtherException)
            )
            ->then
                ->variable($httpCode)
                    ->isEqualTo(404)
        ;
    }


    public function test_it_returns_null_when_no_mapped_http_code_found()
    {
        $this
            ->given(
                $this->newTestedInstance(['LogicException' => 404])
            )
            ->when(
                $httpCode = $this->testedInstance->resolveHttpCodeFromException(new \Rezzza\SymfonyRestApiJson\Tests\Fixtures\MyException)
            )
            ->then
                ->variable($httpCode)
                    ->isNull()
        ;
    }
}
