<?php


namespace App\Controller\Content;

use Ibexa\Bundle\Core\Controller;
use Ibexa\Core\MVC\Symfony\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\EventListener\AbstractSessionListener;

class CacheViewController extends Controller {

    const DEFAULT_TTL = 300;

    /**
     * Example: location_view.yaml
     *   frontpage:
     *      controller: App\Controller\Content\CacheViewController:index
     *      template: "@standard/frontpage/frontpage-full.html.twig"
     *      match:
     *          Identifier\ContentType: frontpage
     *      params:
     *          ttl: 300
     *
     * @param View $view
     * @param Request $request
     * @return View
     */
    public function index(View $view, Request $request) {
        $ttl = $view->getParameter('ttl');
        if(empty($ttl)) {
            $ttl = self::DEFAULT_TTL;
        }
        $response = new Response();
        $response->headers->set(AbstractSessionListener::NO_AUTO_CACHE_CONTROL_HEADER, 'true');
        if (is_object($request) && !$request->get('nocache')) {
            $response->setCache([
                'public' => true,
                'private' => false,
                'must_revalidate' => false,
                'max_age' => $ttl,
            ]);
        }
        $view->setResponse($response);
        return $view;
    }
}
