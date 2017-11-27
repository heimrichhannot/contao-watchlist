<?php

namespace HeimrichHannot\Watchlist\Controller;


use Contao\Controller;
use Contao\FilesModel;
use Contao\ModuleModel;
use Contao\Session;
use HeimrichHannot\Ajax\Ajax;
use HeimrichHannot\Ajax\Response\ResponseData;
use HeimrichHannot\Ajax\Response\ResponseSuccess;
use HeimrichHannot\Watchlist\Watchlist;
use HeimrichHannot\Watchlist\WatchlistItemEnclosure;
use HeimrichHannot\Watchlist\WatchlistItemModel;
use HeimrichHannot\Watchlist\WatchlistModel;

class FrontendController extends Controller
{
    public function xhrAction()
    {
        Ajax::runActiveAction(Watchlist::XHR_GROUP, Watchlist::XHR_WATCHLIST_ADD_ACTION, $this);
        Ajax::runActiveAction(Watchlist::XHR_GROUP, Watchlist::XHR_WATCHLIST_MULTIPLE_ADD_ACTION, $this);
        Ajax::runActiveAction(Watchlist::XHR_GROUP, Watchlist::XHR_WATCHLIST_UPDATE_ACTION, $this);
        Ajax::runActiveAction(Watchlist::XHR_GROUP, Watchlist::XHR_WATCHLIST_DELETE_ACTION, $this);
        Ajax::runActiveAction(Watchlist::XHR_GROUP, Watchlist::XHR_WATCHLIST_SELECT_ACTION, $this);
        Ajax::runActiveAction(Watchlist::XHR_GROUP, Watchlist::XHR_WATCHLIST_DELETE_ALL_ACTION, $this);
        Ajax::runActiveAction(Watchlist::XHR_GROUP, Watchlist::XHR_WATCHLIST_UPDATE_MODAL_ACTION, $this);
        Ajax::runActiveAction(Watchlist::XHR_GROUP, Watchlist::XHR_WATCHLIST_DOWNLOAD_ALL_ACTION, $this);
        Ajax::runActiveAction(Watchlist::XHR_GROUP, Watchlist::XHR_WATCHLIST_DOWNLOAD_LINK_ACTION, $this);
        Ajax::runActiveAction(Watchlist::XHR_GROUP, Watchlist::XHR_WATCHLIST_DOWNLOAD_ITEM_ACTION, $this);
        Ajax::runActiveAction(Watchlist::XHR_GROUP, Watchlist::XHR_WATCHLIST_MULTIPLE_SELECT_ADD_ACTION, $this);
    }

    public function watchlistUpdateAction($id)
    {
        $objResponse = new ResponseSuccess();
        $objResponse->setResult(new ResponseData($this->updateWatchlist($id)));

        return $objResponse;
    }

    public function watchlistMultipleAddAction($id, $cid, $type, $pageID, $title, $watchlist, $durability)
    {
        $objResponse = new ResponseSuccess();
        $objResponse->setResult(new ResponseData(false));
        if (!$watchlist) {
            return $objResponse;
        }
        $watchlistModel = WatchlistModel::findBy('name', $watchlist);

        if ($watchlistModel !== null) {
            $notification = Watchlist::getNotifications(sprintf($GLOBALS['TL_LANG']['WATCHLIST']['notify_watchlist_exists_error'], $watchlist), Watchlist::NOTIFY_STATUS_ERROR);
            $objResponse->setResult(new ResponseData(['id' => $id, 'notification' => $notification]));

            return $objResponse;
        }

        $watchlistModel = WatchlistController::addMultipleWatchlist($watchlist, $durability);
        if ($watchlistModel === null) {
            $notification = Watchlist::getNotifications(sprintf($GLOBALS['TL_LANG']['WATCHLIST']['notify_add_watchlist_error'], $watchlist), Watchlist::NOTIFY_STATUS_ERROR);
            $objResponse->setResult(new ResponseData(['id' => $id, 'notification' => $notification]));

            return $objResponse;
        }
        $objResponse = $this->addItemToWatchlist($id, $cid, $type, $pageID, $title, $watchlistModel);

        return $objResponse;
    }

    public function watchlistMultipleSelectAddAction($id, $cid, $type, $pageID, $title, $watchlist)
    {
        $objResponse = new ResponseSuccess();
        $objResponse->setResult(new ResponseData(false));
        if (!$watchlist) {
            return $objResponse;
        }

        $watchlistModel = WatchlistModel::findBy(['published=?', 'id=?'], ['1', $watchlist]);
        if ($watchlistModel === null) {
            return $objResponse;
        }

        $objResponse = $this->addItemToWatchlist($id, $cid, $type, $pageID, $title, $watchlistModel);

        return $objResponse;
    }

