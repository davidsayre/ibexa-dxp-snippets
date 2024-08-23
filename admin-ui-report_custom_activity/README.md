## DXP Admin - Content activity report

GOAL: usable form to query content in the CMS.
GOAL: download results as CSV (for offline use / people workflow)
GOAL: XML download for editing / later re-importing via DXP REST API

Filter:
* by created/modified
* by section
* by tag
* excluding paths
* etc..


### How to Create a controller that can export XML in the Ibexa REST API schema

Exporting with the official Ibexa REST API schema is good becuase it means you cna use the IBEXA REST API to re-import.

NOTE: The IBEXA REST API (content/update) has no error handling or messanging. This is very frustrating to debug.

It will return the content as-is and will not tell you if the import worked, if fields imported. 


see src/admin-ui-report_custom_activity/src/Controller/Admin/ReportContentActivityController.php for my implementation of this (and a fancy report UI)


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
