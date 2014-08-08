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


	public function generateActions(WatchlistItem $item, $strHash)
	{
		return $this->_strategy->generateActions($item, $strHash);
	}
}