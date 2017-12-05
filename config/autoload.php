<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */


/**
 * Register the namespaces
 */
ClassLoader::addNamespaces([
    'HeimrichHannot',
]);


/**
 * Register the classes
 */
ClassLoader::addClasses([
    // Models
    'HeimrichHannot\Watchlist\WatchlistModel'              => 'system/modules/watchlist/models/WatchlistModel.php',
    'HeimrichHannot\Watchlist\WatchlistItemModel'          => 'system/modules/watchlist/models/WatchlistItemModel.php',

    // Modules
    'HeimrichHannot\Watchlist\ModuleWatchlist'             => 'system/modules/watchlist/modules/ModuleWatchlist.php',
    'HeimrichHannot\Watchlist\ModuleWatchlistDownloadList' => 'system/modules/watchlist/modules/ModuleWatchlistDownloadList.php',
]);


/**
 * Register the templates
 */
TemplateLoader::addFiles([
    'watchlist_edit_actions'        => 'system/modules/watchlist/templates/actions',
    'watchlist_add_action'          => 'system/modules/watchlist/templates/actions',
    'watchlist_global_actions'      => 'system/modules/watchlist/templates/actions',
    'mod_watchlist'                 => 'system/modules/watchlist/templates/modules',
    'watchlist_item'                => 'system/modules/watchlist/templates/items',
    'watchlist_notify'              => 'system/modules/watchlist/templates/notifications',
    'watchlist_grouped'             => 'system/modules/watchlist/templates/watchlist',
    'watchlist'                     => 'system/modules/watchlist/templates/watchlist',
    'watchlist_parents'             => 'system/modules/watchlist/templates/nav',
    'watchlist_view_download'       => 'system/modules/watchlist/templates/views',
    'watchlist_multiple_add_action' => 'system/modules/watchlist/templates/actions',
    'watchlist_multiple'            => 'system/modules/watchlist/templates/watchlist',
    'watchlist_select_actions'      => 'system/modules/watchlist/templates/actions',
    'watchlist_downloadLink_action' => 'system/modules/watchlist/templates/actions',
    'watchlist_download_list_item'  => 'system/modules/watchlist/templates/items',
    'mod_watchlist_download_list'   => 'system/modules/watchlist/templates/modules',
]);
