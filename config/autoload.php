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
	'HeimrichHannot\Watchlist\Watchlist'                  => 'system/modules/watchlist/classes/Watchlist.php',
	'HeimrichHannot\Watchlist\WatchlistItemView'          => 'system/modules/watchlist/classes/WatchlistItemView.php',
	'HeimrichHannot\Watchlist\WatchlistItemViewInterface' => 'system/modules/watchlist/classes/WatchlistItemViewInterface.php',
	'HeimrichHannot\Watchlist\WatchlistItem'              => 'system/modules/watchlist/classes/WatchlistItem.php',
	'HeimrichHannot\Watchlist\WatchlistItemDownload'      => 'system/modules/watchlist/classes/views/WatchlistItemDownload.php',
));


/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
	'mod_watchlist'             => 'system/modules/watchlist/templates/modules',
	'watchlist_parents'         => 'system/modules/watchlist/templates/watchlist',
	'watchlist_grouped'         => 'system/modules/watchlist/templates/watchlist',
	'watchlist_edit_actions'    => 'system/modules/watchlist/templates/watchlist',
	'watchlist_content_actions' => 'system/modules/watchlist/templates/watchlist',
	'watchlist_item'            => 'system/modules/watchlist/templates/watchlist',
	'watchlist'                 => 'system/modules/watchlist/templates/watchlist',
	'watchlist_add_actions'     => 'system/modules/watchlist/templates/watchlist',
	'ce_download'               => 'system/modules/watchlist/templates/elements',
	'ce_downloads'              => 'system/modules/watchlist/templates/elements',
	'watchlist_view_download'   => 'system/modules/watchlist/templates/views',
	'block_searchable'          => 'system/modules/watchlist/templates/block',
));