    public function watchlistAddAction($id, $cid, $type, $pageID, $title, $watchlist)
    {
        $watchlistModel = WatchlistModel::findById($watchlist);

        if ($watchlistModel === null) {
            $watchlistModel = WatchlistModel::getWatchlistModel();
        }

        $objResponse = $this->addItemToWatchlist($id, $cid, $type, $pageID, $title, $watchlistModel);

        return $objResponse;
    }

    protected function addItemToWatchlist($id, $cid, $type, $pageID, $title, $watchlistModel)
    {
        $objResponse = new ResponseSuccess();

        if ($watchlistModel == null) {
            $objResponse->setResult(new ResponseData(false));

            return $objResponse;
        }
        $file = FilesModel::findByUuid($id);
        if ($file == null) {
            $objResponse->setResult(new ResponseData(false));

            return $objResponse;
        }

        $notification = WatchlistController::addWatchlistItem($id, $watchlistModel, $cid, $type, $pageID, $title);
        $objResponse->setResult(new ResponseData(['id' => $id, 'notification' => $notification]));

        return $objResponse;
    }

    public function watchlistDeleteAction($id)
    {
        $notification = WatchlistController::deleteWatchlistItem($id);
        $objResponse  = new ResponseSuccess();
        $objResponse->setResult(new ResponseData(['id' => $id, 'notification' => $notification]));

        return $objResponse;
    }

    public function watchlistDeleteAllAction()
    {
        $objResponse = new ResponseSuccess();
        $objResponse->setResult(new ResponseData(['notification' => WatchlistController::deleteWatchlist()]));

        return $objResponse;
    }

    public function watchlistSelectAction($id)
    {
        Session::getInstance()->set(WatchlistController::WATCHLIST_SELECT, $id);
        $objResponse = new ResponseSuccess();
        $objResponse->setResult(new ResponseData($id));

        return $objResponse;
    }

    public function watchlistUpdateModalAction($id)
    {
        $objResponse = new ResponseSuccess();
        $objResponse->setResult(new ResponseData(Watchlist::getSelectAction($id)));

        return $objResponse;
    }

    public function watchlistDownloadAllAction($id)
    {
        $objResponse = new ResponseSuccess();
        $objResponse->setResult(new ResponseData(false));
        if ($id === '0') {
            $id = Session::getInstance()->get(Watchlist::WATCHLIST_SELECT);
        }
        $watchlistModel = WatchlistModel::findById($id);
        if ($watchlistModel === null) {
            return $objResponse;
        }
        $objResponse->setResult(new ResponseData(WatchlistController::downloadAll($watchlistModel)));

        return $objResponse;
    }

    public function watchlistDownloadLinkAction($id)
    {
        $objResponse = new ResponseSuccess();
        $objResponse->setResult(new ResponseData(false));
        $pageModel = \Contao\PageModel::findByPk($id);
        if ($pageModel === null) {
            return $objResponse;
        }
        $watchlist = WatchlistModel::findById(Session::getInstance()->get(Watchlist::WATCHLIST_SELECT));
        if ($watchlist === null) {
            return $objResponse;
        }
        $url = $pageModel->getAbsoluteUrl('?watchlist=' . $watchlist->uuid);
        $objResponse->setResult(new ResponseData($url));

        return $objResponse;
    }

    public function watchlistDownloadItemAction($id)
    {
        $objResponse = new ResponseSuccess();
        $objResponse->setResult(new ResponseData(false));
        $watchlistItem = WatchlistItemModel::findByUuid($id);
        if ($watchlistItem == null) {
            return $objResponse;
        }
        $objResponse->setResult(new ResponseData(WatchlistController::downloadItem($watchlistItem->uuid)));

        return $objResponse;
    }

    /**
     * @param $moduleId
     *
     * @return array|WatchlistModel|\Model|null|string
     */
    protected function updateWatchlist($moduleId)
    {
        $module = ModuleModel::findById($moduleId);
        if ($module === null) {
            return '';
        }

        if ($module->useMultipleWatchlist) {
            $watchlist = WatchlistModel::getMultipleWatchlistModel();
            $watchlistTemplate = Watchlist::getMultipleWatchlist($watchlist, $moduleId);
        } else {
            $watchlist         = WatchlistModel::getWatchlistModel();
            $watchlistTemplate = Watchlist::getWatchlist($watchlist, $moduleId);
        }

        return $watchlistTemplate;
    }
}
