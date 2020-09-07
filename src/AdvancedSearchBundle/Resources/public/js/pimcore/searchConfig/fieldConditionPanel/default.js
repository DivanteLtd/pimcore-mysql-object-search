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


pimcore.registerNS("pimcore.bundle.advancedSearch.searchConfig.fieldConditionPanel.default");
pimcore.bundle.advancedSearch.searchConfig.fieldConditionPanel.default = Class.create({

    fieldSelectionInformation: null,
    data: {},
    termField: null,
    operatorField: null,
    inheritanceField: null,
    classId: null,

    initialize: function(fieldSelectionInformation, data, classId) {
        this.fieldSelectionInformation = fieldSelectionInformation;
        this.classId = classId;
        if(data) {
            this.data = data;
        }
    },

    getConditionPanel: function() {

        this.termField = Ext.create('Ext.form.field.Text',
            {
                fieldLabel:  t("bundle_advancedSearch_term"),
                width: 400,
                style: "padding-left: 20px",
                value: this.data.filterEntryData
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

        var operatorCombobox = this.getOperatorCombobox(this.data.operator);
        operatorCombobox.on('change', function( item, newValue, oldValue, eOpts ) {
            this.termField.setHidden(newValue == "is_defined" || newValue == "is_not_defined");
        }.bind(this));

        return Ext.create('Ext.panel.Panel', {
            layout: 'hbox',
            items: [
                operatorCombobox,
                this.termField,
                this.inheritanceField
            ]
        });
    },

    getOperatorCombobox: function(value) {
        const operators = Ext.create('Ext.data.Store', {
            fields: ['fieldName', 'fieldLabel'],
            data : this.fieldSelectionInformation.context.operators
        });

        this.operatorField = Ext.create('Ext.form.ComboBox',
            {
                fieldLabel:  t("bundle_advancedSearch_operator"),
                store: operators,
                queryMode: 'local',
                width: 300,
                valueField: 'fieldName',
                displayField: 'fieldLabel',
                value
            }
        );

        var operator = operators.data.items[0];

        if (this.data.operator) {
            operator = this.data.operator;
            if (this.termField) {
                this.termField.setHidden(operator == "is_defined" || operator == "is_not_defined");
            }
        }

        this.operatorField.setValue(operator);

        return this.operatorField;
    },

    getFilterValues: function() {

       return {
            fieldname: this.fieldSelectionInformation.fieldName,
            filterEntryData: this.termField.getValue(),
            operator: this.operatorField.getValue(),
            ignoreInheritance: this.inheritanceField.getValue()
        };

    }


});
