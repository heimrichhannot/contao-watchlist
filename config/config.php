<?php

// session keys
define('WATCHLIST_SESSION', 'WATCHLIST');
define('WATCHLIST_SESSION_FE', 'WATCHLIST_SESSION_FE');
define('WATCHLIST_SESSION_BE', 'WATCHLIST_SESSION_BE');

/**
 * Models
 */
$GLOBALS['TL_MODELS']['tl_watchlist']      = 'HeimrichHannot\Watchlist\WatchlistModel';
$GLOBALS['TL_MODELS']['tl_watchlist_item'] = 'HeimrichHannot\Watchlist\WatchlistItemModel';

/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['getPageLayout'][] = ['HeimrichHannot\Watchlist\Controller\AjaxController', 'xhrAction'];

/**
 * Front end modules
 */
array_insert($GLOBALS['FE_MOD'], 2, [
    'miscellaneous' => [
        'watchlist'               => 'HeimrichHannot\Watchlist\ModuleWatchlist',
        'watchlist_download_list' => 'HeimrichHannot\Watchlist\ModuleWatchlistDownloadList',
    ],
]);


/**
 * Ajax Actions
 */
$GLOBALS['AJAX'][\HeimrichHannot\Watchlist\Watchlist::XHR_GROUP] = [
    'actions' => [
        \HeimrichHannot\Watchlist\Watchlist::XHR_WATCHLIST_UPDATE_ACTION              => [
            'arguments' => [
                \HeimrichHannot\Watchlist\Watchlist::XHR_PARAMETER_WATCHLIST_ITEM_ID,
            ],
            'optional'  => [],
        ],
        \HeimrichHannot\Watchlist\Watchlist::XHR_WATCHLIST_DOWNLOAD_ALL_ACTION        => [
            'arguments' => [
                \HeimrichHannot\Watchlist\Watchlist::XHR_PARAMETER_WATCHLIST_ITEM_ID,
            ],
            'optional'  => [],
        ],
        \HeimrichHannot\Watchlist\Watchlist::XHR_WATCHLIST_UPDATE_MODAL_ADD_ACTION    => [
            'arguments' => [
                \HeimrichHannot\Watchlist\Watchlist::XHR_PARAMETER_WATCHLIST_ITEM_ID,
            ],
            'optional'  => [],
        ],
        \HeimrichHannot\Watchlist\Watchlist::XHR_WATCHLIST_DOWNLOAD_LINK_ACTION       => [
            'arguments' => [
                \HeimrichHannot\Watchlist\Watchlist::XHR_PARAMETER_WATCHLIST_ITEM_ID,
            ],
            'optional'  => [],
        ],
        \HeimrichHannot\Watchlist\Watchlist::XHR_WATCHLIST_DELETE_ACTION              => [
            'arguments' => [
                \HeimrichHannot\Watchlist\Watchlist::XHR_PARAMETER_WATCHLIST_ITEM_ID,
            ],
            'optional'  => [],
        ],
        \HeimrichHannot\Watchlist\Watchlist::XHR_WATCHLIST_DELETE_ALL_ACTION          => [
            'arguments' => [],
            'optional'  => [],
        ],
        \HeimrichHannot\Watchlist\Watchlist::XHR_WATCHLIST_SELECT_ACTION              => [
            'arguments' => [
                \HeimrichHannot\Watchlist\Watchlist::XHR_PARAMETER_WATCHLIST_ITEM_ID,
            ],
            'optional'  => [],
        ],
        \HeimrichHannot\Watchlist\Watchlist::XHR_WATCHLIST_ADD_ACTION                 => [
            'arguments' => [
                \HeimrichHannot\Watchlist\Watchlist::XHR_PARAMETER_WATCHLIST_ITEM_ID,
                \HeimrichHannot\Watchlist\Watchlist::XHR_PARAMETER_WATCHLIST_ITEM_CID,
                \HeimrichHannot\Watchlist\Watchlist::XHR_PARAMETER_WATCHLIST_ITEM_TYPE,
                \HeimrichHannot\Watchlist\Watchlist::XHR_PARAMETER_WATCHLIST_ITEM_PAGE,
                \HeimrichHannot\Watchlist\Watchlist::XHR_PARAMETER_WATCHLIST_ITEM_TITLE,
                \HeimrichHannot\Watchlist\Watchlist::XHR_PARAMETER_WATCHLIST_NAME,
            ],
            'optional'  => [],
        ],
        \HeimrichHannot\Watchlist\Watchlist::XHR_WATCHLIST_MULTIPLE_ADD_ACTION        => [
            'arguments' => [
                \HeimrichHannot\Watchlist\Watchlist::XHR_PARAMETER_WATCHLIST_ITEM_ID,
                \HeimrichHannot\Watchlist\Watchlist::XHR_PARAMETER_WATCHLIST_ITEM_CID,
                \HeimrichHannot\Watchlist\Watchlist::XHR_PARAMETER_WATCHLIST_ITEM_TYPE,
                \HeimrichHannot\Watchlist\Watchlist::XHR_PARAMETER_WATCHLIST_ITEM_PAGE,
                \HeimrichHannot\Watchlist\Watchlist::XHR_PARAMETER_WATCHLIST_ITEM_TITLE,
                \HeimrichHannot\Watchlist\Watchlist::XHR_PARAMETER_WATCHLIST_NAME,
                \HeimrichHannot\Watchlist\Watchlist::XHR_PARAMETER_WATCHLIST_DURABILITY,
            ],
            'optional'  => [],
        ],
        \HeimrichHannot\Watchlist\Watchlist::XHR_WATCHLIST_MULTIPLE_SELECT_ADD_ACTION => [
            'arguments' => [
                \HeimrichHannot\Watchlist\Watchlist::XHR_PARAMETER_WATCHLIST_ITEM_ID,
                \HeimrichHannot\Watchlist\Watchlist::XHR_PARAMETER_WATCHLIST_ITEM_CID,
                \HeimrichHannot\Watchlist\Watchlist::XHR_PARAMETER_WATCHLIST_ITEM_TYPE,
                \HeimrichHannot\Watchlist\Watchlist::XHR_PARAMETER_WATCHLIST_ITEM_PAGE,
                \HeimrichHannot\Watchlist\Watchlist::XHR_PARAMETER_WATCHLIST_ITEM_TITLE,
                \HeimrichHannot\Watchlist\Watchlist::XHR_PARAMETER_WATCHLIST_NAME,
            ],
            'optional'  => [],
        ],
    ],
];