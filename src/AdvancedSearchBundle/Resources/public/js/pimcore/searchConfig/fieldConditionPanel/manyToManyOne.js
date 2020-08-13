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


pimcore.registerNS("pimcore.bundle.advancedSearch.searchConfig.fieldConditionPanel.manyToOneRelation");
pimcore.bundle.advancedSearch.searchConfig.fieldConditionPanel.manyToOneRelation = Class.create(pimcore.bundle.advancedSearch.searchConfig.fieldConditionPanel.default, {

    inheritanceField: null,

    getConditionPanel: function() {

        this.subPanel = Ext.create('Ext.panel.Panel', {});

        var typeStore =  Ext.create('Ext.data.ArrayStore', {
            fields: [ 'key', 'label'],
            data: this.fieldSelectionInformation.context.allowedTypes
        });

        this.typeField = Ext.create('Ext.form.ComboBox',
            {
                fieldLabel: t("bundle_advancedSearch_type"),
                store: typeStore,
                // value: data.condition,
                queryMode: 'local',
                width: 300,
                forceSelection: true,
                valueField: 'key',
                displayField: 'label',
                listeners: {
                    change: function( item, newValue, oldValue, eOpts ) {
                        this.subPanel.removeAll();
                        if(newValue != "object_filter") {
                            this.idsField = Ext.create('Ext.form.field.Text',
                                {
                                    value: this.data.filterEntryData && this.data.filterEntryData.id ? this.data.filterEntryData.id.join() : "",
                                    fieldCls: "pimcore_droptarget_input",
                                    listeners: {
                                        render: function (el) {
                                            new Ext.dd.DropZone(el.getEl(), {
                                                reference: this,
                                                ddGroup: 'element',
                                                getTargetFromEvent: function (e) {
                                                    return this.getEl();
                                                }.bind(el),

                                                onNodeOver: function (target, dd, e, data) {
                                                    data = data.records[0].data;

                                                    if (data.elementType == newValue) {
                                                        return Ext.dd.DropZone.prototype.dropAllowed;
                                                    }

                                                    return Ext.dd.DropZone.prototype.dropNotAllowed;
                                                },

                                                onNodeDrop: function (target, dd, e, data) {
                                                    data = data.records[0].data;

                                                    if (data.elementType == newValue) {
                                                        this.setValue(data.id);
                                                        return true;
                                                    }

                                                    return false;
                                                }.bind(el)
                                            });
                                        }
                                    }
                                }
                            );

                            this.tooltip = new Ext.tip.ToolTip({
                                target: this.idsField.getEl(),
                                html: ''
                            });

                            var items = [
                                this.idsField,
                                {
                                    xtype: "button",
                                    iconCls: "pimcore_icon_open",
                                    style: "margin-left: 5px",
                                    handler: function () {
                                        if (this.idsField && this.idsField.getValue()) {
                                            pimcore.helpers.openElement(this.idsField.getValue(), newValue);
                                        }
                                    }.bind(this)
                                },
                                {
                                    xtype: "button",
                                    iconCls: "pimcore_icon_delete",
                                    style: "margin-left: 5px",
                                    handler: function() {
                                        if (this.idsField) {
                                            this.idsField.setValue("");
                                            this.tooltip.setTarget(null);
                                            this.tooltip.setHtml("");
                                        }
                                    }.bind(this)
                                },
                                {
                                    xtype: "button",
                                    iconCls: "pimcore_icon_search",
                                    style: "margin-left: 5px",
                                    handler: function() {
                                        pimcore.helpers.itemselector(
                                            false,
                                            function (data) {
                                                this.idsField.setValue(data.id);
                                                this.tooltip.setTarget(this.idsField.getEl());
                                                this.tooltip.setHtml(data.fullpath);
                                            }.bind(this),
                                            {
                                                type: ["object"],
                                                subtype: {
                                                    object: ["object"]
                                                },
                                                specific: {
                                                    classes: this.fieldSelectionInformation.context.allowedClasses
                                                }
                                            },
                                            {
                                                context: Ext.apply(
                                                    {
                                                        scope: "objectEditor"
                                                    },
                                                    {
                                                        containerType: "object",
                                                        fieldname: null
                                                    }
                                                )
                                            }
                                        );
                                    }.bind(this)
                                }
                            ];

                            this.composite = Ext.create('Ext.form.FieldContainer', {
                                fieldLabel: t("bundle_advancedSearch_ids"),
                                width: 400,
                                layout: 'hbox',
                                items: items,
                                componentCls: "object_field",
                                border: false,
                                style: {
                                    padding: 0
                                }
                            });

                            this.subPanel.add(this.composite);
                        } else {

                            var classStore = pimcore.globalmanager.get("object_types_store");
                            var filteredClassStore = null;

                            if(this.fieldSelectionInformation.context.allowedClasses.length) {
                                var filteredClassStore = Ext.create('Ext.data.Store', {});

                                classStore.each(function(record) {
                                    if(this.fieldSelectionInformation.context.allowedClasses.indexOf(record.data.text) > -1) {
                                        filteredClassStore.add(record)
                                    }
                                }.bind(this));
                            } else {
                                filteredClassStore = classStore;
                            }


                            this.classSelection = Ext.create('Ext.form.ComboBox',
                                {
                                    fieldLabel: t("bundle_advancedSearch_subclass"),
                                    store: filteredClassStore,
                                    valueField: 'id',
                                    displayField: 'translatedText',
                                    triggerAction: 'all',
                                    value: this.data.filterEntryData ? this.data.filterEntryData.classId : "",
                                    queryMode: 'local',
                                    width: 300,
                                    forceSelection: true,
                                    listeners: {
                                        change: function( item, newValue, oldValue, eOpts ) {

                                            if(newValue != oldValue) {
                                                this.subConditionsPanel.removeAll();
                                                this.subConditions = new pimcore.bundle.advancedSearch.searchConfig.conditionPanel(newValue, null, "auto");
                                                this.subConditionsPanel.add(this.subConditions.getConditionPanel());
                                            }

                                        }.bind(this)
                                    }
                                }
                            );

                            this.subConditionsPanel = Ext.create('Ext.panel.Panel', {});

                            if(this.data.filterEntryData && this.data.filterEntryData.classId) {
                                this.subConditions = new pimcore.bundle.advancedSearch.searchConfig.conditionPanel(this.data.filterEntryData.classId, this.data.filterEntryData, "auto");
                                this.subConditionsPanel.add(this.subConditions.getConditionPanel());
                            }

                            this.subPanel.add(this.classSelection, this.subConditionsPanel);
                            pimcore.layout.refresh();

                        }
                    }.bind(this)
                }
            }
        );

        if(this.data.filterEntryData) {
            if(this.data.filterEntryData.id) {
                this.typeField.setValue("object");
            } else {
                this.typeField.setValue("object_filter");
            }
        }

        this.inheritanceField = Ext.create('Ext.form.field.Checkbox',
            {
                fieldLabel:  t("bundle_advancedSearch_ignoreInheritance"),
                style: "padding-left: 20px",
                value: this.data.ignoreInheritance,
                hidden: !this.fieldSelectionInformation.context.classInheritanceEnabled
            }
        );

        return Ext.create('Ext.panel.Panel', {
            items: [
                {
                    xtype: 'panel',
                    layout: 'hbox',
                    style: "padding-bottom: 10px",
                    items: [
                        this.typeField,
                        this.inheritanceField,
                        this.getOperatorCombobox('eq')
                    ]
                },
                this.subPanel
            ]
        });
    },

    getFilterValues: function() {

        var subValue = {};

        if(this.typeField.getValue() == "object_filter") {

            subValue.type = "object";
            subValue.classId = this.classSelection.getValue();
            if(this.subConditions) {
                var saveData = this.subConditions.getSaveData();
                subValue.filters = saveData.filters;
                subValue.fulltextSearchTerm = saveData.fulltextSearchTerm;
            }

        } else {

            subValue.type = "object";
            if(this.idsField) {
                subValue.id = this.idsField.getValue().split(",");
            }

        }

        return {
            fieldname: this.fieldSelectionInformation.fieldName,
            filterEntryData: subValue,
            operator: this.operatorField.getValue(),
            ignoreInheritance: this.inheritanceField.getValue()
        };
    }


});
