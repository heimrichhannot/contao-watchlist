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

	public function generate(WatchlistItemModel $objItem, Watchlist $objWatchlist)
	{
		return '';
	}

	public function generateEditActions(WatchlistItemModel $objItem, Watchlist $objWatchlist)
	{
		$objPage = \PageModel::findByPk($objItem->pageID);

		if ($objPage === null) return;

		$objT = new \FrontendTemplate('watchlist_edit_actions');

		$objT->delHref = ampersand(\Controller::generateFrontendUrl($objPage->row()) . '?act=' . WATCHLIST_ACT_DELETE . '&id=' . \StringUtil::binToUuid($objItem->uuid) . '&title=' . urlencode($objItem->title));
		$objT->delTitle = $GLOBALS['TL_LANG']['WATCHLIST']['delTitle'];
		$objT->delLink = $GLOBALS['TL_LANG']['WATCHLIST']['delLink'];
		$objT->id = \StringUtil::binToUuid($objItem->uuid);

		return $objT->parse();
	}


	public function generateAddActions($arrData, $id, Watchlist $objWatchlist)
	{
		return '';
	}

	public function generateArchiveOutput(WatchlistItemModel $objItem, \ZipWriter $objZip)
	{
		return $objZip;
	}

	public function getTitle(WatchlistItemModel $objItem)
	{
		return $objItem->title;
	}
}