<?php

/**
 * Derived from https://github.com/janit/ezplatform-migrate-image-asset/blob/master/src/Command/MigrateImageToAssetCommand.php
 * upgraded from eZ to Ibexa
 *
 * ./bin/console app:migrate_image_to_asset content_page image asset 9372
 *   NOTE: must add '--save 1' to perform database changes (else dry run)
 */

namespace App\Command\Ibexa;

use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\Repository\SearchService;
use Ibexa\Contracts\Core\Repository\UserService;
use Ibexa\Contracts\Core\Repository\Values\Content\LocationQuery;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Core\FieldType\Image\Value as ImageFieldValue;
use Ibexa\Core\FieldType\ImageAsset\Value as ImageAssetFieldValue;
use Ibexa\Core\Repository\Values\Content\Content as ContentObject;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateImageToAssetCommand extends Command
{
    public const COMMAND_NAME ='app:migrate_image_to_asset';

    const IMAGE_CONTENT_TYPE = 'image';
    const IMAGE_LANGUAGE = 'eng-US';
    const IMPORT_USER = 14;

    /*
     * TODO: log output of image fields found with dimensions
     * TODO: check the source image dimensions and do not use if to small
     * TODO: do not create image asset if already populated in content
     * DONE: limit to content on the LIVE tree only
     * DONE: limit to only visible content
     * TODO: SKIP content that is in the /archive locationID path 52252
     */

    protected $contentService;
    protected $contentTypeService;
    protected $locationService;
    protected $searchService;
    protected $permissionResolver;
    protected $userService;

    // change minimum width/height specifically for each purpose
    // example landscape 1900 x 500
    // example card 750 x 500
    protected $minImageHeightAllow = 750;
    protected $minImageWidthAllow = 460;

    protected $logger;

    /**
     * VERY IMPORTANT!! limit the search to this section 'standard'
     * @var string
     */
    protected $searchSectionIdentifier = 'standard'; // specifically onl query this section. MUST change /archive section OR move it before running!

    protected $searchParentLocationId = 62; // ONLY consider migrating

    protected $searchLimit = 10;

    protected $output;

    /**
     * Save = true updates database / false = dry run (safe)
     * @var bool
     */
    protected $save = false;

    public function __construct(
        ContentService $contentService,
        ContentTypeService $contentTypeService,
        LocationService $locationService,
        SearchService $searchService,
        PermissionResolver $permissionResolver,
        UserService $userService,
        LoggerInterface $migrateImagesLogger
    )
    {
        $this->contentService = $contentService;
        $this->contentTypeService = $contentTypeService;
        $this->locationService = $locationService;
        $this->searchService = $searchService;
        $this->permissionResolver = $permissionResolver;
        $this->userService = $userService;
        $this->logger = $migrateImagesLogger;

        parent::__construct(self::COMMAND_NAME);
    }

    public function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        parent::initialize($input, $output);
    }

    protected function configure()
    {
        $this
            ->setDescription('Copies image field type contents to an image asset field')
            ->addArgument('type_identifier', InputArgument::REQUIRED, 'Identifier of content type whose to data is to be modified')
            ->addArgument('source_field', InputArgument::REQUIRED, 'Source field identifier')
            ->addArgument('target_field', InputArgument::REQUIRED, 'Target field identifier')
            ->addArgument('target_location', InputArgument::REQUIRED, 'Target location id where image objects should be created')
            ->addOption('save', '--save',InputOption::VALUE_OPTIONAL, 'save 1 = live DB operations');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $contentTypeIdentifier = $input->getArgument('type_identifier');
        $sourceFieldIdentifier = $input->getArgument('source_field');
        $targetFieldIdentifier = $input->getArgument('target_field');
        $imageTargetLocationId = $input->getArgument('target_location');

        // save / dry run
        if($input->getOption('save') == "1" | $input->getOption('save') == true){
            $this->save = true;
        }

        $this->permissionResolver->setCurrentUserReference(
            $this->userService->loadUser(MigrateImageToAssetCommand::IMPORT_USER)
        );

        $sourceLocation = $this->locationService->loadLocation($this->searchParentLocationId);
        $searchResults = $this->loadContentObjects($sourceLocation, $contentTypeIdentifier);

        $this->log("info","Processing [begin]");
        foreach ($searchResults as $searchHit) {
            /** @var ContentObject $contentObject */
            $contentObject = $searchHit->valueObject;
            $this->updateContentObject($contentObject, $sourceFieldIdentifier, $targetFieldIdentifier, $imageTargetLocationId);
        }
        $this->log("info","Processing [complete]");

        return self::SUCCESS;
    }


    private function loadContentObjects($parentLocation, $contentTypeIdentifier): array
    {
        $query = new LocationQuery();
        $mainAndCriteria = [
            new Query\Criterion\Visibility(Query\Criterion\Visibility::VISIBLE),
            new Query\Criterion\ContentTypeIdentifier($contentTypeIdentifier)
        ];
        // if section limiter applied
        if(!empty($this->searchSectionIdentifier)) {
            $mainAndCriteria[] = new Query\Criterion\SectionIdentifier($this->searchSectionIdentifier);
        }
        // if subtee limited
        if(!empty($this->searchParentLocationId)) {
            $mainAndCriteria[] = new Query\Criterion\Subtree($parentLocation->pathString);
        }

        $query->filter = new Query\Criterion\LogicalAnd($mainAndCriteria);
        $query->limit = $this->searchLimit;
        $result = $this->searchService->findContent($query);
        return $result->searchHits;

    }

    private function updateContentObject(ContentObject $contentObject, $sourceFieldIdentifier, $targetFieldIdentifier, $imageTargetLocationId): void
    {
        $contentShortName = "[".$contentObject->id."] ".$contentObject->getName();
        $this->log("info",$contentShortName." [validating]");

        // CHECK: asset field (ezimageasset) is empty
        /** @var ImageAssetFieldValue $assetFieldValue */
        $assetFieldValue = $contentObject->getFieldValue($targetFieldIdentifier);
        if( is_numeric($assetFieldValue->destinationContentId) and $assetFieldValue->destinationContentId > 0) {
            $this->log("error",$contentShortName." [skip] asset already populated");
            return;
        }

        // check content is not located in the 'archive' section

        // Check image (ezimage) populated
        /** @var ImageFieldValue $imageFieldValue */
        $imageFieldValue = $contentObject->getFieldValue($sourceFieldIdentifier);
        if(empty($imageFieldValue)) {
            $this->log("error",$contentShortName." [skip] Source image field empty");
            return;
        } else {
            $this->log("info",$contentShortName." [ok] source ezimage has data");
            // CHECK: source image on disk
            if(!file_exists($imageFieldValue->uri)) {
                $this->log("error",$contentShortName." [error] source image not on disk");
                return;
            }
            // CHECK source image width
            if($imageFieldValue->height < $this->minImageHeightAllow) {
                $this->log("error", $contentShortName." [deny] image height ".$imageFieldValue->height);
                return;
            }
            // CHECK source image width
            if($imageFieldValue->width < $this->minImageWidthAllow) {
                $this->log("error", $contentShortName." [deny] image width ".$imageFieldValue->width);
                return;
            }
        }

        $imageObjectRemoteId = 'image-asset-' . $contentObject->id . '-' . $contentObject->getField($sourceFieldIdentifier)->fieldDefIdentifier;

        if($this->save === true) {
            $this->log("info",$contentShortName." [save] creating content update ... ");

            // get the new / existing image to assign
            $imageObject = $this->createOrUpdateImage($imageObjectRemoteId, $imageTargetLocationId, $imageFieldValue);

            $contentDraft = $this->contentService->createContentDraft( $contentObject->contentInfo );

            $contentUpdateStruct = $this->contentService->newContentUpdateStruct();
            $contentUpdateStruct->initialLanguageCode = MigrateImageToAssetCommand::IMAGE_LANGUAGE;

            // assign the new image into the image 'asset' field of the holding content
            $contentUpdateStruct->setField($targetFieldIdentifier, $imageObject->id);

            $draft = $this->contentService->updateContent($contentDraft->versionInfo, $contentUpdateStruct);
            $content = $this->contentService->publishVersion($draft->versionInfo);
            $this->log("info",$contentShortName." [Save] content published");

        } else {
            $this->log("info",$contentShortName." [Dry run] would have updated content");
        }

    }

    /**
     * @param string $remoteId
     * @param int $parentLocationId
     * @param ImageFieldValue $imageFieldValue
     * @return ContentObject|null
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    private function createOrUpdateImage(string $remoteId, int $parentLocationId, ImageFieldValue $imageFieldValue)
    {
        $contentType = $this->contentTypeService->loadContentTypeByIdentifier(MigrateImageToAssetCommand::IMAGE_CONTENT_TYPE);

        $imageName = $imageFieldValue->fileName;
        $imagePath = getcwd() . '/public' . $imageFieldValue->uri;

        // dry run
        if($this->save !== true) {
            $this->log("info","Dry run image asset create");
            return null;
        }

        try {

            // Lookup imageasset in Media via remoteId
            $contentObject = $this->contentService->loadContentByRemoteId($remoteId, [MigrateImageToAssetCommand::IMAGE_LANGUAGE]);

            // TODO: maybe only update the SAME version. Don't want multiple versions for each run

            $contentDraft = $this->contentService->createContentDraft( $contentObject->contentInfo );

            $contentUpdateStruct = $this->contentService->newContentUpdateStruct();
            $contentUpdateStruct->initialLanguageCode = MigrateImageToAssetCommand::IMAGE_LANGUAGE;

            $contentUpdateStruct->setField('name', $imageName);
            $contentUpdateStruct->setField('image', $imagePath);

            // TODO: copy alternative Text to new image

            $draft = $this->contentService->updateContent($contentDraft->versionInfo, $contentUpdateStruct);
            $content = $this->contentService->publishVersion($draft->versionInfo);

        } catch (\eZ\Publish\Core\Base\Exceptions\NotFoundException $e){

            // Not found, create new object

            try {

                $contentCreateStruct = $this->contentService->newContentCreateStruct($contentType, MigrateImageToAssetCommand::IMAGE_LANGUAGE);
                $contentCreateStruct->remoteId = $remoteId;

                $contentCreateStruct->setField('name', $imageName);
                $contentCreateStruct->setField('image', $imagePath);

                // TODO: copy alternative Text to new image

                $locationCreateStruct = $this->locationService->newLocationCreateStruct($parentLocationId);
                $draft = $this->contentService->createContent($contentCreateStruct, [$locationCreateStruct]);
                $content = $this->contentService->publishVersion($draft->versionInfo);

            } catch (\Exception $e){
                dump($e);
                die();
            }

        } catch (\Exception $e){
            dump($e);
            die();
        }

        return $content;

    }
    
    public function log($type, $message) {
        $this->logger->log($type, $message);
        $this->output->writeln($message);
    }


}