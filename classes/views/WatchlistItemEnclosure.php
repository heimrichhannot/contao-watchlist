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


class WatchlistItemEnclosure extends WatchlistItemDownload implements WatchlistItemViewInterface
{
	public function generateAddActions($arrData, $id, Watchlist $objWatchlist)
	{
		global $objPage;

		if($objPage === null) return;

		if(\Validator::isUuid($id))
		{
			$objFile = \FilesModel::findByUuid($id);
		}else
		{
			$objFile = \FilesModel::findBy('path', $id);
		}

		if($objFile === null) return;

		$objItem = new WatchlistItem(\String::binToUuid($objFile->uuid), $objPage->id, $arrData['id'], $arrData['type']);

		$objT = new \FrontendTemplate('watchlist_add_actions');

		$objT->addHref = ampersand(\Controller::generateFrontendUrl($objPage->row()) . '?act=' . WATCHLIST_ACT_ADD . '&hash=' . $objWatchlist->getHash() . '&cid=' . $objItem->getCid() . '&type=' . $objItem->getType() . '&id=' . $objItem->getId()  . '&title=' . urlencode($objItem->getTitle()));
		$objT->addTitle = $GLOBALS['TL_LANG']['WATCHLIST']['addTitle'];
		$objT->addLink = $GLOBALS['TL_LANG']['WATCHLIST']['addLink'];

		$objT->active = $objWatchlist->isInList($objItem->getId());

		return $objT->parse();
	}

	public function getTitle(WatchlistItemModel $objItem)
	{
		$objFileModel = \FilesModel::findByPk($objItem->getId());

		if ($objFileModel === null) return;

		$objFile = new \File($objFileModel->path, true);

		$linkTitle = $objFile->name;

		$arrMeta = deserialize($objFileModel->meta);

		// Language support
		if (($arrLang = $arrMeta[$GLOBALS['TL_LANGUAGE']]) != '') {
			$linkTitle = $arrLang['title'] ? $arrLang['title'] : $linkTitle;
		}

		return $linkTitle;
	}
}