<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class ContentLookupController extends AbstractController {

    protected $locationService;
    protected $contentService;

    /**
     * @param LocationService $locationService
     * @param ContentService $contentService
     */
    public function __construct(LocationService $locationService, ContentService $contentService) {
        $this->locationService = $locationService;
        $this->contentService = $contentService;
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Request $request) {
        return $this->render("@standard/admin/content_lookup/dashboard.html.twig");
    }

    /**
     * @param Request $request
     * @param $content
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function redirectByContentId (Request $request, $content = null) {
        $contentId = $request->query->get('content');
        if(is_numeric($contentId)) {
            try {
                $contentId = (int) $contentId;
                /** @var Content $content */
                $content = $this->contentService->loadContent($contentId);
                $location = $this->locationService->loadLocation($content->contentInfo->mainLocationId);
                return $this->redirect("/view/content/" . $contentId . "/full/1/" . $location->id);
            } catch (\Exception $e) {

            }
        }
        return $this->redirect("/admin/?lookup_failed");
    }

    /**
     * @param Request $request
     * @param $location
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function redirectbyLocationId (Request $request, $location = null) {
        $locationId = $request->query->get('location');
        if(is_numeric($locationId)) {
            try{
                $locationId = (int) $locationId;
                /** @var Location $location */
                $location = $this->locationService->loadLocation($locationId);
                return $this->redirect("/view/content/".$location->contentId."/full/1/".$location->id);
            } catch(\Exception $e) {

            }
        }

        return $this->redirect("/?lookup_failed");
    }
}
?>