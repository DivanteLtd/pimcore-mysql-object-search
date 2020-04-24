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

use DivanteLtd\AdvancedSearchBundle\Filter\FieldSelectionInformation;
use DivanteLtd\AdvancedSearchBundle\Service\FilterService as Service;
use ONGR\ElasticsearchDSL\BuilderInterface;
use ONGR\ElasticsearchDSL\Query\FullText\QueryStringQuery;
use ONGR\ElasticsearchDSL\Query\TermLevel\ExistsQuery;
use Pimcore\Model\DataObject\AbstractObject;
use Pimcore\Model\DataObject\ClassDefinition\Data;
use Pimcore\Model\DataObject\Concrete;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class DefaultAdapter
 *
 * @package DivanteLtd\AdvancedSearchBundle\Filter\FieldDefinitionAdapter
 */
class DefaultAdapter implements IFieldDefinitionAdapter
{
    /**
     * field type for search frontend
     *
     * @var string
     */
    protected $fieldType = 'default';

    /**
     * @var Data
     */
    protected $fieldDefinition;

    /**
     * @var bool
     */
    protected $considerInheritance;

    /**
     * @var Service
     */
    protected $service;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * DefaultAdapter constructor.
     * @param Service $service
     * @param TranslatorInterface $translator
     */
    public function __construct(Service $service, TranslatorInterface $translator)
    {
        $this->service = $service;
        $this->translator = $translator;
    }

    /**
     * @param Data $fieldDefinition
     * @return void
     */
    public function setFieldDefinition(Data $fieldDefinition): void
    {
        $this->fieldDefinition = $fieldDefinition;
    }

    /**
     * @param bool $considerInheritance
     * @return void
     */
    public function setConsiderInheritance(bool $considerInheritance): void
    {
        $this->considerInheritance = $considerInheritance;
    }

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
                            'type'   => 'string',
                            'fields' => [
                                'raw' => ['type' => 'string', 'index' => 'not_analyzed'],
                            ],
                        ],
                        static::ES_MAPPING_PROPERTY_NOT_INHERITED => [
                            'type'   => 'string',
                            'fields' => [
                                'raw' => ['type' => 'string', 'index' => 'not_analyzed'],
                            ],
                        ],
                    ],
                ],
            ];
        } else {
            return [
                $this->fieldDefinition->getName(),
                [
                    'type'   => 'string',
                    'fields' => [
                        'raw' => ['type' => 'string', 'index' => 'not_analyzed'],
                    ],
                ],
            ];
        }
    }

    /**
     * @param Concrete $object
     * @param bool $ignoreInheritance
     * @return string
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

        return (string) $value;
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

            $returnValue = null;
            if ($value) {
                $returnValue[static::ES_MAPPING_PROPERTY_STANDARD] = $value;
            }

            if ($notInheritedValue) {
                $returnValue[static::ES_MAPPING_PROPERTY_NOT_INHERITED] = $notInheritedValue;
            }

            return $returnValue;
        } else {
            if ($value) {
                return $value;
            } else {
                return null;
            }
        }
    }

    /**
     * @param bool $ignoreInheritance
     *
     * @return string
     */
    protected function buildQueryFieldPostfix($ignoreInheritance = false)
    {
        $postfix = '';

        if ($this->considerInheritance) {
            if ($ignoreInheritance) {
                $postfix = '.' . static::ES_MAPPING_PROPERTY_NOT_INHERITED;
            } else {
                $postfix = '.' . static::ES_MAPPING_PROPERTY_STANDARD;
            }
        }

        return $postfix;
    }

    /**
     * @param mixed $fieldFilter
     *
     * filter field format as follows:
     *   - simple string like
     *       "filter for value"  --> creates QueryStringQuery
     * @param bool $ignoreInheritance
     * @param string $path
     *
     * @return QueryStringQuery
     */
    public function getQueryPart($fieldFilter, $ignoreInheritance = false, $path = '')
    {
        return new QueryStringQuery(
            $fieldFilter,
            [
                'fields' => [
                    $path . $this->fieldDefinition->getName() . $this->buildQueryFieldPostfix($ignoreInheritance),
                ],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getExistsFilter($fieldFilter, $ignoreInheritance = false, $path = '')
    {
        return new ExistsQuery(
            $path . $this->fieldDefinition->getName() . $this->buildQueryFieldPostfix($ignoreInheritance)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldSelectionInformation()
    {
        return [new FieldSelectionInformation(
            $this->fieldDefinition->getName(),
            $this->fieldDefinition->getTitle(),
            $this->fieldType,
            $this->getFieldSelectionContext()
        )];
    }

    /**
     * @return array
     */
    protected function getFieldSelectionContext(): array
    {
        $fieldOperators = array_map(
            function ($operator) {
                $operator['fieldLabel'] = $this->translator->trans($operator['fieldLabel'], [], 'admin');

                return $operator;
            },
            $this->getFieldOperators()
        );

        return [
            'operators'               => $fieldOperators,
            'classInheritanceEnabled' => false,
        ];
    }

    /**
     * @return array
     */
    protected function getFieldOperators(): array
    {
        return [];
    }
}
