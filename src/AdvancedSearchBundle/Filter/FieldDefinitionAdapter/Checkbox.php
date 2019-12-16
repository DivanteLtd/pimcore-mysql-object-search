<?php
/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Enterprise License (PEL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 * @license    http://www.pimcore.org/license     GPLv3 and PEL
 */

namespace DivanteLtd\AdvancedSearchBundle\Filter\FieldDefinitionAdapter;

use ONGR\ElasticsearchDSL\BuilderInterface;
use ONGR\ElasticsearchDSL\Query\TermLevel\TermQuery;
use Pimcore\Model\DataObject\AbstractObject;
use Pimcore\Model\DataObject\Concrete;

/**
 * Class Checkbox
 *
 * @package DivanteLtd\AdvancedSearchBundle\Filter\FieldDefinitionAdapter
 */
class Checkbox extends DefaultAdapter implements IFieldDefinitionAdapter
{
    use Operators\LogicalOperatorsTrait;

    /**
     * field type for search frontend
     *
     * @var string
     */
    protected $fieldType = 'checkbox';

    /**
     * @return array
     */
    public function getESMapping()
    {
        if ($this->considerInheritance) {
            return [
                $this->fieldDefinition->getName(),
                [
                    'properties' => [
                        static::ES_MAPPING_PROPERTY_STANDARD => [
                            'type' => 'boolean',
                        ],
                        static::ES_MAPPING_PROPERTY_NOT_INHERITED => [
                            'type' => 'boolean',
                        ],
                    ],
                ],
            ];
        } else {
            return [
                $this->fieldDefinition->getName(),
                [
                    'type' => 'boolean',
                ],
            ];
        }
    }

    /**
     * @param Concrete $object
     * @param bool     $ignoreInheritance
     */
    protected function doGetIndexDataValue($object, $ignoreInheritance = false)
    {
        $inheritanceBackup = null;
        if ($ignoreInheritance) {
            $inheritanceBackup = AbstractObject::getGetInheritedValues();
            AbstractObject::setGetInheritedValues(false);
        }

        $value = $this->fieldDefinition->getForWebserviceExport($object);

        if ($ignoreInheritance) {
            AbstractObject::setGetInheritedValues($inheritanceBackup);
        }

        return (bool) $value;
    }

    /**
     * @param Concrete $object
     *
     * @return mixed
     */
    public function getIndexData($object)
    {
        $value = $this->doGetIndexDataValue($object, false);

        if ($this->considerInheritance) {
            $notInheritedValue = $this->doGetIndexDataValue($object, true);

            $returnValue = [];
            $returnValue[static::ES_MAPPING_PROPERTY_STANDARD] = $value;
            $returnValue[static::ES_MAPPING_PROPERTY_NOT_INHERITED] = $notInheritedValue;

            return $returnValue;
        } else {
            return $value;
        }
    }

    /**
     * @param $fieldFilter
     *
     * filter field format as follows:
     *   - simple boolean like
     *       true | false  --> creates QueryStringQuery
     * @param bool   $ignoreInheritance
     * @param string $path
     *
     * @return BuilderInterface
     */
    public function getQueryPart($fieldFilter, $ignoreInheritance = false, $path = '')
    {
        return new TermQuery(
            $path . $this->fieldDefinition->getName() . $this->buildQueryFieldPostfix($ignoreInheritance),
            $fieldFilter
        );
    }
}
