pimcore.registerNS("pimcore.bundle.advancedSearch.helper");

pimcore.bundle.advancedSearch.helper = {
    rebuildAdvancedSearchMenu: function () {
        Ext.get('pimcore_menu_advanced_search').on('click', function() {
            var advancedSearch = new pimcore.bundle.advancedSearch.searchConfigPanel();
            pimcore.globalmanager.add(advancedSearch.getTabId(), advancedSearch);
        });

        var searchMenu = pimcore.globalmanager.get("layout_toolbar").searchMenu;

        var advancedSearchMenu = pimcore.globalmanager.get("bundle_advancedSearch_menu");

        if (!advancedSearchMenu) {
            advancedSearchMenu = Ext.create('Ext.menu.Item', {
                text: t("bundle_advancedSearch"),
                iconCls: "pimcore_bundle_nav_icon_advancedSearch",
                hideOnClick: false,
                menu: {
                    cls: "pimcore_navigation_flyout",
                    shadow: false,
                    items: []
                }

            });
            searchMenu.add(advancedSearchMenu);

            pimcore.globalmanager.add("bundle_advancedSearch_menu", advancedSearchMenu);
        }
        advancedSearchMenu.getMenu().removeAll();

        advancedSearchMenu.getMenu().add({
            text: t("bundle_advancedSearch_new"),
            iconCls: "pimcore_bundle_nav_icon_advancedSearch",
            handler: function () {
                var advancedSearch = new pimcore.bundle.advancedSearch.searchConfigPanel();
                pimcore.globalmanager.add(advancedSearch.getTabId(), advancedSearch);
            }
        });
        advancedSearchMenu.getMenu().add({
            text: t("bundle_advancedSearch_search"),
            iconCls: "pimcore_bundle_nav_icon_advancedSearch",
            handler: function () {
                new pimcore.bundle.advancedSearch.selector(pimcore.bundle.advancedSearch.helper.openEsSearch);
            }
        });

        Ext.Ajax.request({
            url: "/admin/bundle/advanced-search/admin/load-short-cuts",
            method: "get",
            success: function (response) {
                var rdata = Ext.decode(response.responseText);

                if(rdata.entries && rdata.entries.length) {
                    advancedSearchMenu.getMenu().add("-");

                    for(var i = 0; i < rdata.entries.length; i++) {
                        var id = rdata.entries[i].id;
                        advancedSearchMenu.getMenu().add({
                            text: rdata.entries[i].name,
                            iconCls: "pimcore_bundle_nav_icon_advancedObjectSearch",
                            handler: function (id) {
                                pimcore.bundle.advancedSearch.helper.openEsSearch(id);
                            }.bind(this, id)
                        });
                    }
                }

            }.bind(this)
        });
    },

    // initializeStatusIcon: function() {
    //
    //     var notificationMenu = pimcore.globalmanager.get("layout_toolbar")["notificationMenu"];
    //
    //     if(notificationMenu) {
    //         // Pimcore 6
    //         var statusIcon = new Ext.menu.Item({
    //             text: t("bundle_advancedObjectSearch_updating_index"),
    //             iconCls: 'pimcore_bundle_nav_icon_advancedObjectSearch'
    //         });
    //         notificationMenu.add(statusIcon);
    //     } else {
    //         // Pimcore 5
    //         var statusBar = Ext.get("pimcore_status");
    //         var statusIcon = Ext.get(statusBar.insertHtml('afterBegin',
    //             '<div id="pimcore_bundle_advancedObjectSearch_toolbar" data-menu-tooltip="'
    //             + t("bundle_advancedObjectSearch_updating_index") + '"></div>'));
    //
    //         pimcore.helpers.initMenuTooltips();
    //     }
    //
    //     this.checkIndexStatus(statusIcon);
    // },
    //
    // checkIndexStatus: function(statusIcon) {
    //
    //     Ext.Ajax.request({
    //         url: "/admin/bundle/advanced-object-search/admin/check-index-status",
    //         method: "get",
    //         success: function (response) {
    //             var rdata = Ext.decode(response.responseText);
    //
    //             if(rdata.indexUptodate === true) {
    //                 statusIcon.hide();
    //             } else {
    //                 statusIcon.show();
    //             }
    //
    //             setTimeout(this.checkIndexStatus.bind(this, statusIcon), 60000);
    //
    //         }.bind(this)
    //     });
    //
    // },
    //
    openEsSearch: function(id, callback) {
        Ext.Ajax.request({
            url: '/admin/bundle/advanced-search/admin/load-search',
            params: {
                id: id
            },
            method: "get",
            success: function (response) {
                var rdata = Ext.decode(response.responseText);

                var esSearch = new pimcore.bundle.advancedSearch.searchConfigPanel(rdata);
                pimcore.globalmanager.add(esSearch.getTabId(), esSearch);
                esSearch.activate();

                if(callback) {
                    callback();
                }

            }.bind(this)
        });

    }

};
