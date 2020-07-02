<?php

namespace DivanteLtd\AdvancedSearchBundle\Filter\FieldDefinitionAdapter;

/**
 * Class AdvancedManyToManyObjectRelation
 * @package DivanteLtd\AdvancedSearchBundle\Filter\FieldDefinitionAdapter
 */
class AdvancedManyToManyObjectRelation extends ManyToOneRelation implements IFieldDefinitionAdapter
{
    use Operators\MultiRelatedOperatorsTrait;
}
