<?php

namespace DivanteLtd\AdvancedSearchBundle\Service;

use DivanteLtd\AdvancedSearchBundle\Filter\FieldDefinitionAdapter\IFieldDefinitionAdapter;
use DivanteLtd\AdvancedSearchBundle\Filter\FieldSelectionInformation;
use Pimcore\Bundle\AdminBundle\Security\User\TokenStorageUserResolver;
use Pimcore\Model\DataObject\ClassDefinition;
use Pimcore\Model\DataObject\Fieldcollection\Definition;
use Pimcore\Model\DataObject\Listing;
use Pimcore\Model\User;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

/**
 * Class FilterService
 *
 * @package DivanteLtd\AdvancedSearchBundle
 */
class FilterService
{
    private const OPERATORS = [
        'contain' => [
            'expression' => '%s LIKE ?',
            'data' => '%_data_%',
        ],
        'not_contain' => [
            'expression' => '(%s LIKE ?) IS NOT TRUE',
            'data' => '%_data_%',
        ],
        'equal' => [
            'expression' => '%s = ?',
            'data' => '_data_',
        ],
        'not_equal' => [
            'expression' => '!(%s <=> ?)',
            'data' => '_data_',
        ],
        'start_from' => [
            'expression' => '%s LIKE ?',
            'data' => '_data_%',
        ],
        'not_start_from' => [
            'expression' => '(%s LIKE ?) IS NOT TRUE',
            'data' => '_data_%',
        ],
        'is_defined' => [
            'expression' => 'LENGTH(%s) > ?',
            'data' => '0',
        ],
        'is_not_defined' => [
            'expression' => '(LENGTH(%s) > ?) IS NOT TRUE',
            'data' => '0',
        ],
        'less' => [
            'expression' => '%s < ?',
            'data' => '_data_',
        ],
        'less_equal' => [
            'expression' => '%s <= ?',
            'data' => '_data_',
        ],
        'greater' => [
            'expression' => '%s > ?',
            'data' => '_data_',
        ],
        'greater_equal' => [
            'expression' => '%s > ?',
            'data' => '_data_',
        ],
        'is_null' => [
            'expression' => '%s IS NULL',
            'data' => '_data_',
        ],
    ];

    /**
     * @var User
     */
    protected $user;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ContainerInterface
     */
    protected $filterLocator;

    /**
     * Service constructor.
     *
     * @param LoggerInterface $logger
     * @param TokenStorageUserResolver $userResolver
     * @param ContainerInterface $filterLocator
     */
    public function __construct(
        LoggerInterface $logger,
        TokenStorageUserResolver $userResolver,
        ContainerInterface $filterLocator
    ) {
        $this->user = $userResolver->getUser();
        $this->logger = $logger;
        $this->filterLocator = $filterLocator;
    }

    /**
     * returns field definition adapter for given field definition
     *
     * @param ClassDefinition\Data $fieldDefinition
     * @param bool $considerInheritance
     *
     * @return IFieldDefinitionAdapter|null
     */
    public function getFieldDefinitionAdapter(ClassDefinition\Data $fieldDefinition, bool $considerInheritance)
    {
        $adapter = null;

        if ($this->filterLocator->has($fieldDefinition->fieldtype)) {
            $adapter = $this->filterLocator->get($fieldDefinition->fieldtype);
            $adapter->setConsiderInheritance($considerInheritance);
            $adapter->setFieldDefinition($fieldDefinition);
        }

        return $adapter;
    }

    /**
     * returns selectable fields with their type information for search frontend
     *
     * @param ClassDefinition|Definition $definition
     * @param bool $allowInheritance
     * @return FieldSelectionInformation[]
     */
    public function getFieldSelectionInformationForClassDefinition($definition, $allowInheritance = false)
    {
        $fieldEntries = [];

        $fieldDefinitions = $definition->getFieldDefinitions();
        foreach ($fieldDefinitions as $fieldDefinition) {
            $fieldAdapter = $this->getFieldDefinitionAdapter($fieldDefinition, $allowInheritance);
            if ($fieldAdapter instanceof IFieldDefinitionAdapter) {
                $fieldEntries = array_merge(
                    $fieldEntries,
                    $fieldAdapter->getFieldSelectionInformation()
                );
            }
        }

        return $fieldEntries;
    }

    /**
     * @param int $classId
     * @param string $fieldName
     *
     * @return array
     * @throws \Exception
     *
     */
    public function getFieldOptions(int $classId, string $fieldName): array
    {
        $class = ClassDefinition::getById($classId);

        if (!$class instanceof ClassDefinition) {
            throw new \UnexpectedValueException(sprintf(
                'No class was found with ID %d',
                $classId
            ));
        }

        $field = $class->getFieldDefinition($fieldName, ['suppressEnrichment' => false]);

        if (!method_exists($field, 'getOptions')) {
            return [];
        }

        $options = [];

        if (!$field->getMandatory()) {
            $empty = [
                'key' => 'empty',
                'value' => 'null',
            ];

            $options[] = $empty;
        }

        return array_merge($options, $field->getOptions());
    }

    /**
     * @param Listing $listing
     * @param array $conditions
     *
     * @return Listing
     */
    public function doFilter(Listing $listing, array $conditions): Listing
    {
        $filters = $conditions['filters'];
        $operator = $conditions['operator'];

        foreach ($filters ?? [] as $filter) {
            $listing = $this->processFilter($listing, $filter, $operator);
        }

        return $listing;
    }

    /**
     * @param Listing $listing
     * @param array $filter
     * @param string $operator
     * @return Listing
     */
    protected function processFilter(Listing $listing, array $filter, string $operator): Listing
    {
        $operation = $filter['operator'];

        $expression = sprintf(static::OPERATORS[$operation]['expression'], $filter['fieldname']);
        $data = str_replace('_data_', $filter['filterEntryData'], static::OPERATORS[$operation]['data']);

        if ($filter['operator'] === 'equal' && $data === 'null') {
            $expression = sprintf(static::OPERATORS['is_null']['expression'], $filter['fieldname']);
        }

        $number = mt_rand(1, 10000);
        $expression = sprintf('%s = %s AND %s', $number, $number, $expression);

        $listing->addConditionParam($expression, $data, $operator);

        return $listing;
    }
}
