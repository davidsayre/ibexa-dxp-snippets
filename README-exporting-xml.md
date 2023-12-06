## Snippet provided by Ibexa Support regarding how to get XML from your controller leveraging the REST API

Inject the following into a Custom Controller:

```
\Ibexa\Core\Repository\Repository $repository (or prefered: just the repository services that you need)
\Ibexa\Core\Helper\TranslationHelper $translationHelper,
\Ibexa\Contracts\Rest\Output\Visitor $visitor,
\Ibexa\Rest\Output\Generator\Xml $xml
```

Do something similar to what is done in ibexa/rest/src/lib/Server/Controller/Content:loadContent to load Content from a given contentId to an appropriate structure
Use similar logic to what is done in ibexa/rest/src/lib/Server/Output/ValueObjectVisitor/RestContent->visit to generate XML from the data retrieved in the previous step.