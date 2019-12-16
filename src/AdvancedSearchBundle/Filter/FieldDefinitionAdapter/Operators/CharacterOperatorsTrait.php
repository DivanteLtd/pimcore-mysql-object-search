<?php
/**
 * @category    pimcore
 * @date        02/10/2019 16:06
 *
 * @author      Korneliusz Kirsz <kkirsz@divante.co>
 * @copyright   Copyright (c) 2019 Divante Ltd. (https://divante.co)
 */

namespace DivanteLtd\AdvancedSearchBundle\Filter\FieldDefinitionAdapter\Operators;

/**
 * Trait CharacterOperatorsTrait
 *
 * @package DivanteLtd\AdvancedSearchBundle\Filter\FieldDefinitionAdapter\Operators
 */
trait CharacterOperatorsTrait
{
    /**
     * @return array
     */
    protected function getFieldOperators(): array
    {
        return [
            ['fieldName' => 'equal', 'fieldLabel' => '='],
            ['fieldName' => 'not_equal', 'fieldLabel' => '!='],
            ['fieldName' => 'contain', 'fieldLabel' => 'Zawiera'],
            ['fieldName' => 'not_contain', 'fieldLabel' => 'Nie zawiera'],
            ['fieldName' => 'start_from', 'fieldLabel' => 'Zaczyna się od'],
            ['fieldName' => 'not_start_from', 'fieldLabel' => 'Nie zaczyna się od'],
            ['fieldName' => 'is_defined', 'fieldLabel' => 'Jest zdefiniowany'],
            ['fieldName' => 'is_not_defined', 'fieldLabel' => 'Nie jest zdefiniowany'],
        ];
    }
}
