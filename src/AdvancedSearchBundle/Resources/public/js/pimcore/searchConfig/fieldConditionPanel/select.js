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


pimcore.registerNS("pimcore.bundle.advancedSearch.searchConfig.fieldConditionPanel.select");
pimcore.bundle.advancedSearch.searchConfig.fieldConditionPanel.select = Class.create(pimcore.bundle.advancedSearch.searchConfig.fieldConditionPanel.default, {

    getConditionPanel: function() {

        var optionStore =  Ext.create('Ext.data.Store', {
            autoDestroy: true,
            autoLoad: true,
            proxy: {
                type: 'ajax',
                url: "/admin/bundle/advanced-search/admin/get-field-options",
                extraParams: {
                    classId: this.classId,
                    fieldName: this.fieldSelectionInformation.fieldName
                },
                reader: {
                    type: 'json',
                    rootProperty: 'data',
                    transform: {
                        fn: function(data) {
                            let elements = [];

                            data.data.forEach(function (element) {
                                element['key'] = t(element['key']);

                                elements.push(element);
                            });
                            return elements;
                        },
                        scope: this
                    }
                }
            },
            fields: ['key', 'value'],
            pageSize: 0
        });

        this.termField = Ext.create('Ext.form.ComboBox',
            {
                fieldLabel:  t("bundle_advancedSearch_term"),
                width: 400,
                store: optionStore,
                typeAhead: true,
                queryDelay: 0,
                queryMode: "local",
                anyMatch: true,
                forceSelection: true,
                mode: 'local',
                style: "padding-left: 20px",
                valueField: 'value',
                displayField: 'key',
                value: this.data.filterEntryData,
                displayTpl: Ext.create('Ext.XTemplate',
                    '<tpl for=".">',
                    '{[Ext.util.Format.stripTags(values.key)]}',
                    '</tpl>'
                )
            }
        );

        this.inheritanceField = Ext.create('Ext.form.field.Checkbox',
            {
                fieldLabel:  t("bundle_advancedSearch_ignoreInheritance"),
                style: "padding-left: 20px",
                value: this.data.ignoreInheritance,
                hidden: !this.fieldSelectionInformation.context.classInheritanceEnabled
            }
        );

        return Ext.create('Ext.panel.Panel', {
            layout: 'hbox',
            items: [
                this.getOperatorCombobox(this.data.operator),
                this.termField,
                this.inheritanceField
            ]
        });
    }

});
