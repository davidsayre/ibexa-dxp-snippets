<?php

declare(strict_types=1);

namespace App\Twig;

use Ibexa\Contracts\Core\Repository\Values\Content\LocationQuery;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Ancestor;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\SearchService;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Symfony\Component\Routing\RouterInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AncestorsTwigExtension extends AbstractExtension
{
    protected RouterInterface $router;
    protected LocationService $locationService;
    protected SearchService $searchService;

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
            new TwigFunction('ancestors', [$this, 'getAncestors']),
        ];
    }

    public function getAncestors(int $locationId, $skipMinDepth = 2): array
    {
        if(!is_numeric($locationId)) {
            return [];
        }
        if($locationId <= 1) {
            return [];
        }
        $mainAndCriteria = [
            new Query\Criterion\Visibility(Query\Criterion\Visibility::VISIBLE),
            new Ancestor([$this->locationService->loadLocation($locationId)->pathString]),
            new Query\Criterion\Location\Depth(">", $skipMinDepth)
        ];

        $query = new LocationQuery();
        $query->query = new Query\Criterion\LogicalAnd($mainAndCriteria);
        $query->sortClauses = [new Query\SortClause\Location\Depth(Query::SORT_ASC)];
        $results = $this->searchService->findLocations($query);
        $parents = [];
        /** @var Location $searchHit */
        foreach ($results->searchHits as $searchHit) {
            $parents[] = $searchHit->valueObject;
        }
        return $parents;
    }
}
