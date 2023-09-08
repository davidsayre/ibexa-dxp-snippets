<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


/**
 * @Route("/content_lookup", name="admin_content_lookup")
 */
class ContentLookupController extends AbstractController {

    protected $locationService;
    protected $contentService;

    public function __construct(LocationService $locationService, ContentService $contentService) {
        $this->locationService = $locationService;
        $this->contentService = $contentService;
    }

    /**
     * @Route("/", name="_dashboard")
     */
    public function ContentLookupDashboard(Request $request) {
        return $this->render("@standard/content_lookup/dashboard.html.twig");
    }

    /**
     * @Route("/content/", name="_by_content_id")
     */
    public function redirectByContentId (Request $request) {
        $contentId = $request->query->get('content');
        if(is_numeric($contentId)) {
            try {
                $contentId = (int) $contentId;
                /** @var Content $content */
                $content = $this->contentService->loadContent($contentId);
                $location = $this->locationService->loadLocation($content->contentInfo->mainLocationId);
                return $this->redirect("/admin/view/content/" . $contentId . "/full/1/" . $location->id);
            } catch (\Exception $e) {

            }
        }
        return $this->redirect("/admin/?lookup_failed");
    }

    /**
     * @Route("/location/", name="_by_location_id")
     */
    public function redirectbyLocationId (Request $request) {
        $locationId = $request->query->get('location');
        if(is_numeric($locationId)) {
            try{
                $locationId = (int) $locationId;
                /** @var Location $location */
                $location = $this->locationService->loadLocation($locationId);
                return $this->redirect("/admin/view/content/".$location->contentId."/full/1/".$location->id);
            } catch(\Exception $e) {

            }
        }

        return $this->redirect("/admin/?lookup_failed");
    }
}
?>