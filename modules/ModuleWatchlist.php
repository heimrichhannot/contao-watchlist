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


class ModuleWatchlist extends \Module
{

	protected $strTemplate = 'mod_watchlist';

	public function generate()
	{
		if (TL_MODE == 'BE') {
			$objTemplate           = new \BackendTemplate('be_wildcard');
			$objTemplate->wildcard = '### ' . utf8_strtoupper($GLOBALS['TL_LANG']['FMD']['watchlist'][0]) . ' ###';
			$objTemplate->title    = $this->headline;
			$objTemplate->id       = $this->id;
			$objTemplate->link     = $this->name;
			$objTemplate->href     = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

			return $objTemplate->parse();
		}

		$GLOBALS['TL_JAVASCRIPT']['watchlist'] = 'system/modules/watchlist/assets/js/jquery.watchlist.js';

		if (\Input::get('act') && \Input::get('hash') == Watchlist::getInstance()->getHash()) {
			$this->runAction();
		}

		return parent::generate();
	}

	protected function compile()
	{
		$this->Template->watchlist = Watchlist::getInstance()->generate();
	}

	protected function runAction()
	{
		global $objPage;

		switch (\Input::get('act')) {
			case WATCHLIST_ACT_DELETE:
				Watchlist::getInstance()->deleteItem(\Input::get('id'));
				break;
			case WATCHLIST_ACT_ADD:
				$objContent = \ContentModel::findByPk(\Input::get('cid'));
				if ($objContent === null) break;
				$item = new WatchlistItem(\Input::get('id'), $objPage->id, $objContent->id, $objContent->type);
				Watchlist::getInstance()->addItem($item);
				break;
			case WATCHLIST_ACT_DELETE_ALL:
				Watchlist::getInstance()->deleteAll();
				break;
			case WATCHLIST_ACT_DOWNLOAD_ALL:
				Watchlist::getInstance()->downloadAll();
				break;
		}

		// if ajax -> return the content of the watchlist
		if (\Environment::get('isAjaxRequest')) {
			die(json_encode(Watchlist::getInstance()->generate()));
		}

		// no js support -- redirect and remove GET parameters
		\Controller::redirect(\Controller::generateFrontendUrl($objPage->row()));
	}
} 