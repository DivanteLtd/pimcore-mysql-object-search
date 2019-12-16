pimcore.registerNS("pimcore.bundle.advancedSearch.searchConfig.conditionPanel");

pimcore.bundle.advancedSearch.searchConfig.conditionPanel = Class.create({

    classId: null,
    data: {},
    conditionEntryPanelLayout: "hbox",

    initialize: function (classId, data, conditionEntryPanelLayout) {
        this.classId = classId;
        if (data) {
            this.data = data;
        }
        if (conditionEntryPanelLayout) {
            this.conditionEntryPanelLayout = conditionEntryPanelLayout;
        }
    },

    getPanel: function () {
        if (!this.panel) {
            return this.getConditionPanel();
        }

        return this.panel;
    },

    getConditionPanel: function() {
        var helper = new pimcore.bundle.advancedSearch.searchConfig.conditionPanelContainerBuilder(this.classId, this, "root-panel", this.conditionEntryPanelLayout);
        this.conditionsContainerInner = helper.buildConditionsContainerInner();

        var operator = 'AND';

        if (this.data.operator) {
            operator = this.data.operator
        }

        this.mainOperatorField = Ext.create('Ext.form.ComboBox', {
            fieldLabel:  t("bundle_advancedSearch_operator"),
            store: ['AND', 'OR'],
            value: operator,
            queryMode: 'local',
            width: 300,
            valueField: 'fieldName',
            displayField: 'fieldLabel'
        });

        if (this.data.filters) {
            helper.populateConditionsContainerInner(this.data.filters);
        }

        this.panel = Ext.create('Ext.panel.Panel', {
            border: false,
            items: [
                this.mainOperatorField,
                this.conditionsContainerInner
            ]
        });

        return this.panel;
    },

    getSaveData: function() {
        var conditionsData = [];
        var conditions = this.conditionsContainerInner.items.getRange();
        for (var i=0; i<conditions.length; i++) {
            var condition = conditions[i].panelInstance.getFilterValues();
            if(condition) {
                conditionsData.push(condition);
            }
        }

        return {
            "operator": this.mainOperatorField.getValue(),
            "filters": conditionsData
        };
    }
});
