<?php

declare(strict_types=1);


use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Ancestor;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\SearchService;
use Symfony\Component\Routing\RouterInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AncestorsTwigExtension extends AbstractExtension
{
    /** @var \Symfony\Component\Routing\RouterInterface */
    protected $router;

    /** @var LocationService */
    protected $locationService;

    /** @var SearchService */
    protected $searchService;

    /**
     * SvgTwigExtension constructor.
     */
    public function __construct(RouterInterface $router, LocationService $locationService, SearchService $searchService)
    {
        $this->router = $router;
        $this->locationService = $locationService;
        $this->searchService = $searchService;
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('ancestors', [
                $this,
                'getAncestors'
            ]),
        ];
    }

    public function getAncestors(int $locationId): array
    {
        $query = new LocationQuery();
        $query->query = new Ancestor([$this->locationService->loadLocation($locationId)->pathString]);
        $results = $this->searchService->findLocations($query);
        $parents = [];
        foreach ($results->searchHits as $searchHit) {
            $parents[] = $searchHit->valueObject;
        }
        return $parents;
    }
}