pimcore.registerNS("pimcore.bundle.advancedSearch.searchConfig.conditionPanelContainerBuilder");

pimcore.bundle.advancedSearch.searchConfig.conditionPanelContainerBuilder = Class.create({

    initialize: function (classId, parentPanel, panelId, conditionEntryPanelLayout) {
        this.classId = classId;
        this.parentPanel = parentPanel;
        this.panelId = panelId;
        this.conditionEntryPanelLayout = conditionEntryPanelLayout;
    },

    buildConditionsContainerInner: function () {

        // drop down menu for adding new conditions
        var addMenu = Ext.create('Ext.menu.Menu');

        var toggleGroup = "panel_" + this.panelId;
        this.conditionsContainerInner = Ext.create('Ext.panel.Panel',{
            tbar: [
                {
                    iconCls: "pimcore_icon_add",
                    menu: addMenu
                }
            ],
            collapsible: true,
            title: t("bundle_advancedSearch_filters"),
            border: false,
            items: []
        });

        addMenu.add({
            iconCls: "pimcore_icon_add",
            handler: function (type, data) {
                this.addConditionEntryPanel(this.parentPanel, this.classId, this.conditionEntryPanelLayout, this.conditionsContainerInner);
            }.bind(this),
            text: t("bundle_advancedSearch_condition")
        });

        return this.conditionsContainerInner;
    },

    addConditionEntryPanel: function (data) {
        var itemClass = new pimcore.bundle.advancedSearch.searchConfig.conditionEntryPanel(this.classId, this.conditionEntryPanelLayout);
        var item = itemClass.getConditionPanel(this.parentPanel, data);
        this.conditionsContainerInner.add(item);
        item.updateLayout();
        this.conditionsContainerInner.updateLayout();
    },

    addConditionGroupPanel: function(data) {
        var itemClass = new pimcore.bundle.advancedObjectSearch.searchConfig.conditionGroupPanel(this.classId, this.conditionEntryPanelLayout);
        var item = itemClass.getConditionPanel(this.parentPanel, data);
        this.conditionsContainerInner.add(item);
        item.updateLayout();
        this.conditionsContainerInner.updateLayout();
    },

    populateConditionsContainerInner: function(dataArray) {

        if(dataArray && Array === dataArray.constructor) {
            for(var i = 0; i < dataArray.length; i++) {

                var filterData = dataArray[i];
                if(filterData.fieldname == "~~group~~") {
                    this.addConditionGroupPanel(filterData);
                } else {
                    this.addConditionEntryPanel(filterData);
                }

            }
        }
    }
});
