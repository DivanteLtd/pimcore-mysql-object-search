<?php

namespace DivanteLtd\AdvancedSearchBundle\Filter\FieldDefinitionAdapter\Operators;


trait RelatedOperatorsTrait
{
    /**
     * @return array
     */
    protected function getFieldOperators(): array
    {
        return [
            ['fieldName' => 'equal_related', 'fieldLabel' => '='],
        ];
    }
}
