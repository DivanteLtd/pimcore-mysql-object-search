<?php

namespace DivanteLtd\AdvancedSearchBundle\Filter\FieldDefinitionAdapter;

use DivanteLtd\AdvancedSearchBundle\Filter\FieldSelectionInformation;
use DivanteLtd\AdvancedSearchBundle\Service\FilterService as Service;
use ONGR\ElasticsearchDSL\BuilderInterface;
use ONGR\ElasticsearchDSL\Query\TermLevel\ExistsQuery;
use Pimcore\Model\DataObject\ClassDefinition\Data;
use Pimcore\Model\DataObject\Concrete;

/**
 * Interface IFieldDefinitionAdapter
 *
 * @package DivanteLtd\AdvancedSearchBundle\Filter\FieldDefinitionAdapter
 */
interface IFieldDefinitionAdapter
{
    const ES_MAPPING_PROPERTY_STANDARD = 'standard';
    const ES_MAPPING_PROPERTY_NOT_INHERITED = 'notInherited';

    /**
     * IFieldDefinitionAdapter constructor.
     *
     * @param Service $service
     */
    public function __construct(Service $service);

    /**
     * @param Data $fieldDefinition
     */
    public function setFieldDefinition(Data $fieldDefinition);

    /**
     * @param bool $considerInheritance
     */
    public function setConsiderInheritance(bool $considerInheritance);

    /**
     * @return array
     */
    public function getESMapping();

    /**
     * @param Concrete $object
     *
     * @return array
     */
    public function getIndexData($object);

    /**
     * @param $fieldFilter - see concrete implementations for format
     * @param string $path              - sub path for nested objects (only needed internally)
     * @param bool   $ignoreInheritance - if true inheritance is not considered during query
     *
     * @return BuilderInterface
     */
    public function getQueryPart($fieldFilter, $ignoreInheritance = false, $path = '');

    /**
     * @param $fieldFilter - see concrete implementations for format
     * @param bool   $ignoreInheritance - if true inheritance is not considered during query
     * @param string $path              - sub path for nested objects (only needed internally)
     *
     * @return ExistsQuery
     */
    public function getExistsFilter($fieldFilter, $ignoreInheritance = false, $path = '');

    /**
     * returns selectable fields with their type information for search frontend
     *
     * @return FieldSelectionInformation[]
     */
    public function getFieldSelectionInformation();
}
