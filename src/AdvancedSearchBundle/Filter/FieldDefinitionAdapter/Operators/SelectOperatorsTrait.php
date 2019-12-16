<?php
/**
 * @category    pimcore
 * @date        03/10/2019 13:15
 *
 * @author      Korneliusz Kirsz <kkirsz@divante.co>
 * @copyright   Copyright (c) 2019 Divante Ltd. (https://divante.co)
 */

namespace DivanteLtd\AdvancedSearchBundle\Filter\FieldDefinitionAdapter\Operators;

/**
 * Trait SelectOperatorsTrait
 *
 * @package DivanteLtd\AdvancedSearchBundle\Filter\FieldDefinitionAdapter\Operators
 */
trait SelectOperatorsTrait
{
    /**
     * @return array
     */
    protected function getFieldOperators(): array
    {
        return [
            ['fieldName' => 'equal', 'fieldLabel' => '='],
        ];
    }
}
