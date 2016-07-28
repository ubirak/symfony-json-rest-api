<?php

namespace Rezzza\SymfonyRestApiJson;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use League\Tactician\Bundle\Middleware\InvalidCommandException;

class JsonExceptionHandler
{
    private $debug;

    private $showExceptionToken;

    private $exceptionHttpCodeMap;

    public function __construct($debug, $showExceptionToken, ExceptionHttpCodeMap $exceptionHttpCodeMap)
    {
        $this->debug = (bool) $debug;
        $this->showExceptionToken = $showExceptionToken;
        $this->exceptionHttpCodeMap = $exceptionHttpCodeMap;
    }

    /**
     * Please note we cannot typehint $exception because of Flatten exception
     */
    public function handleExceptionOfRequest($exception, Request $request)
    {
        $exceptionIsFlatten = $exception instanceof FlattenException;
        if (false === ($exception instanceof \Exception || $exceptionIsFlatten)) {
            return;
        }

        $payload = [];

        if ($this->debug || $this->showExceptionToken === $request->headers->get('X-Show-Exception-Token')) {
            $exceptionFlatten = $exceptionIsFlatten ? $exception : FlattenException::create($exception);
            $payload = ['exception' => $exceptionFlatten->toArray()];
        }

        if ($exceptionIsFlatten) {
            return new JsonResponse($payload);
        }

        $resolvedHttpCode = $this->exceptionHttpCodeMap->resolveHttpCodeFromException($exception);

        if (null !== $resolvedHttpCode) {
            return new JsonResponse($payload + ['errors' => ['message' => $exception->getMessage()]], $resolvedHttpCode);
        }

        if ($exception instanceof InvalidCommandException) {
            $converter = new CamelCaseToSnakeCaseNameConverter;
            $violationsPayload = [];

            foreach ($exception->getViolations() as $violation) {
                $violationsPayload[] = [
                    'parameter' => $converter->normalize($violation->getPropertyPath()),
                    'message' => $violation->getMessage()
                ];
            }

            return new JsonResponse($payload + ['errors' => $violationsPayload], 406);
        }

        if ($exception instanceof InvalidPayload) {
            $violationsPayload = [];

            foreach ($exception->getErrors() as $violation) {
                $violationsPayload[] = [
                    'parameter' => $violation['property'],
                    'message' => $violation['message']
                ];
            }

            return new JsonResponse($payload + ['errors' => $violationsPayload], 400);
        }

        throw $exception;
    }
}
