<?php

namespace App\Migrations\Action\AssignSearchableAction;

use Ibexa\Migration\ValueObject\Step\Action;

final class AssignSearchableAction implements Action
{
    const TYPE = 'assign_searchable';

    private bool $searchable;
    private string $fieldDefinitionIdentifier;

    public function __construct(bool $searchable, string $fieldDefinitionIdentifier)
    {
        $this->searchable = $searchable;
        $this->fieldDefinitionIdentifier = $fieldDefinitionIdentifier;
    }

    public function getSearchable(): bool
    {
        return $this->searchable;
    }

    public function getFieldDefinitionIdentifier(): string
    {
        return $this->fieldDefinitionIdentifier;
    }

    public function getValue(): string
    {
        return $this->getFieldDefinitionIdentifier();
    }

    public function getSupportedType(): string
    {
        return self::TYPE;
    }
}
