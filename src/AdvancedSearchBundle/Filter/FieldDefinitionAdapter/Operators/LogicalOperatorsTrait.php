<?php
/**
 * @category    pimcore
 * @date        02/10/2019 16:14
 *
 * @author      Korneliusz Kirsz <kkirsz@divante.co>
 * @copyright   Copyright (c) 2019 Divante Ltd. (https://divante.co)
 */

namespace DivanteLtd\AdvancedSearchBundle\Filter\FieldDefinitionAdapter\Operators;

/**
 * Trait LogicalOperatorsTrait
 *
 * @package DivanteLtd\AdvancedSearchBundle\Filter\FieldDefinitionAdapter\Operators
 */
trait LogicalOperatorsTrait
{
    /**
     * @return array
     */
    protected function getFieldOperators(): array
    {
        return [
            true,
            false,
        ];
    }
}
