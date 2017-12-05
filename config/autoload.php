<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */


/**
 * Register the namespaces
 */
ClassLoader::addNamespaces(array
(
	'HeimrichHannot',
));


/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
	// Modules
	'HeimrichHannot\Watchlist\ModuleWatchlistDownloadList'    => 'system/modules/watchlist/modules/ModuleWatchlistDownloadList.php',
	'HeimrichHannot\Watchlist\ModuleWatchlist'                => 'system/modules/watchlist/modules/ModuleWatchlist.php',

	// Models
	'HeimrichHannot\Watchlist\WatchlistModel'                 => 'system/modules/watchlist/models/WatchlistModel.php',
	'HeimrichHannot\Watchlist\WatchlistItemModel'             => 'system/modules/watchlist/models/WatchlistItemModel.php',

	// Classes
	'HeimrichHannot\Watchlist\Controller\WatchlistController' => 'system/modules/watchlist/classes/controller/WatchlistController.php',
	'HeimrichHannot\Watchlist\Controller\AjaxController'      => 'system/modules/watchlist/classes/controller/AjaxController.php',
	'HeimrichHannot\Watchlist\Watchlist'                      => 'system/modules/watchlist/classes/Watchlist.php',
));


/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
	'mod_watchlist'                 => 'system/modules/watchlist/templates/modules',
	'mod_watchlist_download_list'   => 'system/modules/watchlist/templates/modules',
	'watchlist_notify'              => 'system/modules/watchlist/templates/notifications',
	'watchlist_multiple'            => 'system/modules/watchlist/templates/watchlist',
	'watchlist'                     => 'system/modules/watchlist/templates/watchlist',
	'watchlist_grouped'             => 'system/modules/watchlist/templates/watchlist',
	'watchlist_parents'             => 'system/modules/watchlist/templates/nav',
	'watchlist_download_list_item'  => 'system/modules/watchlist/templates/items',
	'watchlist_item'                => 'system/modules/watchlist/templates/items',
	'watchlist_view_download'       => 'system/modules/watchlist/templates/views',
	'watchlist_multiple_add_action' => 'system/modules/watchlist/templates/actions',
	'watchlist_downloadLink_action' => 'system/modules/watchlist/templates/actions',
	'watchlist_add_action'          => 'system/modules/watchlist/templates/actions',
	'watchlist_edit_actions'        => 'system/modules/watchlist/templates/actions',
	'watchlist_modal'               => 'system/modules/watchlist/templates/actions',
	'watchlist_select_actions'      => 'system/modules/watchlist/templates/actions',
	'watchlist_global_actions'      => 'system/modules/watchlist/templates/actions',
));
