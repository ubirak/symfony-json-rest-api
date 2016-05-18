<?php

namespace Rezzza\SymfonyRestApiJson;

/**
 * Parse Weblink header according to the RFC https://tools.ietf.org/html/rfc5988#section-5.5
 *
 * We introduce a specific support to url with tel and fax scheme (see https://tools.ietf.org/html/rfc2806) 
 * as FILTER_VALIDATE_URL does not handle it.
 * But be aware that we don't validate the following phonenumber, it's your domain responsability
 */
class Weblink
{
    /**
     * string
     */
    private $url;

    /**
     * string
     */
    private $rel;

    /**
     * array
     */
    private $attributes;

    public function __construct($url, $rel = null, $attributes = [])
    {
        $scheme = parse_url($url, PHP_URL_SCHEME);
        $extendedSchemes = ['tel', 'fax'];

        if (false === in_array($scheme, $extendedSchemes) && !filter_var($url, FILTER_VALIDATE_URL)) {
            throw new \LogicException(sprintf('"%s" is not a valid url', $url));
        }

        $this->url = $url;
        $this->rel = $rel;
        $this->attributes = $attributes;
    }

    /**
     * @param Weblink $weblink
     * @param string  $host    complete base url (scheme+host) eg http://sub.domain.tld
     *
     */
    public static function fromWeblinkWithHost(Weblink $weblink, $host)
    {
        $urlParts = parse_url($weblink->getUrl());
        unset($urlParts['host']);
        unset($urlParts['scheme']);

        $urlWithHost = http_build_url($host, $urlParts);

        return new static($urlWithHost, $weblink->getRel(), $weblink->getAttributes());
    }

    /**
     * @param string $header
     */
    public static function fromHeaderString($header)
    {
        $parts = explode(';', $header);
        $url = null;
        $rel = null;
        $attributes = [];

        foreach ($parts as $part) {
            if (preg_match('/<(?P<url>[^>]*)>/', $part, $matches)) {
                // Try to find url : <http://url.scheme>
                $url = $matches['url'];
            } elseif (preg_match('/(?P<key>[A-Za-z0-9]*)="(?P<value>[^"]*)"/', $part, $matches)) {
                // Try to find attributes : rel="customer" or target="http://google.fr"
                if ($matches['key'] == 'rel') {
                    $rel = $matches['value'];
                } else {
                    $attributes[$matches['key']] = $matches['value'];
                }
            }
        }

        return new static($url, $rel, $attributes);
    }

    public function isRelatedTo($relationName)
    {
        return strtolower($relationName) === strtolower($this->rel);
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function getRel()
    {
        return $this->rel;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }
}
