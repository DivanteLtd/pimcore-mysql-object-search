<?php

namespace DivanteLtd\AdvancedSearchBundle\Filter\FieldDefinitionAdapter;

/**
 * Class ManyToManyObjectRelation
 * @package DivanteLtd\AdvancedSearchBundle\Filter\FieldDefinitionAdapter
 */
class ManyToManyObjectRelation extends ManyToOneRelation implements IFieldDefinitionAdapter
{
    use Operators\MultiRelatedOperatorsTrait;
}
