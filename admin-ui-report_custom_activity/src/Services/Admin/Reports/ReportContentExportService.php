<?php

namespace App\Services\Admin\Reports;

use Ibexa\Contracts\Core\Repository\SearchService;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Personalization\Value\Search\SearchHit;
use Symfony\Component\HttpFoundation\Response;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Contracts\Core\Repository\Values\Content\LocationQuery;
use Ibexa\Core\Repository\SiteAccessAware\Repository;
use Ibexa\Core\Helper\TranslationHelper;
use Ibexa\Contracts\Rest\Output\Visitor;
use Ibexa\Rest\Output\Generator\Xml;
use Ibexa\Rest\Server\Values as RestServerValues;
use Symfony\Component\HttpFoundation\StreamedResponse;
use function PHPUnit\Framework\isInstanceOf;

/**
 * I feel like there is a way to export XML using the REST API or something but there are no examples
 * So i have to build this from scrtch :(
 */

class ReportContentExportService {

    const CSV_EOL = "\r\n";

    private Repository $repository;
    private TranslationHelper $translationHelper;
    private Visitor $visitor;
    private Xml $xmlGenerator;
    private SearchService $searchService;

    /**
     * @param Repository $repository
     * @param Visitor $visitor
     * @param Xml $xmlGenerator
     */
    public function __construct(
        Repository $repository,
        TranslationHelper $translationHelper,
        $visitor,
        Xml $xmlGenerator,
        SearchService $searchService
    ) {
        $this->repository = $repository;
        $this->translationHelper = $translationHelper;
        $this->visitor = $visitor;
        $this->xmlGenerator = $xmlGenerator;
        $this->searchService = $searchService;
    }

