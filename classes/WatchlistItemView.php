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

	public function generate(WatchlistItem $item, $strHash)
	{
		return $this->_strategy->generate($item, $strHash);
	}


	public function generateEditActions(WatchlistItem $item, $strHash)
	{
		return $this->_strategy->generateEditActions($item, $strHash);
	}

	public function generateAddActions(WatchlistItem $item, $strHash)
	{
		return $this->_strategy->generateAddActions($item, $strHash);
	}
}