<?php

namespace App\Controller\Content;

use Ibexa\Bundle\Core\Controller;
use Ibexa\Core\MVC\Symfony\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * This is a 'Base' controller that other 'View' controllers would use.
 * There are several helpful functions
 */

class BaseContentViewPaginatedController extends Controller {

    /**
     * Build view params from the formIdentifier in the request
     * @param \Ibexa\Core\MVC\Symfony\View\View $view
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    protected function supplyContentViewFormPagination(View $view, Request $request, $formIdentifier): void
    {
        $page = $request->query->get('page');

        $routeParams = $request->get('_route_params');

        if(!empty($formIdentifier)) {
            $routeFormParams = array();
            // augment with custom fields (if not empty)
            $formParams = $request->get($formIdentifier);

            if(is_array($formParams)) {
                foreach($formParams as $formParam => $formParamValue) {
                    // NOTE: $formParamValue could also be an array locations[][0]=123 url syntax
                    if(!empty($formParamValue)) {
                        $routeFormParams[$formParam] = $formParamValue;
                    }
                }
            }
            // only merge if populated
            if(count($routeFormParams)) {
                $routeParams[$formIdentifier] = $routeFormParams;
            }
        }

        $view->addParameters([
            'pagination_params' => [
                'route_name' => $request->get('_route'), // ibexa.view
                'route_params' => $routeParams,
                'page' => $page ?? 1
            ],
        ]);
    }


    /**
     * Caching use case from a Controller extending this 'Base' class:
     *   if ($form->isSubmitted() ) {
     *       // make private
     *       $this->setViewPrivate($view);
     *   } else {
     *       // default is 30 seconds for unsubmited
     *       $this->setViewPublicShort($view,30);
     *   }
     */

    /**
     * Call in a controller function when you need to disable cache.
     * Use case is form submitted
     * @param $view
     * @return void
     */
    protected function setViewPrivate($view) {
        // no caching
        $response = new Response();
        $response->setPrivate();
        $response->setSharedMaxAge(0);
        $view->setResponse($response);
    }

    /**
     * Call in controller function when you need to enable public with short ttl
     * Any caching is better than nothing, espectially for search and form pages.
     * @param $view
     * @param $ttl
     * @return void
     */
    protected function setViewPublicShort($view, $ttl = 30) {
        $response = new Response();
        $response->setPublic();
        $response->setSharedMaxAge($ttl);
        $view->setResponse($response);
    }
}

?>