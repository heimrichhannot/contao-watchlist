<?php

// session keys
define('WATCHLIST_SESSION', 'WATCHLIST');
define('WATCHLIST_SESSION_FE', 'WATCHLIST_SESSION_FE');
define('WATCHLIST_SESSION_BE', 'WATCHLIST_SESSION_BE');

// actions
define('WATCHLIST_ACT_DELETE', 'delete');
define('WATCHLIST_ACT_DELETE_ALL', 'deleteAll');
define('WATCHLIST_ACT_DOWNLOAD_ALL', 'downloadAll');
define('WATCHLIST_ACT_ADD', 'add');


// notifications
define('WATCHLIST_NOTIFICATION_UPDATE_ITEM', 'UPDATE_ITEM');
define('WATCHLIST_NOTIFICATION_ADD_ITEM', 'ADD_ITEM');
define('WATCHLIST_NOTIFICATION_DELETE_ITEM', 'DELETE_ITEM');
define('WATCHLIST_NOTIFICATION_DELETE_ALL', 'DELETE_ALL');

/**
 * Models
 */
$GLOBALS['TL_MODELS']['tl_watchlist'] = 'HeimrichHannot\Watchlist\WatchlistModel';
$GLOBALS['TL_MODELS']['tl_watchlist_item'] = 'HeimrichHannot\Watchlist\WatchlistItemModel';

/**
 * Front end modules
 */
array_insert($GLOBALS['FE_MOD'], 2, array
(
	'miscellaneous' => array
	(
		'watchlist' => 'HeimrichHannot\Watchlist\ModuleWatchlist',
	)
));


/**
 * Watchlist Views
 */
array_insert($GLOBALS['WLV'], 2, array
(
	'download'  => 'HeimrichHannot\Watchlist\WatchlistItemDownload',
	'downloads' => 'HeimrichHannot\Watchlist\WatchlistItemDownloads',
	'enclosure'	=> 'HeimrichHannot\Watchlist\WatchlistItemEnclosure',
));