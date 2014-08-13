<?php

// session keys
define('WATCHLIST_SESSION', 'WATCHLIST');

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
	'downloads' => 'HeimrichHannot\Watchlist\WatchlistItemDownloads'
));