<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2014 Leo Feyer
 *
 * @package Watchlist
 * @link    https://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
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
	'HeimrichHannot\Watchlist\ModuleWatchlist'            => 'system/modules/watchlist/modules/ModuleWatchlist.php',

	// Code
	'ShoppingCart'                                        => 'system/modules/watchlist/code/ShoppingCart.php',
	'Item'                                                => 'system/modules/watchlist/code/Item.php',

	// Classes
	'HeimrichHannot\Watchlist\WatchlistItemDownloads'     => 'system/modules/watchlist/classes/views/WatchlistItemDownloads.php',
	'HeimrichHannot\Watchlist\WatchlistItemDefault'       => 'system/modules/watchlist/classes/views/WatchlistItemDefault.php',
	'HeimrichHannot\Watchlist\WatchlistItemEnclosure'     => 'system/modules/watchlist/classes/views/WatchlistItemEnclosure.php',
	'HeimrichHannot\Watchlist\WatchlistItemDownload'      => 'system/modules/watchlist/classes/views/WatchlistItemDownload.php',
	'HeimrichHannot\Watchlist\Watchlist'                  => 'system/modules/watchlist/classes/core/Watchlist.php',
	'HeimrichHannot\Watchlist\WatchlistItemView'          => 'system/modules/watchlist/classes/core/WatchlistItemView.php',
	'HeimrichHannot\Watchlist\WatchlistItemViewInterface' => 'system/modules/watchlist/classes/core/WatchlistItemViewInterface.php',
	'HeimrichHannot\Watchlist\WatchlistItem'              => 'system/modules/watchlist/classes/core/WatchlistItem.php',
));


/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
	'watchlist_edit_actions'    => 'system/modules/watchlist/templates/actions',
	'watchlist_content_actions' => 'system/modules/watchlist/templates/actions',
	'watchlist_add_actions'     => 'system/modules/watchlist/templates/actions',
	'watchlist_global_actions'  => 'system/modules/watchlist/templates/actions',
	'news_full'                 => 'system/modules/watchlist/templates/news',
	'mod_watchlist'             => 'system/modules/watchlist/templates/modules',
	'watchlist_item'            => 'system/modules/watchlist/templates/items',
	'watchlist_notify_default'  => 'system/modules/watchlist/templates/notifications',
	'watchlist_grouped'         => 'system/modules/watchlist/templates/watchlist',
	'watchlist'                 => 'system/modules/watchlist/templates/watchlist',
	'ce_download_inserttag'     => 'system/modules/watchlist/templates/elements',
	'ce_download'               => 'system/modules/watchlist/templates/elements',
	'ce_downloads'              => 'system/modules/watchlist/templates/elements',
	'watchlist_parents'         => 'system/modules/watchlist/templates/nav',
	'watchlist_view_download'   => 'system/modules/watchlist/templates/views',
));
