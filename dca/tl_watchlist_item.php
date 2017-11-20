<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2015 Heimrich & Hannot GmbH
 *
 * @package watchlist
 * @author  Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

$GLOBALS['TL_DCA']['tl_watchlist_item'] = [
    // Config
    'config' => [
        'dataContainer' => 'Table',
        'ptable'        => 'tl_watchlist',
        'sql'           => [
            'keys' => [
                'id'  => 'primary',
                'pid' => 'index',
            ],
        ],
    ],
    // Fields
    'fields' => [
        'id'     => [
            'sql' => "int(10) unsigned NOT NULL auto_increment",
        ],
        'uuid'   => [
            'sql' => "binary(16) NULL",
        ],
        'pid'    => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'pageID' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'cid'    => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'title'  => [
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'type'   => [
            'sql' => "varchar(128) NOT NULL default ''",
        ],
    ],
];