    /**
     * @param LocationQuery $query
     * @param array $languages - examples: [] or [eng-US,esl-ES]
     * @param $limit
     * @return string
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function generateRestXMLRowsByQuery(LocationQuery $query, array $languages = Language::ALL, $limit = 500) {

        $xmlString = "<test></test>";
        $this->xmlGenerator->reset();
        $this->xmlGenerator->startDocument('root');
        $this->xmlGenerator->startObjectElement('root');
        $this->xmlGenerator->startList('rows');

        // failsafe
        if(!is_numeric($limit)) {
            $limit = 500;
        }
        $query->limit = $limit;
        $searchResult = $this->searchService->findLocations($query);
        /** @var SearchHit $searchHit */
        foreach($searchResult as $searchHit) {
            /** @var Location $location */
            $location = $searchHit->valueObject;
            $restContent = $this->generateRestXMLByContentId($location->contentId, $languages);
            $this->visitor->visitValueObject($restContent);
        }
        $this->xmlGenerator->endList('rows');
        $this->xmlGenerator->endObjectElement('root');
        return $this->xmlGenerator->endDocument('root');
    }

    /**
     * @param $contentId
     * @param array $languages - examples: [] or [eng-US,esl-ES]
     * @return RestServerValues\RestContent
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function generateRestXMLByContentId($contentId, array $languages = Language::ALL) {
        $contentInfo = $this->repository->getContentService()->loadContentInfo($contentId);

        $mainLocation = null;
        if (!empty($contentInfo->mainLocationId)) {
            $mainLocation = $this->repository->getLocationService()->loadLocation($contentInfo->mainLocationId);
        }

        $contentType = $this->repository->getContentTypeService()->loadContentType($contentInfo->contentTypeId);

        // Important note that the languages array is passed in to set the field set(s)
        $contentVersion = $this->repository->getContentService()->loadContent($contentId, $languages);

        //SKIP: $relations = $this->repository->getContentService()->loadRelations($contentVersion->getVersionInfo());

        $restContent = new RestServerValues\RestContent(
            $contentInfo,
            $mainLocation,
            $contentVersion,
            $contentType,
            array()
            //SKIP: $relations,
        );

        return $restContent;
    }

    /**
     * Leverage the Ibexa DXP xml generator and magic visitor based on the RestContent (already queried, language applied)
     * @param RestServerValues\RestContent $restContent
     * @return false|string
     */
    public function convertRestContentToRestXml(RestServerValues\RestContent $restContent) {
        $this->xmlGenerator->reset();
        $this->xmlGenerator->startDocument($restContent);
        $xmlResponse = $this->visitor->visit($restContent);
        return $xmlResponse->getContent();
    }

    /**
     * @param $query
     * @param $viewAddAttributes
     * @param $fieldAddAttributes
     * @param $limit
     * @return string
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    public function generateCSVByQuery($query, $viewAddAttributes = [], $fieldAddAttributes = [], $limit = 500) {

        $csvFileText = "\xEF\xBB\xBF"; // main CSV string

        $aHeaders = array();
        $aHeaders["Name"] = "";
        $aHeaders["Type"] = "";
        $aHeaders["Published"] = "";
        $aHeaders["Modified"] = "";
        $aHeaders["Reviewed"] = "";
        $aHeaders["Owner"] = "";

        if ( isset($viewAddAttributes) ) {
            foreach ( $viewAddAttributes as $fieldKey ) {
                if(!empty($fieldKey)) {
                    $aHeaders[$fieldKey] = "";
                }
            }
        }

        if ( isset($fieldAddAttributes) ) {
            foreach ( $fieldAddAttributes as $fieldKey ) {
                if(!empty($fieldKey)) {
                    $aHeaders[$fieldKey] = "";
                }
            }
        }

        $csvFileText .= implode(",",array_keys($aHeaders)). self::CSV_EOL;

        // failsafe
        if(!is_numeric($limit)) {
            $limit = 500;
        }
        $query->limit = $limit;
        $searchResult = $this->searchService->findLocations($query);
        /** @var SearchHit $searchHit */
        foreach($searchResult as $searchHit) {
            /** @var Location $location */
            $location = $searchHit->valueObject;
            $csvFileText .= $this->generateCSVRowFromLocation($location, $viewAddAttributes, $fieldAddAttributes);
        }
        return $csvFileText; // first row is header
    }

    /**
     * Simple CSV listing of results - ids,dates,name,languages,(picked custom field(s)) only
     * NO DYANAMIC FIELDS loop!
     * @param Location $location
     * @param $viewAddAttributes
     * @param $fieldAddAttributes
     * @return string
     */
    public function generateCSVRowFromLocation(Location $location, $viewAddAttributes = [],$fieldAddAttributes = []) {
        $content = $location->getContent();
        $fields = $content->getFields();

        $csvLineCols = array(); // top from header

        $csvLineCols['Name'] = trim( str_replace( '"', '""', str_replace(array("\r", "\n"), '', $content->getName())));
        $csvLineCols['Type'] = $content->contentInfo->getContentType()->identifier;
        $csvLineCols["Published"] = date('m/d/Y', $content->contentInfo->publishedDate->getTimestamp());
        $csvLineCols["Modified"] = date('m/d/Y', $content->contentInfo->modificationDate->getTimestamp());
        $csvLineCols["Reviewed"] = "NA";
        $csvLineCols["Owner"] = $content->contentInfo->getOwner()->getName();

        // maybe publish_date
        if ( isset( $fields['publish_date'] )) {
            $reviewTimestamp = $fields['publish_date']->getValue();
            if(!empty($reviewTimestamp)) {
                $csvLineCols["Reviewed"] = date('m/d/Y',$reviewTimestamp );
            }
        }

        // View filters to add to line
        if ( isset($viewAddAttributes) ) {
            foreach ( $viewAddAttributes as $fieldKey ) {
                switch ($fieldKey) {
                    case 'filtered_language':
                        $csvLineCols["v_".$fieldKey] = $content->contentInfo->mainLanguageCode;
                        break;
                    case 'language':
                        $csvLineCols["v_".$fieldKey] = implode("|",$content->versionInfo->languageCodes);
                        break;
                    default:
                        $csvLineCols["v_".$fieldKey] = $fields[$fieldKey]->getValue();
                        break;
                }

            }
        }

        // Dynamic fields to add to CSV line
        if ( isset($fieldAddAttributes) ) {
            foreach ( $fieldAddAttributes as $fieldKey ) {
                $csvLineCols["f_".$fieldKey] = ""; // alwyas init field
                if ( isset( $fields[$fieldKey] )) {
                    $attrValue = $fields[$fieldKey]->getValue();
                    // check for datetime field type
                    if(isInstanceOf('DateTime',$attrValue)) {
                        $attrValue = date('m/d/Y',$attrValue->getTimestamp());
                    }
                    // must be a string, else need additional datatype handler logic above
                    if(!is_string($attrValue)) {
                        $attrValue = "(object)";
                    }
                    $csvLineCols["f_".$fieldKey] = $attrValue; // set value if string / ok
                }
            }
        }

        // remove all " with ' from all strings
        foreach($csvLineCols as $key => $value) {
            $csvLineCols[$key] = str_replace('"',"'",$value);
        }

        // convert array to quoted scring
        $csvLineColsString = '"'.implode('","',$csvLineCols).'"'.self::CSV_EOL;
        return $csvLineColsString;
    }

}

?>