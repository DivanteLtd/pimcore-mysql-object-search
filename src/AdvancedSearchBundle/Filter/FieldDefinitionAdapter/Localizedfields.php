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
use DivanteLtd\AdvancedSearchBundle\Filter\FilterEntry;
use DivanteLtd\AdvancedSearchBundle\Service\FilterService as Service;
use ONGR\ElasticsearchDSL\BuilderInterface;
use ONGR\ElasticsearchDSL\Query\Compound\BoolQuery;
use ONGR\ElasticsearchDSL\Query\Joining\NestedQuery;
use Pimcore\Localization\LocaleServiceInterface;
use Pimcore\Model\DataObject\ClassDefinition\Data;
use Pimcore\Model\DataObject\Concrete;
use Pimcore\Tool;

/**
 * Class Localizedfields
 *
 * @package DivanteLtd\AdvancedSearchBundle\Filter\FieldDefinitionAdapter
 */
class Localizedfields extends DefaultAdapter implements IFieldDefinitionAdapter
{
    /**
     * @var Data\Localizedfields
     */
    protected $fieldDefinition;

    /**
     * @var Locale
     */
    protected $localeService;

    /**
     * Localizedfields constructor.
     *
     * @param Service                     $service
     * @param LocaleServiceInterface|null $locale
     *
     * @throws \Exception
     */
    public function __construct(Service $service, LocaleServiceInterface $locale = null)
    {
        parent::__construct($service);

        $this->localeService = $locale;
        if (!$locale) {
            throw new \InvalidArgumentException('Locale not set');
        }
    }

    /**
     * @return array
     */
    public function getESMapping()
    {
        $children = $this->fieldDefinition->getFieldDefinitions();
        $childProperties = [];
        foreach ($children as $child) {
            $fieldAdapter = $this->service->getFieldDefinitionAdapter($child, $this->considerInheritance);
            list($key, $mappingEntry) = $fieldAdapter->getESMapping();
            $childProperties[$key] = $mappingEntry;
        }

        $mappingProperties = [];
        $languages = Tool::getValidLanguages();

        foreach ($languages as $language) {
            $mappingProperties[$language] = [
                'type'       => 'nested',
                'properties' => $childProperties,
            ];
        }

        return [
            $this->fieldDefinition->getName(),
            [
                'type'       => 'nested',
                'properties' => $mappingProperties,
            ],
        ];
    }

    /**
     * @param Concrete $object
     *
     * @return array
     */
    public function getIndexData($object)
    {
        $localeBackup = $this->localeService->getLocale();

        $validLanguages = Tool::getValidLanguages();

        $localizedFieldData = [];

        if ($validLanguages) {
            foreach ($validLanguages as $language) {
                foreach ($this->fieldDefinition->getFieldDefinitions() as $key => $fieldDefinition) {
                    $this->localeService->setLocale($language);

                    $fieldAdapter = $this->service->getFieldDefinitionAdapter(
                        $fieldDefinition,
                        $this->considerInheritance
                    );
                    $localizedFieldData[$language][$key] = $fieldAdapter->getIndexData($object);
                }
            }
        }

        $this->localeService->setLocale($localeBackup);

        return $localizedFieldData;
    }

    /**
     * @param array $fieldFilter
     *
     * filter field format as follows:
     *  stdObject with language as key and languageFilter array as values like
     *    [
     *      'en' => FilterEntry[]  - FULL FEATURES FILTER ENTRY ARRAY
     *    ]
     *   e.g.
     *      'en' => [
     *          ["fieldnme" => "locname", "filterEntryData" => "englname"]
     *       ]
     * @param bool   $ignoreInheritance
     * @param string $path
     *
     * @return BoolQuery
     */
    public function getQueryPart($fieldFilter, $ignoreInheritance = false, $path = '')
    {
        $languageQueries = [];
        foreach ($fieldFilter as $language => $languageFilters) {
            $path = $path . $this->fieldDefinition->getName() . '.' . $language;
            $languageBoolQuery = new BoolQuery();

            foreach ($languageFilters as $localizedFieldFilter) {
                $filterEntryObject = $this->service->buildFilterEntryObject($localizedFieldFilter);
                if ($filterEntryObject->getFilterEntryData() instanceof BuilderInterface) {
                    // add given builder interface without any further processing
                    $languageBoolQuery->add(
                        $filterEntryObject->getFilterEntryData(),
                        $filterEntryObject->getOuterOperator()
                    );
                } else {
                    $fieldDefinitionValue = $this->fieldDefinition->getFielddefinition(
                        $filterEntryObject->getFieldname()
                    );
                    $fieldAdapter = $this->service->getFieldDefinitionAdapter(
                        $fieldDefinitionValue,
                        $this->considerInheritance
                    );

                    if ($filterEntryObject->getOperator() == FilterEntry::EXISTS ||
                        $filterEntryObject->getOperator() == FilterEntry::NOT_EXISTS) {
                        //add exists filter generated by filter definition adapter
                        $languageBoolQuery->add(
                            $fieldAdapter->getExistsFilter(
                                $filterEntryObject->getFilterEntryData(),
                                $filterEntryObject->isIgnoreInheritance(),
                                $path . '.'
                            ),
                            $filterEntryObject->getOuterOperator()
                        );
                    } else {
                        //add query part generated by filter definition adapter
                        $languageBoolQuery->add(
                            $fieldAdapter->getQueryPart(
                                $filterEntryObject->getFilterEntryData(),
                                $filterEntryObject->isIgnoreInheritance(),
                                $path . '.'
                            ),
                            $filterEntryObject->getOuterOperator()
                        );
                    }
                }
            }
            $languageQueries[] = new NestedQuery($path, $languageBoolQuery);
        }

        if (count($languageQueries) == 1) {
            return $languageQueries[0];
        } else {
            $boolQuery = new BoolQuery();
            foreach ($languageQueries as $query) {
                $boolQuery->add($query);
            }

            return $boolQuery;
        }
    }

    /**
     * returns selectable fields with their type information for search frontend
     *
     * @return FieldSelectionInformation[]
     */
    public function getFieldSelectionInformation()
    {
        $fieldEntries = [];

        $children = $this->fieldDefinition->getFieldDefinitions();
        foreach ($children as $child) {
            $fieldAdapter = $this->service->getFieldDefinitionAdapter($child, $this->considerInheritance);

            if ($fieldAdapter instanceof IFieldDefinitionAdapter) {
                $subEntries = $fieldAdapter->getFieldSelectionInformation();
                foreach ($subEntries as $subEntry) {
                    $context = $subEntry->getContext();
                    $context['subType'] = $subEntry->getFieldType();
                    $context['languages'] = Tool::getValidLanguages();
                    $subEntry->setContext($context);

                    $subEntry->setFieldType('localizedfields');
                }

                $fieldEntries = array_merge($fieldEntries, $subEntries);
            }
        }

        return $fieldEntries;
    }
}
