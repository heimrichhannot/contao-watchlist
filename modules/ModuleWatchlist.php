<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2014 Heimrich & Hannot GmbH
 *
 * @package watchlist
 * @author  Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\Watchlist;


use HeimrichHannot\Ajax\AjaxAction;
use HeimrichHannot\Watchlist\Controller\WatchlistController;

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

        if ($this->protected) {
            $groups = deserialize(\FrontendUser::getInstance()->groups);
            if (!array_intersect(deserialize($this->groups), $groups)) {
                return;
            }
        }

        if (\Input::get('file')) {
            \Contao\Controller::sendFileToBrowser(\Input::get('file'));
        }

        $GLOBALS['TL_JAVASCRIPT']['watchlist'] = 'system/modules/watchlist/assets/js/jquery.watchlist.js|static';

        return parent::generate();
    }

    protected function compile()
    {
        $count                     = 0;
        $this->Template->watchlist = $GLOBALS['TL_LANG']['WATCHLIST']['empty'];
        if ($this->useMultipleWatchlist) {
            /* @var $watchlist WatchlistModel */
            $watchlist                 = WatchlistModel::getMultipleWatchlistModel();
            $this->Template->watchlist = Watchlist::getMultipleWatchlist($watchlist, $this->id);
            if ($watchlist !== null) {
                $count = $watchlist->countItems();
            }
        } else {
            $watchlist                 = WatchlistModel::getWatchlistModel();
            $this->Template->watchlist = Watchlist::getWatchlist($watchlist, $this->id);
            if ($watchlist !== null) {
                $count = $watchlist->current()->countItems();
            }
        }
        if ($this->useDownloadLink) {
            $this->Template->actions = Watchlist::getGlobalActions($this->downloadLink);
        } else {
            $this->Template->actions = Watchlist::getGlobalActions();
        }
        $this->Template->watchlistHeadline = $GLOBALS['TL_LANG']['WATCHLIST']['headline'];
        $this->Template->close             = $GLOBALS['TL_LANG']['WATCHLIST']['closeLink'];
        $this->Template->count             = $count;
        $this->Template->cssClass          = $count > 0 ? 'not-empty' : 'empty';
        $this->Template->toggleLink        = $GLOBALS['TL_LANG']['WATCHLIST']['toggleLink'];
        $this->Template->updateHref        = AjaxAction::generateUrl(WatchlistController::XHR_GROUP, WatchlistController::XHR_WATCHLIST_UPDATE_ACTION, ['id' => $this->id]);
    }
}