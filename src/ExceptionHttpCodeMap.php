<?php

namespace Rezzza\SymfonyRestApiJson;

/**
 * Map exception class to Http code
 */
class ExceptionHttpCodeMap
{
    private $httpCodeMap;

    public function __construct(array $httpCodeMap = [])
    {
        $this->httpCodeMap = $httpCodeMap;
    }

    public function resolveHttpCodeFromException(\Exception $exception)
    {
        $class = get_class($exception);

        foreach ($this->httpCodeMap as $exceptionClass => $httpCode) {
            if ($class === $exceptionClass || is_subclass_of($class, $exceptionClass)) {
                return $httpCode;
            }
        }

        return null;
    }
}
