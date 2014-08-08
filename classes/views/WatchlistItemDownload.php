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


class WatchlistItemDownload implements WatchlistItemViewInterface
{
	public function generate(WatchlistItem $item, $strHash)
	{
		$objContent = \ContentModel::findByPk($item->getId());

		if($objContent === null) return;

		$objItem = new \ContentDownload($objContent);

		$objT = new \FrontendTemplate('watchlist_view_download');
		$objT->setData($objContent->row());
		$objT->item = $objItem->generate();
		$objT->actions = $this->generateEditActions($item, $strHash);

		return $objT->parse();
	}

	public function generateEditActions(WatchlistItem $item, $strHash)
	{
		global $objPage;

		$objT = new \FrontendTemplate('watchlist_edit_actions');

		$objT->delHref = ampersand(\Controller::generateFrontendUrl($objPage->row()) . '?act=' . WATCHLIST_ACT_DELETE . '&hash=' . $strHash . '&id=' . $item->getId());

		return $objT->parse();
	}


	public function generateAddActions(WatchlistItem $item, $strHash)
	{
		global $objPage;

		$objT = new \FrontendTemplate('watchlist_add_actions');

		$objT->addHref = ampersand(\Controller::generateFrontendUrl($objPage->row()) . '?act=' . WATCHLIST_ACT_ADD . '&hash=' . $strHash . '&id=' . $item->getId());

		return $objT->parse();
	}
}