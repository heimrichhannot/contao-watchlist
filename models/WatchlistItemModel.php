<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2015 Heimrich & Hannot GmbH
 *
 * @package markenportal
 * @author  Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\Watchlist;


class WatchlistItemModel extends \Contao\Model
{
    const WATCHLIST_ITEM_TYPE_DOWNLOAD    = 'download';
    const WATCHLIST_ITEM_TYPE_NO_DOWNLOAD = 'no_download';

    protected static $strTable = 'tl_watchlist_item';

    /**
     * Find a watchlist item by its pid and uuid
     *
     * @param       $intPid
     * @param       $strUuid
     * @param array $arrOptions
     *
     * @return static
     */
    public static function findByPidAndUuid($intPid, $strUuid, array $arrOptions = [])
    {
        $t = static::$strTable;

        // Convert UUIDs to binary
        if (\Validator::isStringUuid($strUuid)) {
            $strUuid = \StringUtil::uuidToBin($strUuid);
        }

        return static::findOneBy(["$t.pid=?", "$t.uuid=UNHEX(?)"], [$intPid, bin2hex($strUuid)], $arrOptions);
    }

    /**
     * Find a watchlist item by its pid and uuid
     *
     * @param       $strUuid
     * @param array $arrOptions
     *
     * @return static
     */
    public static function findByUuid($strUuid, array $arrOptions = [])
    {
        $t = static::$strTable;

        // Convert UUIDs to binary
        if (\Validator::isStringUuid($strUuid)) {
            $strUuid = \StringUtil::uuidToBin($strUuid);
        }

        return static::findOneBy(["$t.uuid=UNHEX(?)"], bin2hex($strUuid), $arrOptions);
    }
}