<?php

namespace App\Migrations\Action\AssignSearchableAction;

use Ibexa\Contracts\Migration\Serializer\Denormalizer\AbstractActionDenormalizer;
use Webmozart\Assert\Assert;

final class AssignSearchableDenormalizer extends AbstractActionDenormalizer
{
    protected function supportsActionName(string $actionName, string $format = null): bool
    {
        return $actionName === AssignSearchableAction::TYPE;
    }

    public function denormalize($data, string $type, string $format = null, array $context = []): AssignSearchableAction
    {
        Assert::keyExists($data, 'searchable');
        Assert::keyExists($data, 'fieldDefinitionIdentifier');

        return new AssignSearchableAction(
            $data['searchable'] === 'true',
            $data['fieldDefinitionIdentifier']
        );
    }
}
