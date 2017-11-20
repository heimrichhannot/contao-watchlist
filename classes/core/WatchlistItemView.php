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


use HeimrichHannot\Watchlist\Controller\WatchlistController;

class WatchlistItemView
{
	private $_strategy;

	protected $strHash;

	public function __construct(WatchlistItemViewInterface $strategy)
	{
		$this->_strategy = $strategy;
	}

	public function generate(WatchlistItemModel $objItem, WatchlistController $objWatchlist)
	{
		return $this->_strategy->generate($objItem, $objWatchlist);
	}

	public function generateEditActions(WatchlistItemModel $objItem, WatchlistController $objWatchlist)
	{
		return $this->_strategy->generateEditActions($objItem, $objWatchlist);
	}

	public function generateAddActions($arrData, $id, WatchlistController $objWatchlist)
	{
		return $this->_strategy->generateAddActions($arrData, $id, $objWatchlist);
	}

	public function generateArchiveOutput(WatchlistItemModel $objItem, \ZipWriter $objZip)
	{
		return $this->_strategy->generateArchiveOutput($objItem, $objZip);
	}

	public function getTitle(WatchlistItemModel $objItem)
	{
		return $this->_strategy->getTitle($objItem);
	}
}