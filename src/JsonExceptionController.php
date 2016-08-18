<?php

namespace Rezzza\SymfonyRestApiJson;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Debug\Exception\FlattenException;

/**
 * Display uncatched exception in JSON
 * Should be used with as first argument of Symfony\Component\HttpKernel\EventListener\ExceptionListener
 */
class JsonExceptionController
{
    private $debug;

    private $showExceptionToken;

    public function __construct($debug, $showExceptionToken)
    {
        $this->debug = (bool) $debug;
        $this->showExceptionToken = $showExceptionToken;
    }

    public function showException(Request $request, FlattenException $exception)
    {
        $payload = [];

        if ($this->debug || $this->showExceptionToken === $request->headers->get('X-Show-Exception-Token')) {
            $payload = ['exception' => $exception->toArray()];
        }

        return new JsonResponse($payload, $exception->getStatusCode() ?: 500);
    }
}
