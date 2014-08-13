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

	public function generateAddActions($arrData, $id, Watchlist $objWatchlist)
	{
		global $objPage;

		if($objPage === null) return;

		$objFile = \FilesModel::findByPk($id);

		if($objFile === null) return;

		$item = new WatchlistItem($objFile->id, $objPage->id, $arrData['id'], $arrData['type']);

		$objT = new \FrontendTemplate('watchlist_add_actions');

		$objT->addHref = ampersand(\Controller::generateFrontendUrl($objPage->row()) . '?act=' . WATCHLIST_ACT_ADD . '&hash=' . $objWatchlist->getHash() . '&cid=' . $item->getCid() . '&type=' . $item->getType() . '&id=' . $item->getId());
		$objT->addTitle = $GLOBALS['TL_LANG']['WATCHLIST']['addTitle'];
		$objT->addLink = $GLOBALS['TL_LANG']['WATCHLIST']['addLink'];
		$objT->active = $objWatchlist->isInList($item->getUid());

		return $objT->parse();
	}
}