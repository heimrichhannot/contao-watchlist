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


class WatchlistItemView
{
	private $_strategy;

	protected $strHash;

	public function __construct(WatchlistItemViewInterface $strategy)
	{
		$this->_strategy = $strategy;
	}

	public function generate(WatchlistItem $item, Watchlist $objWatchlist)
	{
		return $this->_strategy->generate($item, $objWatchlist);
	}

	public function generateEditActions(WatchlistItem $item, Watchlist $objWatchlist)
	{
		return $this->_strategy->generateEditActions($item, $objWatchlist);
	}

	public function generateAddActions($arrData, $id, Watchlist $objWatchlist)
	{
		return $this->_strategy->generateAddActions($arrData, $id, $objWatchlist);
	}

	public function generateArchiveOutput(WatchlistItem $item, \ZipWriter $objZip)
	{
		return $this->_strategy->generateArchiveOutput($item, $objZip);
	}

	public function getTitle(WatchlistItem $item)
	{
		return $this->_strategy->getTitle($item);
	}
}