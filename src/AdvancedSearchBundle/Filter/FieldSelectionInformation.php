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
     * @param string $fieldName
     * @param string $fieldLabel
     * @param string $fieldType
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
     * @return void
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
     * @return void
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
     * @return void
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
     * @return void
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
            'fieldName' => $this->fieldName,
            'fieldLabel' => $this->fieldLabel,
            'fieldType' => $this->fieldType,
            'context' => $this->context,
        ];
    }
}
