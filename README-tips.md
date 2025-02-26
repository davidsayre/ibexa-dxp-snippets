
## Problem: customize download controller
Solution: https://github.com/netgen/site-bundle/blob/master/bundle/Controller/Download.php

> "My use case where I was having an issue was overriding the DownloadController to support inline content deposition. (inspired by https://github.com/netgen/site-bundle/blob/master/bundle/Controller/Download.php)" - wizhippo
> "This issue I ran into is I could not just extend the ibexa contoller and set the disposition on the base class returned response the base class was would set he disposition using the filename from the field instead of using the passed in filename which had slashes and would throw when the deposition was se to inline." - wizhippo


### Ibexa Automated translation

see https://github.com/ibexa/automated-translation/

``` 
composer require ibexa/automated-translation
```

``` 

parameters:
    google_translate_api_key: '%env(GOOGLE_TRANSLATE_API_KEY)%'
    
ibexa_automated_translation:
    system:
        default:
            configurations:
                google:
                    apiKey: "%google_translate_api_key%"
```

Edit .env
```
 GOOGLE_TRANSLATE_API_KEY=
```

Edit .env.local
``` 
GOOGLE_TRANSLATE_API_KEY=************
```

## Problem: hide Header 1 as this is confusing

### Solution @ Mateusz Bieniek from Slack 2024-09-18

``` 
in order to use a different configuration for CKEditor you need to manually override Ibexa's CKEditor initialization. I'll provide you with a guide on how to approach this below.
I would add a new file, called for example assets/js/overridden-base-ckeditor.js with the contents of vendor/ibexa/fieldtype-richtext/src/bundle/Resources/public/js/CKEditor/core/base-ckeditor.js. Obviously, you should remember to change import paths, for example:

import IbexaCharacterCounter from '../../vendor/ibexa/fieldtype-richtext/src/bundle/Resources/public/js/CKEditor/plugins/character-counter';
import IbexaElementsPath from '../../vendor/ibexa/fieldtype-richtext/src/bundle/Resources/public/js/CKEditor/plugins/elements-path';
import IbexaEmbed from '../../vendor/ibexa/fieldtype-richtext/src/bundle/Resources/public/js/CKEditor/embed/embed';
import IbexaCustomTags from '../../vendor/ibexa/fieldtype-richtext/src/bundle/Resources/public/js/CKEditor/custom-tags/custom-tags';
import IbexaCustomStylesInline from '../../vendor/ibexa/fieldtype-richtext/src/bundle/Resources/public/js/CKEditor/custom-styles/inline/custom-styles-inline';
import IbexaCustomAttributes from '../../vendor/ibexa/fieldtype-richtext/src/bundle/Resources/public/js/CKEditor/custom-attributes/custom-attributes';
import IbexaLink from '../../vendor/ibexa/fieldtype-richtext/src/bundle/Resources/public/js/CKEditor/link/link';
import IbexaAnchor from '../../vendor/ibexa/fieldtype-richtext/src/bundle/Resources/public/js/CKEditor/anchor/anchor';
import IbexaFormatted from '../../vendor/ibexa/fieldtype-richtext/src/bundle/Resources/public/js/CKEditor/formatted/formatted';
import IbexaMove from '../../vendor/ibexa/fieldtype-richtext/src/bundle/Resources/public/js/CKEditor/move/move';
import IbexaRemoveElement from '../../vendor/ibexa/fieldtype-richtext/src/bundle/Resources/public/js/CKEditor/remove-element/remove-element';
import IbexaBlockAlignment from '../../vendor/ibexa/fieldtype-richtext/src/bundle/Resources/public/js/CKEditor/block-alignment/block-alignment';
import IbexaUploadImage from '../../vendor/ibexa/fieldtype-richtext/src/bundle/Resources/public/js/CKEditor/upload-image/upload-image';
Now, add the following file to your project root directory: encore/ibexa.richtext.config.manager.js with the following contents:
const path = require('path');
 
module.exports = (ibexaConfig, ibexaConfigManager) => {
    ibexaConfigManager.replace({
        ibexaConfig,
        entryName: 'ibexa-richtext-onlineeditor-js',
        itemToReplace: path.resolve(__dirname, '../vendor/ibexa/fieldtype-richtext/src/bundle/Resources/public/js/CKEditor/core/base-ckeditor.js'),
        newItem: path.resolve(__dirname, '../assets/js/overridden-base-ckeditor.js'),
    });
};

This will allow us to override the ibexa-richtext-onlineeditor-js entry in the richtext config.
Now you are free to change the overridden-base-ckeditor.js however you like - remove plugins, remove headers, etc. according to the CKEditor docs (https://ckeditor.com/docs/ckeditor5/latest/api/module_core_editor_editorconfig-EditorConfig.html).
After the modifications, please, run the composer run post-update-cmd command so your new config manager is caught by Webpack.

Please keep in mind, this means that you have to review this solution with every update
```

``` 
oh, and i got informed we also have a configuration event for online editor, so in theory this should also work:

document.body.addEventListener("ibexa-ckeditor:configure", (e) => {
    e.detail.config.heading.options = e.detail.config.heading.options.filter(val => val.model !== 'heading1');
});

Sure, np - I remembered there should be a way to do that and got some help from our Support Team
```


Hi, I am looking for a way to find a location by its URL (with PHP API) but I didn't find any criterion for that in the docs.
Does anyone know how I can do that ? Thanks

Matthias Schmidt 
I had asked the support the same question some time ago and also found no clean solution, but the following code is working for us:
```
    protected function resetRouter(): null|SiteAccess
    {
        $oldSiteAccess = $this->siteAccessRouter->getSiteAccess();
        $this->siteAccessRouter->setSiteAccess();

        return $oldSiteAccess;
    }

    protected function restoreRouter(null|SiteAccess $siteAccess): void
    {
        $this->siteAccessRouter->setSiteAccess($siteAccess);
    }

    protected function getLocation(string $url, SiteAccess $siteAccess): Location
    {
        $currentSiteAccess = $this->resetRouter();

        try {
            $this->restoreRouter($siteAccess);

            $request = Request::create($url);

            /**
             * Use the established mechanism to determine `semanticPathinfo` which is needed for request matching.
             * @see \Ibexa\Bundle\Core\EventListener\SiteAccessListener::onSiteAccessMatch()
             */
            $event = new PostSiteAccessMatchEvent($siteAccess, $request, HttpKernelInterface::MAIN_REQUEST);
            $this->eventDispatcher->dispatch($event, MVCEvents::SITEACCESS);
            $requestData = $this->requestMatcher->matchRequest($request);
        } finally {
            $this->restoreRouter($currentSiteAccess);
        }

        if (!isset($requestData['locationId'])) {
            throw new \LogicException('Request matching did not yield a location id');
        }

        return $this->locationService->loadLocation($requestData['locationId']);
    }
Beforehand, you can use \Ibexa\Core\MVC\Symfony\SiteAccess\Router::match to find the site access based on the URL. (again with having to wrap the code in reset/restoreRouter calls.
```