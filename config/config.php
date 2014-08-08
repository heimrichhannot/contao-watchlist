<?php

define('WATCHLIST_SESSION', 'WATCHLIST');
define('WATCHLIST_FE_COOKIE', 'FE_WATCHLIST');
define('WATCHLIST_FE_COOKIE_LIFETIME', 7776000);

/**
 * Front end modules
 */
array_insert($GLOBALS['FE_MOD'], 2, array
(
	'miscellaneous' => array
	(
		'watchlist'    => 'HeimrichHannot\Watchlist\ModuleWatchlist',
	)
));


/**
 * Watchlist Views
 */
$GLOBALS['WLV'] = array
(
	'download'	=> 'HeimrichHannot\Watchlist\WatchlistItemDownload'
);