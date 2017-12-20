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
use HeimrichHannot\Request\Request;
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

        if (Request::getGet('file')) {
            \Contao\Controller::sendFileToBrowser(Request::getGet('file'));
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
            $watchlistModel = WatchlistModel::getMultipleWatchlistModel($this->id);
        } else {
            $watchlistModel = WatchlistModel::getWatchlistModel();
        }
        if ($watchlistModel !== null) {
            $count = $watchlistModel->countItems();
        }
        $this->Template->count         = $count;
        $this->Template->toggleLink    = $GLOBALS['TL_LANG']['WATCHLIST']['toggleLink'];
        $this->Template->updateHref    = AjaxAction::generateUrl(Watchlist::XHR_GROUP, Watchlist::XHR_WATCHLIST_UPDATE_ACTION, ['id' => $this->id]);
        $this->Template->showModalHref = AjaxAction::generateUrl(Watchlist::XHR_GROUP, Watchlist::XHR_WATCHLIST_SHOW_MODAL_ACTION, ['moduleId' => $this->id]);
    }
}