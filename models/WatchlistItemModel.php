<?php
/**
 * Contao Open Source CMS
 * 
 * Copyright (c) 2015 Heimrich & Hannot GmbH
 * @package markenportal
 * @author Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\Watchlist;


class WatchlistItemModel extends \Model
{
	protected static $strTable = 'tl_watchlist_item';

	/**
	 * Find a watchlist item by its pid and uuid
	 *
	 * @param string $intPid    The watchlist id
	 * @param string $intUuid   The files uuid
	 * @param array  $arrOptions An optional options array
	 *
	 * @return \Model|null The model or null if there is no watchlist
	 */
	public static function findByPidAndUuid($intPid, $strUuid, array $arrOptions=array())
	{
		$t = static::$strTable;

		// Convert UUIDs to binary
		if (\Validator::isStringUuid($strUuid))
		{
			$strUuid = \String::uuidToBin($strUuid);
		}

		return static::findOneBy(array("$t.pid=?", "$t.uuid=UNHEX(?)"), array($intPid, bin2hex($strUuid)), $arrOptions);
	}

}