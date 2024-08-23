<?php

namespace App\Migrations\Action\AssignSearchableAction;

use Ibexa\Contracts\Core\Repository\Repository;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Contracts\Core\Repository\Values\ValueObject;
use Ibexa\Migration\StepExecutor\ActionExecutor\ExecutorInterface;
use Ibexa\Migration\ValueObject\Step\Action;
use Webmozart\Assert\Assert;

class AssignSearchableExecutor implements ExecutorInterface
{

    public function __construct(private readonly Repository $repository)
    {
    }

    public function handle(Action $action, ValueObject $valueObject): void
    {
        Assert::isInstanceOf($action, AssignSearchableAction::class, 'Action must be an instance of ' . AssignSearchableAction::class . ', got ' . get_class($action));
        Assert::isInstanceOf($valueObject, ContentType::class, 'Value object must be an instance of ' . ContentType::class . ', got ' . get_class($valueObject));
        /** @var AssignSearchableAction $action */
        /** @var ContentType $valueObject */

        $searchableValue = $action->getSearchable();
        $fieldDefinitionIdentifier = $action->getFieldDefinitionIdentifier();

        $this->repository->sudo(function (Repository $repository) use ($valueObject, $searchableValue, $fieldDefinitionIdentifier) {
            $contentType = $repository->getContentTypeService()->loadContentType($valueObject->id);

            $draft = $repository->getContentTypeService()->createContentTypeDraft($contentType);

            $fieldDefinition = $draft->getFieldDefinition($fieldDefinitionIdentifier);

            if ($fieldDefinition === null) {
                return;
            }

            $fieldDefinitionUpdate = $repository->getContentTypeService()->newFieldDefinitionUpdateStruct();
            $fieldDefinitionUpdate->isSearchable = $searchableValue;

            $repository->getContentTypeService()->updateFieldDefinition($draft, $fieldDefinition, $fieldDefinitionUpdate);


            $repository->getContentTypeService()->publishContentTypeDraft($draft);

        });
    }
}
