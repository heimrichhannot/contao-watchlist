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


class WatchlistItemDefault implements WatchlistItemViewInterface
{

	public function generate(WatchlistItem $item, Watchlist $objWatchlist)
	{
		return '';
	}

	public function generateEditActions(WatchlistItem $item, Watchlist $objWatchlist)
	{
		$objPage = \PageModel::findByPk($item->getPid());

		if ($objPage === null) return;

		$objT = new \FrontendTemplate('watchlist_edit_actions');

		$objT->delHref = ampersand(\Controller::generateFrontendUrl($objPage->row()) . '?act=' . WATCHLIST_ACT_DELETE . '&hash=' . $objWatchlist->getHash() . '&id=' . $item->getUid() . '&title=' . urlencode($item->getTitle()));
		$objT->delTitle = $GLOBALS['TL_LANG']['WATCHLIST']['delTitle'];
		$objT->delLink = $GLOBALS['TL_LANG']['WATCHLIST']['delLink'];
		$objT->id = $item->getUid();

		return $objT->parse();
	}


	public function generateAddActions($arrData, $id, Watchlist $objWatchlist)
	{
		return '';
	}

	public function generateArchiveOutput(WatchlistItem $item, \ZipWriter $objZip)
	{
		return $objZip;
	}

	public function getTitle(WatchlistItem $item)
	{
		return $item->getTitle();
	}
}