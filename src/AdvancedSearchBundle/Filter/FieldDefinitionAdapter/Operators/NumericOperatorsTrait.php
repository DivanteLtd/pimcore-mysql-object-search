<?php
/**
 * @category    pimcore
 * @date        02/10/2019 16:16
 *
 * @author      Korneliusz Kirsz <kkirsz@divante.co>
 * @copyright   Copyright (c) 2019 Divante Ltd. (https://divante.co)
 */

namespace DivanteLtd\AdvancedSearchBundle\Filter\FieldDefinitionAdapter\Operators;

/**
 * Trait NumericOperatorsTrait
 *
 * @package DivanteLtd\AdvancedSearchBundle\Filter\FieldDefinitionAdapter\Operators
 */
trait NumericOperatorsTrait
{
    /**
     * @return array
     */
    protected function getFieldOperators(): array
    {
        return [
            ['fieldName' => 'equal', 'fieldLabel' => '='],
            ['fieldName' => 'not_equal', 'fieldLabel' => '!='],
            ['fieldName' => 'less', 'fieldLabel' => '<'],
            ['fieldName' => 'less_equal', 'fieldLabel' => '<='],
            ['fieldName' => 'greater', 'fieldLabel' => '>'],
            ['fieldName' => 'greater_equal', 'fieldLabel' => '>='],
        ];
    }
}
