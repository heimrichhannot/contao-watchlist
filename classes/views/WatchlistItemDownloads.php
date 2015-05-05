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


class WatchlistItemDownloads extends WatchlistItemDownload implements WatchlistItemViewInterface
{

	public function generateAddActions($arrData, $strUuid, Watchlist $objWatchlist)
	{
		global $objPage;

		if($objPage === null) return;

		$objFile = \FilesModel::findByUuid($strUuid);

		if($objFile === null) return;

		$objItem = new WatchlistItemModel();
		$objItem->pid = Watchlist::getInstance()->getId();
		$objItem->uuid = $objFile->uuid;
		$objItem->pageID = $objPage->id;
		$objItem->cid = $arrData['id'];
		$objItem->type = $arrData['type'];

		$objT = new \FrontendTemplate('watchlist_add_actions');

		$objT->addHref = ampersand(\Controller::generateFrontendUrl($objPage->row()) . '?act=' . WATCHLIST_ACT_ADD . '&cid=' . $objItem->cid . '&type=' . $objItem->type . '&id=' . $strUuid  . '&title=' . urlencode($objItem->getTitle()));
		$objT->addTitle = $GLOBALS['TL_LANG']['WATCHLIST']['addTitle'];
		$objT->addLink = $GLOBALS['TL_LANG']['WATCHLIST']['addLink'];
		$objT->active = $objWatchlist->isInList($strUuid);
		$objT->id = $strUuid;

		return $objT->parse();
	}
}