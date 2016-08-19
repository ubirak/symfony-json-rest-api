<?php

namespace Rezzza\SymfonyRestApiJson;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class LinkRequestListener
{
    /**
     * @param GetResponseEvent $event event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$event->getRequest()->headers->has('link')) {
            return;
        }

        try {
            $event->getRequest()->attributes->set(
                'links',
                $this->parseWeblink(
                    $event->getRequest()->headers->get('link')
                )
            );
        } catch (\Exception $e) {
            throw new BadRequestHttpException('Invalid link header provided.', $e);
        }
    }

    /*
     * Due to limitations, multiple same-name headers are sent as comma
     * separated values.
     *
     * This breaks those headers into Link headers following the format
     * http://tools.ietf.org/html/rfc2068#section-19.6.2.4
     */
    private function parseWeblink($linkHeader)
    {
        $links = [];

        while (preg_match('/^((?:[^"]|"[^"]*")*?),/', $linkHeader, $matches)) {
            $linkHeader  = trim(substr($linkHeader, strlen($matches[0])));
            $links[] = Weblink::fromHeaderString($matches[1]);
        }

        if ($linkHeader) {
            $links[] = Weblink::fromHeaderString($linkHeader);
        }

        return $links;
    }
}
