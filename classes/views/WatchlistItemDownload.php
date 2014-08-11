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


use Contao\ZipWriter;

class WatchlistItemDownload implements WatchlistItemViewInterface
{

	public function generate(WatchlistItem $item, $strHash)
	{
		$objFileModel = \FilesModel::findByPk($item->getId());

		if ($objFileModel === null) return;

		$objFile = new \File($objFileModel->path, true);

		$objContent = \ContentModel::findByPk($item->getCid());

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

		$strHref = \Environment::get('request');

		// Remove an existing file parameter (see #5683)
		if (preg_match('/(&(amp;)?|\?)file=/', $strHref)) {
			$strHref = preg_replace('/(&(amp;)?|\?)file=[^&]+/', '', $strHref);
		}

		$strHref .= ((\Config::get('disableAlias') || strpos($strHref, '?') !== false) ? '&amp;' : '?') . 'file=' . \System::urlEncode($objFile->path);

		$objT->link      = $linkTitle;
		$objT->title     = specialchars($objContent->titleText ? : $linkTitle);
		$objT->href      = $strHref;
		$objT->filesize  = \System::getReadableSize($objFile->filesize, 1);
		$objT->icon      = TL_ASSETS_URL . 'assets/contao/images/' . $objFile->icon;
		$objT->mime      = $objFile->mime;
		$objT->extension = $objFile->extension;
		$objT->path      = $objFile->dirname;

		$objT->actions = $this->generateEditActions($item, $strHash);

		return $objT->parse();
	}

	public function generateEditActions(WatchlistItem $item, $strHash)
	{
		$objPage = \PageModel::findByPk($item->getPid());

		if ($objPage === null) return;

		$objT = new \FrontendTemplate('watchlist_edit_actions');

		$objT->delHref = ampersand(\Controller::generateFrontendUrl($objPage->row()) . '?act=' . WATCHLIST_ACT_DELETE . '&hash=' . $strHash . '&id=' . $item->getUid());
		$objT->delTitle = $GLOBALS['TL_LANG']['WATCHLIST']['delTitle'];
		$objT->delLink = $GLOBALS['TL_LANG']['WATCHLIST']['delLink'];

		return $objT->parse();
	}


	public function generateAddActions($arrData, $id, $strHash)
	{
		global $objPage;

		if ($objPage === null) return;

		$objContent = \ContentModel::findByPk($arrData['id']);

		if ($objContent === null) return;

		$objFile = \FilesModel::findByUuid($objContent->singleSRC);

		if ($objFile === null) return;

		$item = new WatchlistItem($objFile->id, $objPage->id, $arrData['id'], $arrData['type']);

		$objT = new \FrontendTemplate('watchlist_add_actions');

		$objT->addHref = ampersand(\Controller::generateFrontendUrl($objPage->row()) . '?act=' . WATCHLIST_ACT_ADD . '&hash=' . $strHash . '&cid=' . $item->getCid() . '&id=' . $item->getId());
		$objT->addTitle = $GLOBALS['TL_LANG']['WATCHLIST']['addTitle'];
		$objT->addLink = $GLOBALS['TL_LANG']['WATCHLIST']['addLink'];

		return $objT->parse();
	}

	public function generateArchiveOutput(WatchlistItem $item, \ZipWriter $objZip)
	{
		$objFile = \FilesModel::findByPk($item->getId());

		if ($objFile === null) return $objZip;

		$objZip->addFile($objFile->path, $objFile->name);

		return $objZip;
	}
}