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

		if(\Input::get('act') && \Input::get('hash') == Watchlist::getInstance()->getHash())
		{
			$this->runAction();
		}

		return parent::generate();
	}

	protected function compile()
	{
		global $objPage;

//		$objContent = \ContentModel::findByPk('1397');
//
//		$item = new WatchlistItem($objContent->id, $objPage->id, $objContent->type);
//
//		Watchlist::getInstance()->addItem($item);
//
//		$objContent = \ContentModel::findByPk('1399');
//
//		$item = new WatchlistItem($objContent->id, $objPage->id, $objContent->type);
//
//		Watchlist::getInstance()->addItem($item);
//
//		$objContent = \ContentModel::findByPk('1400');
//
//		$item = new WatchlistItem($objContent->id, $objPage->id, $objContent->type);
//
//		Watchlist::getInstance()->addItem($item);

		$this->Template->watchlist = Watchlist::getInstance()->generate();
	}

	protected function runAction()
	{
		global $objPage;

		$output = '';

		switch(\Input::get('act'))
		{
			case WATCHLIST_ACT_DELETE:
				Watchlist::getInstance()->deleteItem(\Input::get('id'));
			break;

			case WATCHLIST_ACT_ADD:
				$objContent = \ContentModel::findByPk(\Input::get('id'));
				if($objContent === null) break;
				$item = new WatchlistItem($objContent->id, $objPage->id, $objContent->type);
				Watchlist::getInstance()->addItem($item);
			break;
		}

		// if ajax -> return the list content
		if(\Environment::get('isAjaxRequest'))
		{
			die(json_encode(Watchlist::getInstance()->generate()));
		}

		// no js support
		\Controller::redirect(\Controller::generateFrontendUrl($objPage->row()));
	}
} 