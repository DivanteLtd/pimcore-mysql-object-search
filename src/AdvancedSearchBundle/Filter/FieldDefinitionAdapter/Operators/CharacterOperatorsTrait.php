<?php
/**
 * @category    pimcore
 * @date        02/10/2019 16:06
 *
 * @author      Korneliusz Kirsz <kkirsz@divante.co>
 * @copyright   Copyright (c) 2019 Divante Ltd. (https://divante.co)
 */

namespace DivanteLtd\AdvancedSearchBundle\Filter\FieldDefinitionAdapter\Operators;

use Symfony\Component\Translation\TranslatorInterface;

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
        $operators = [
            'equal',
            'not_equal',
            'contain',
            'not_contain',
            'start_from',
            'not_start_from',
            'is_defined',
            'is_not_defined',
        ];

        return array_map(
            function ($operator) {
                return [
                    'fieldName' => $operator,
                    'fieldLabel' => 'bundle_advancedSearch_character_operator_' . $operator
                ];
            },
            $operators
        );
    }
}
