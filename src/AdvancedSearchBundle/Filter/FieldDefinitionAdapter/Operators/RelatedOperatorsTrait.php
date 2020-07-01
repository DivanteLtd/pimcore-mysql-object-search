<?php
/**
 * @category    wurth
 * @date        24/06/2020 10:36
 * @author      Pascal Dunaj <pdunaj@divante.pl>
 * @copyright   Copyright (c) 2020 Divante Ltd. (https://divante.co)
 */

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
