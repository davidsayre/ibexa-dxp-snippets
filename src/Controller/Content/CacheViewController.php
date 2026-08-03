<?php


namespace App\Controller\Content;

use Ibexa\Bundle\Core\Controller;
use Ibexa\Core\MVC\Symfony\View\View;
use Ibexa\Core\Repository\Values\Content\Location;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\EventListener\AbstractSessionListener;

class CacheViewController extends Controller
{

    /**
     * Example: location_view.yaml
     *   frontpage:
     *      controller: App\Controller\Content\CacheViewController:index
     *      template: "@oncolink_user/frontpage/frontpage-full.html.twig"
     *      match:
     *          Identifier\ContentType: frontpage
     *      params:
     *          ttl: 3600
     *
     * @param View $view
     * @param Request $request
     * @return View
     */
    public function index(View $view, Request $request, ?Location $location): View
    {
        $ttl = 0; // 0 = ignore value and use default from configs
        if ($view->hasParameter('ttl')) {
            $ttl = $view->getParameter('ttl');
        }
        $response = new Response();
        if (is_object($request) && !$request->get('nocache')) {
            $this->setPublicResponseTTL($response, $ttl);
        }
        $view->setResponse($response);
        return $view;
    }

    protected function setPublicResponseTTL(Response $response, $ttl = 0)
    {
        if (empty($ttl) || $ttl === 0) {
            $ttl = $this->getParameter('httpcache_default_ttl');
        }
        $response->headers->set(AbstractSessionListener::NO_AUTO_CACHE_CONTROL_HEADER, 'true');
        $response->setPublic();
        $response->setTtl($ttl);
        $response->setMaxAge($ttl);
        $response->setSharedMaxAge($ttl);
    }

}