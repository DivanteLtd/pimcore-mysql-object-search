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
use ONGR\ElasticsearchDSL\Query\Compound\BoolQuery;
use ONGR\ElasticsearchDSL\Query\TermLevel\RangeQuery;
use ONGR\ElasticsearchDSL\Query\TermLevel\TermQuery;
use Pimcore\Model\DataObject\AbstractObject;
use Pimcore\Model\DataObject\Concrete;

/**
 * Class QuantityValue
 *
 * @package DivanteLtd\AdvancedSearchBundle\Filter\FieldDefinitionAdapter
 */
class QuantityValue extends Numeric implements IFieldDefinitionAdapter
{
    use Operators\NumericOperatorsTrait;

    /**
     * field type for search frontend
     *
     * @var string
     */
    protected $fieldType = 'quantityValue';

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
                            'properties' => [
                                'value' => [
                                    'type' => 'float',
                                ],
                                'unit' => [
                                    'type' => 'integer',
                                ],
                            ],
                        ],
                        static::ES_MAPPING_PROPERTY_NOT_INHERITED => [
                            'properties' => [
                                'value' => [
                                    'type' => 'float',
                                ],
                                'unit' => [
                                    'type' => 'integer',
                                ],
                            ],
                        ],
                    ],
                ],
            ];
        } else {
            return [
                $this->fieldDefinition->getName(),
                [
                    'properties' => [
                        'value' => [
                            'type' => 'float',
                        ],
                        'unit' => [
                            'type' => 'integer',
                        ],
                    ],

                ],
            ];
        }
    }

    /**
     * @param $fieldFilter
     *
     * filter field format as follows:
     *   - simple array with number/unitID like
     *       ["value" => 234.54, "unit" => 3]   --> creates TermQuery
     *   - array with gt, gte, lt, lte like
     *      ["value" => ["gte" => 40, "lte" => 45], "unit" => 3] --> creates RangeQuery
     * @param bool   $ignoreInheritance
     * @param string $path
     *
     * @return BuilderInterface
     */
    public function getQueryPart($fieldFilter, $ignoreInheritance = false, $path = '')
    {
        $boolQuery = new BoolQuery();
        $fieldPostFix = $this->buildQueryFieldPostfix($ignoreInheritance);

        if (is_array($fieldFilter) && is_array($fieldFilter['value'])) {
            $boolQuery->add(
                new RangeQuery(
                    $path . $this->fieldDefinition->getName() . $fieldPostFix . '.value',
                    $fieldFilter['value']
                )
            );
        } else {
            $boolQuery->add(
                new TermQuery(
                    $path . $this->fieldDefinition->getName() . $fieldPostFix . '.value',
                    $fieldFilter['value']
                )
            );
        }
        $boolQuery->add(
            new TermQuery(
                $path . $this->fieldDefinition->getName() . $fieldPostFix . '.unit',
                $fieldFilter['unit']
            )
        );

        return $boolQuery;
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
        unset($value['unitAbbreviation']);

        if ($ignoreInheritance) {
            AbstractObject::setGetInheritedValues($inheritanceBackup);
        }

        return $value;
    }

    /**
     * @return array
     */
    protected function getFieldSelectionContext(): array
    {
        return [
            'operators'               => $this->getFieldOperators(),
            'classInheritanceEnabled' => false,
            'units'                   => $this->fieldDefinition->getValidUnits(),
            'defaultUnit'             => $this->fieldDefinition->getDefaultUnit(),
        ];
    }
}
