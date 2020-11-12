pimcore.registerNS("pimcore.bundle.advancedSearch");

pimcore.bundle.advancedSearch = Class.create(pimcore.plugin.admin, {
    getClassName: function () {
        return "pimcore.bundle.advancedSearch";
    },

    initialize: function () {
        pimcore.plugin.broker.registerPlugin(this);
    },

    pimcoreReady: function (params, broker) {
        var perspectiveCfg = pimcore.globalmanager.get("perspective");
        var user = pimcore.globalmanager.get("user");

        if (user.roles.length === 0) {
            var searchMenu = pimcore.globalmanager.get("layout_toolbar").searchMenu;
            if (searchMenu && perspectiveCfg.inToolbar("search.advancedSearch")) {
                Ext.get('pimcore_menu_search').insertSibling('<li id="pimcore_menu_advanced_search" data-menu-tooltip="Advanced search" class="pimcore_bundle_nav_icon_advancedSearch pimcore_menu_item pimcore_menu_needs_children"></li>', 'after');
                pimcore.bundle.advancedSearch.helper.rebuildAdvancedSearchMenu();
            }
        }
    }
});

var advancedSearchBundle = new pimcore.bundle.advancedSearch();
