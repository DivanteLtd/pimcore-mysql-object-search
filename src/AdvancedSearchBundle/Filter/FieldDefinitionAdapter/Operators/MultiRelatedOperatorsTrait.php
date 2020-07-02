<?php

namespace DivanteLtd\AdvancedSearchBundle\Filter\FieldDefinitionAdapter\Operators;


trait MultiRelatedOperatorsTrait
{
    /**
     * @return array
     */
    protected function getFieldOperators(): array
    {
        return [
            ['fieldName' => 'contains_related', 'fieldLabel' => 'bundle_advancedSearch_character_operator_contain'],
        ];
    }
}
