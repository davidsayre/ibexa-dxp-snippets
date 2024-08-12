
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
ibexa_automated_translation:
    system:
        default:
            configurations:
                google:
                    apiKey: "%google_translate_api_key%"
```