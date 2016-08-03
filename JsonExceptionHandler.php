<?php

namespace Rezzza\SymfonyRestApiJson;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use League\Tactician\Bundle\Middleware\InvalidCommandException;

/**
 * Directly translate supported exception into unified JSON response
 */
class JsonExceptionHandler
{
    private $exceptionHttpCodeMap;

    public function __construct(ExceptionHttpCodeMap $exceptionHttpCodeMap)
    {
        $this->exceptionHttpCodeMap = $exceptionHttpCodeMap;
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();
        $resolvedHttpCode = $this->exceptionHttpCodeMap->resolveHttpCodeFromException($exception);
        $response = null;

        if (null !== $resolvedHttpCode) {
            $response = new JsonResponse(['errors' => ['message' => $exception->getMessage()]], $resolvedHttpCode);
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

            $response = new JsonResponse(['errors' => $violationsPayload], 400);
        }

        if ($exception instanceof InvalidPayload) {
            $violationsPayload = [];

            foreach ($exception->getErrors() as $violation) {
                $violationsPayload[] = [
                    'parameter' => $violation['property'],
                    'message' => $violation['message']
                ];
            }

            $response = new JsonResponse(['errors' => $violationsPayload], 400);
        }

        if (null !== $response) {
            // Will stop the propagation according to symfony doc
            $event->setResponse($response);
        }
    }
}
