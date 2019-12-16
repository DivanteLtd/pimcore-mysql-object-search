<?php
/**
 * @category    pimcore
 * @date        03/10/2019 13:49
 *
 * @author      Korneliusz Kirsz <kkirsz@divante.co>
 * @copyright   Copyright (c) 2019 Divante Ltd. (https://divante.co)
 */

namespace DivanteLtd\AdvancedSearchBundle\Filter\FieldDefinitionAdapter;

/**
 * Class Input
 *
 * @package DivanteLtd\AdvancedSearchBundle\Filter\FieldDefinitionAdapter
 */
class Input extends DefaultAdapter implements IFieldDefinitionAdapter
{
    use Operators\CharacterOperatorsTrait;
}
