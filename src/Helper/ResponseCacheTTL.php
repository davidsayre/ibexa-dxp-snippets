<?php

namespace App\Helper;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\EventListener\AbstractSessionListener;

class ResponseCacheTTL
{

    const DEFAULT_TTL = 600; // ten minutes

    /**
     * Call in controller function when you need to enable public with custom ttl
     * Any caching is better than nothing, espectially for search and form pages.
     * @param Response $response
     * @param $ttl
     * @return void
     */
    public static function setResponsePublicTTL(Response $response, $ttl = self::DEFAULT_TTL)
    {
        // failsafe
        if (!is_numeric($ttl)) {
            $ttl = self::DEFAULT_TTL;
        }
        $response->setPublic();
        $response->headers->set(AbstractSessionListener::NO_AUTO_CACHE_CONTROL_HEADER, 'true');
        $response->setTtl($ttl);
        $response->setMaxAge($ttl);
        $response->setSharedMaxAge($ttl);
    }
}

?>