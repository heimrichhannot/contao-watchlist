<?php
/**
 * Contao Open Source CMS
 * 
 * Copyright (c) 2014 Heimrich & Hannot GmbH
 * @package watchlist
 * @author Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\Watchlist;


interface WatchlistItemViewInterface
{
	public function generate(WatchlistItem $item, Watchlist $objWatchlist);

	public function generateEditActions(WatchlistItem $item, Watchlist $objWatchlist);

	public function generateAddActions($arrData, $id, Watchlist $objWatchlist);

	public function generateArchiveOutput(WatchlistItem $item, \ZipWriter $objZip);

	public function getTitle(WatchlistItem $item);
}