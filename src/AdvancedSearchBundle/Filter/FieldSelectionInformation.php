<?php

namespace DivanteLtd\AdvancedSearchBundle\Filter;

/**
 * Class FieldSelectionInformation
 *
 * @package DivanteLtd\AdvancedSearchBundle\Filter
 */
class FieldSelectionInformation
{
    /**
     * @var string
     */
    protected $fieldName;

    /**
     * @var string
     */
    protected $fieldLabel;

    /**
     * @var string
     */
    protected $fieldType;

    /**
     * @var array
     */
    protected $context;

    /**
     * FieldSelectionInformation constructor.
     *
     * @param $fieldName
     * @param $fieldLabel
     * @param $fieldType
     * @param array $context
     */
    public function __construct($fieldName, $fieldLabel, $fieldType, $context = [])
    {
        $this->fieldName = $fieldName;
        $this->fieldLabel = $fieldLabel;
        $this->fieldType = $fieldType;
        $this->context = $context;
    }

    /**
     * @return string
     */
    public function getFieldName()
    {
        return $this->fieldName;
    }

    /**
     * @param string $fieldName
     */
    public function setFieldName($fieldName): void
    {
        $this->fieldName = $fieldName;
    }

    /**
     * @return string
     */
    public function getFieldLabel()
    {
        return $this->fieldLabel;
    }

    /**
     * @param string $fieldLabel
     */
    public function setFieldLabel($fieldLabel): void
    {
        $this->fieldLabel = $fieldLabel;
    }

    /**
     * @return string
     */
    public function getFieldType()
    {
        return $this->fieldType;
    }

    /**
     * @param string $fieldType
     */
    public function setFieldType($fieldType): void
    {
        $this->fieldType = $fieldType;
    }

    /**
     * @return array
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @param array $context
     */
    public function setContext($context): void
    {
        $this->context = $context;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'fieldName'  => $this->fieldName,
            'fieldLabel' => $this->fieldLabel,
            'fieldType'  => $this->fieldType,
            'context'    => $this->context,
        ];
    }
}
