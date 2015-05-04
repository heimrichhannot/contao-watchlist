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

class WatchlistItemDownload extends WatchlistItemDefault implements WatchlistItemViewInterface
{

	public function generate(WatchlistItemModel $objItem, Watchlist $objWatchlist)
	{
		global $objPage;

		$objFileModel = \FilesModel::findById($objItem->uuid);

		if ($objFileModel === null) return;

		$file = \Input::get('file', true);

		// Send the file to the browser and do not send a 404 header (see #4632)
		if ($file != '' && $file == $objFileModel->path)
		{
			\Controller::sendFileToBrowser($file);
		}

		$objFile = new \File($objFileModel->path, true);

		$objContent = \ContentModel::findByPk($objItem->cid);

		$objT = new \FrontendTemplate('watchlist_view_download');
		$objT->setData($objFileModel->row());

		$linkTitle = specialchars($objFile->name);

		// use generate for download & downloads as well
		if ($objContent->type == 'download' && $objContent->linkTitle != '') {
			$linkTitle = $objContent->linkTitle;
		}

		$arrMeta = deserialize($objFileModel->meta);

		// Language support
		if (($arrLang = $arrMeta[$GLOBALS['TL_LANGUAGE']]) != '') {
			$linkTitle = $arrLang['title'] ? $arrLang['title'] : $linkTitle;
		}

		$strHref = \Controller::generateFrontendUrl($objPage->row());

		// Remove an existing file parameter (see #5683)
		if (preg_match('/(&(amp;)?|\?)file=/', $strHref)) {
			$strHref = preg_replace('/(&(amp;)?|\?)file=[^&]+/', '', $strHref);
		}

		$strHref .= ((\Config::get('disableAlias') || strpos($strHref, '?') !== false) ? '&amp;' : '?') . 'file=' . \System::urlEncode($objFile->path);

		$objT->link      = ($objItemTitle = $objItem->title) ? $objItemTitle : $linkTitle;
		$objT->title     = specialchars($objContent->titleText ? : $linkTitle);
		$objT->href      = $strHref;
		$objT->filesize  = \System::getReadableSize($objFile->filesize, 1);
		$objT->icon      = TL_ASSETS_URL . 'assets/contao/images/' . $objFile->icon;
		$objT->mime      = $objFile->mime;
		$objT->extension = $objFile->extension;
		$objT->path      = $objFile->dirname;

		$objT->actions = $this->generateEditActions($objItem, $objWatchlist);

		return $objT->parse();
	}

	public function generateAddActions($arrData, $id, Watchlist $objWatchlist)
	{
		global $objPage;

		if ($objPage === null) return;

		$objContent = \ContentModel::findByPk($arrData['id']);

		// inserttag download support
		$blnInsertTag = \Validator::isUuid($id);

		if ($objContent === null && !$blnInsertTag) return;

		$objFile = \FilesModel::findByUuid($blnInsertTag ? $id : $objContent->singleSRC);

		if ($objFile === null) return;


		$objItem = new WatchlistItem($objFile->id, $objPage->id, $arrData['id'], $arrData['type'] , ($blnInsertTag && $arrData['linkTitle']) ? $arrData['linkTitle'] :  '');

		$objT = new \FrontendTemplate('watchlist_add_actions');

		$objT->addHref = ampersand(\Controller::generateFrontendUrl($objPage->row()) . '?act=' . WATCHLIST_ACT_ADD . '&hash=' . $objWatchlist->getHash() . '&cid=' . $objItem->getCid() . '&type=' . $objItem->getType() . '&id=' . $objItem->getId() . '&title=' . urlencode($objItem->getTitle()));
		$objT->addTitle = $GLOBALS['TL_LANG']['WATCHLIST']['addTitle'];
		$objT->addLink = $GLOBALS['TL_LANG']['WATCHLIST']['addLink'];
		$objT->active = $objWatchlist->isInList($objItem->getUid());
		$objT->id = $objItem->getUid();

		return $objT->parse();
	}

	public function generateArchiveOutput(WatchlistItemModel $objItem, \ZipWriter $objZip)
	{
		$objFile = \FilesModel::findById($objItem->uuid);

		if ($objFile === null) return $objZip;

		$objZip->addFile($objFile->path, $objFile->name);

		return $objZip;
	}

	public function getTitle(WatchlistItemModel $objItem)
	{
		$objFileModel = \FilesModel::findById($objItem->uuid);

		if ($objFileModel === null) return;

		$objFile = new \File($objFileModel->path, true);

		$objContent = \ContentModel::findByPk($objItem->getCid());

		$linkTitle = specialchars($objFile->name);

		// use generate for download & downloads as well
		if ($objContent->type == 'download' && $objContent->linkTitle != '') {
			$linkTitle = $objContent->linkTitle;
		}

		$arrMeta = deserialize($objFileModel->meta);

		// Language support
		if (($arrLang = $arrMeta[$GLOBALS['TL_LANGUAGE']]) != '') {
			$linkTitle = $arrLang['title'] ? $arrLang['title'] : $linkTitle;
		}

		return $linkTitle;
	}
}