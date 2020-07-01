<?php

namespace DivanteLtd\AdvancedSearchBundle;

use Pimcore\Extension\Bundle\AbstractPimcoreBundle;
use Pimcore\Extension\Bundle\Traits\PackageVersionTrait;

/**
 * Class AdvancedSearchBundle
 *
 * @package DivanteLtd\AdvancedSearchBundle
 */
class AdvancedSearchBundle extends AbstractPimcoreBundle
{
    use PackageVersionTrait;

    /**
     * @return Installer
     */
    public function getInstaller()
    {
        return $this->container->get(Installer::class);
    }

    /**
     * @return array
     */
    public function getJsPaths()
    {
        return [
            '/bundles/advancedsearch/js/pimcore/startup.js',
            '/bundles/advancedsearch/js/pimcore/selector.js',
            '/bundles/advancedsearch/js/pimcore/helper.js',
            '/bundles/advancedsearch/js/pimcore/searchConfigPanel.js',
            '/bundles/advancedsearch/js/pimcore/searchConfig/resultExtension.js',
            '/bundles/advancedsearch/js/pimcore/searchConfig/resultPanel.js',
            '/bundles/advancedsearch/js/pimcore/searchConfig/conditionPanel.js',
            '/bundles/advancedsearch/js/pimcore/searchConfig/conditionPanelContainerBuilder.js',
            '/bundles/advancedsearch/js/pimcore/searchConfig/conditionAbstractPanel.js',
            '/bundles/advancedsearch/js/pimcore/searchConfig/conditionEntryPanel.js',
            '/bundles/advancedsearch/js/pimcore/searchConfig/fieldConditionPanel/default.js',
            '/bundles/advancedsearch/js/pimcore/searchConfig/fieldConditionPanel/localizedfields.js',
            '/bundles/advancedsearch/js/pimcore/searchConfig/fieldConditionPanel/numeric.js',
            '/bundles/advancedsearch/js/pimcore/searchConfig/fieldConditionPanel/input.js',
            '/bundles/advancedsearch/js/pimcore/searchConfig/fieldConditionPanel/checkbox.js',
            '/bundles/advancedsearch/js/pimcore/searchConfig/fieldConditionPanel/select.js',
            '/bundles/advancedsearch/js/pimcore/searchConfig/fieldConditionPanel/language.js',
            '/bundles/advancedsearch/js/pimcore/searchConfig/fieldConditionPanel/country.js',
            '/bundles/advancedsearch/js/pimcore/searchConfig/fieldConditionPanel/user.js',
            '/bundles/advancedsearch/js/pimcore/searchConfig/fieldConditionPanel/multiselect.js',
            '/bundles/advancedsearch/js/pimcore/searchConfig/fieldConditionPanel/countrymultiselect.js',
            '/bundles/advancedsearch/js/pimcore/searchConfig/fieldConditionPanel/languagemultiselect.js',
            '/bundles/advancedsearch/js/pimcore/searchConfig/fieldConditionPanel/datetime.js',
            '/bundles/advancedsearch/js/pimcore/searchConfig/fieldConditionPanel/date.js',
            '/bundles/advancedsearch/js/pimcore/searchConfig/fieldConditionPanel/time.js',
            '/bundles/advancedsearch/js/pimcore/searchConfig/fieldConditionPanel/quantityValue.js',
            '/bundles/advancedsearch/js/pimcore/searchConfig/fieldConditionPanel/manyToManyOne.js',
        ];
    }

    /**
     * @return array
     */
    public function getCssPaths()
    {
        return [
            '/bundles/advancedsearch/css/admin.css',
        ];
    }

    /**
     * @return string
     */
    protected function getComposerPackageName(): string
    {
        return 'divante-ltd/pimcore-mysql-object-search';
    }
}
