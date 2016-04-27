<?php

namespace Rezzza\SymfonyRestApiJson;

use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Allow to pass JSON raw as request content
 */
class JsonBodyListener
{
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $method = $request->getMethod();

        if (count($request->request->all())
            || !in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE', 'LINK', 'UNLINK'])
        ) {
            return;
        }

        $contentType = $request->headers->get('Content-Type');

        $format = null === $contentType
            ? $request->getRequestFormat()
            : $request->getFormat($contentType);

        if ($format !== 'json') {
            return;
        }

        $content = $request->getContent();

        if (!empty($content)) {
            $data = @json_decode($content, true);

            if (!is_array($data)) {
                throw new BadRequestHttpException('Invalid ' . $format . ' message received');
            }

            $request->request = new ParameterBag($data);
        }
    }
}
