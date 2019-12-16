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

use Pimcore\Model\DataObject\AbstractObject;
use Pimcore\Model\DataObject\Concrete;

/**
 * Class Select
 *
 * @package DivanteLtd\AdvancedSearchBundle\Filter\FieldDefinitionAdapter
 */
class Select extends DefaultAdapter implements IFieldDefinitionAdapter
{
    use Operators\SelectOperatorsTrait;

    /**
     * field type for search frontend
     *
     * @var string
     */
    protected $fieldType = 'select';

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
                            'type'  => 'string',
                            'index' => 'not_analyzed',
                        ],
                        static::ES_MAPPING_PROPERTY_NOT_INHERITED => [
                            'type'  => 'string',
                            'index' => 'not_analyzed',
                        ],
                    ],
                ],
            ];
        } else {
            return [
                $this->fieldDefinition->getName(),
                [
                    'type'  => 'string',
                    'index' => 'not_analyzed',
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

        return $value;
    }
}
