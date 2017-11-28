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

namespace HeimrichHannot\Watchlist\Controller;


use Contao\Session;
use HeimrichHannot\Ajax\AjaxAction;
use HeimrichHannot\Ajax\Response\ResponseData;
use HeimrichHannot\Ajax\Response\ResponseSuccess;
use HeimrichHannot\Haste\Util\StringUtil;
use HeimrichHannot\Haste\Util\Url;
use HeimrichHannot\Watchlist\Watchlist;
use HeimrichHannot\Watchlist\WatchlistItemModel;
use HeimrichHannot\Watchlist\WatchlistItemView;
use HeimrichHannot\Watchlist\WatchlistModel;

class WatchlistController
{
    const XHR_GROUP = 'wl';

    const XHR_WATCHLIST_ADD_ACTION           = 'watchlistAddAction';
    const XHR_WATCHLIST_DELETE_ACTION        = 'watchlistDeleteAction';
    const XHR_WATCHLIST_DELETE_ALL_ACTION    = 'watchlistDeleteAllAction';
    const XHR_WATCHLIST_UPDATE_ACTION        = 'watchlistUpdateAction';
    const XHR_WATCHLIST_SELECT_ACTION        = 'watchlistSelectAction';
    const XHR_WATCHLIST_UPDATE_MODAL_ACTION  = 'watchlistUpdateModalAction';
    const XHR_WATCHLIST_DOWNLOAD_ALL_ACTION  = 'watchlistDownloadAllAction';
    const XHR_WATCHLIST_DOWNLOAD_LINK_ACTION = 'watchlistDownloadLinkAction';
    const XHR_WATCHLIST_DOWNLOAD_ITEM_ACTION = 'watchlistDownloadItemAction';

    const XHR_PARAMETER_WATCHLIST_ITEM_ID    = 'id';
    const XHR_PARAMETER_WATCHLIST_ITEM_TITLE = 'title';
    const XHR_PARAMETER_WATCHLIST_ITEM_CID   = 'cid';
    const XHR_PARAMETER_WATCHLIST_ITEM_PAGE  = 'pageID';
    const XHR_PARAMETER_WATCHLIST_ITEM_TYPE  = 'type';
    const XHR_PARAMETER_WATCHLIST_NAME       = 'watchlist';
    const XHR_PARAMETER_WATCHLIST_DURABILITY = 'durability';

    const WATCHLIST_SELECT = 'watchlist_select';

    /**
     * @var int for tracking iterations
     */
    protected $position = 0;

    /**
     * @var WatchlistModel|null
     */
    protected $objModel = null;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param $uuid
     * @param $watchlistModel
     * @param $cid
     * @param $type
     * @param $pageID
     * @param $title
     *
     * @return string
     * @throws \Exception
     */
    public static function addWatchlistItem($uuid, $watchlistModel, $cid, $type, $pageID, $title)
    {
        // Throw an exception if there's no id:
        if (!\Validator::isStringUuid($uuid)) {
            throw new \Exception('The watchlist requires items with an unique file uuid.');
        }

        $watchlistItemModel = WatchlistItemModel::findByUuid($uuid);

        if ($watchlistItemModel !== null && $watchlistModel->id == $watchlistItemModel->pid) {
            return Watchlist::getNotifications(sprintf($GLOBALS['TL_LANG']['WATCHLIST']['notify_in_watchlist'], $watchlistItemModel->title, $watchlistModel->name), Watchlist::NOTIFY_STATUS_ERROR);
        }

        $objItem         = new WatchlistItemModel();
        $objItem->pid    = $watchlistModel->id;
        $objItem->uuid   = \StringUtil::uuidToBin($uuid); // transform string to bin
        $objItem->pageID = $pageID;
        $objItem->cid    = $cid;
        $objItem->type   = $type;
        $objItem->tstamp = time();
        $objItem->title  = $title;
        $objItem->save();

        return Watchlist::getNotifications(sprintf($GLOBALS['TL_LANG']['WATCHLIST']['notify_add_item'], $objItem->title), Watchlist::NOTIFY_STATUS_SUCCESS);
    }

    /**
     * @param $name
     * @param $durability
     *
     * @return mixed
     */
    public static function addMultipleWatchlist($name, $durability)
    {
        if (FE_USER_LOGGED_IN === true) {
            $watchlist = WatchlistModel::createWatchlist($name, \FrontendUser::getInstance()->id, $durability);
        } else {
            $watchlist = WatchlistModel::createWatchlist($name, 0, $durability);
        }

        return $watchlist;
    }

    /**
     * @param $id
     *
     * @return string
     */
    public static function deleteWatchlistItem($id)
    {
        $watchlistItemModel = WatchlistItemModel::findByUuid($id);
        if ($watchlistItemModel === null) {
            return Watchlist::getNotifications(sprintf($GLOBALS['TL_LANG']['WATCHLIST']['notify_delete_item_error']), Watchlist::NOTIFY_STATUS_ERROR);
        }
        $watchlistItemModel->delete();

        return Watchlist::getNotifications(sprintf($GLOBALS['TL_LANG']['WATCHLIST']['notify_delete_item'], $watchlistItemModel->title), Watchlist::NOTIFY_STATUS_SUCCESS);
    }

    /**
     * @return string
     */
    public static function deleteWatchlist()
    {
        $id     = Session::getInstance()->get(WatchlistController::WATCHLIST_SELECT);
        $result = WatchlistModel::deleteWatchlistById($id);
        if ($result == 0) {
            return Watchlist::getNotifications(sprintf($GLOBALS['TL_LANG']['WATCHLIST']['notify_delete_all_error']), Watchlist::NOTIFY_STATUS_ERROR);
        }
        $watchlist = WatchlistModel::findOneBy(['pid=?', 'published=?'], [\FrontendUser::getInstance()->id, '1']);
        if ($watchlist === null) {
            Session::getInstance()->set(WatchlistController::WATCHLIST_SELECT, null);

            return Watchlist::getNotifications(sprintf($GLOBALS['TL_LANG']['WATCHLIST']['notify_delete_all']), Watchlist::NOTIFY_STATUS_SUCCESS);
        }
        Session::getInstance()->set(WatchlistController::WATCHLIST_SELECT, $watchlist->id);

        return Watchlist::getNotifications(sprintf($GLOBALS['TL_LANG']['WATCHLIST']['notify_delete_all']), Watchlist::NOTIFY_STATUS_SUCCESS);
    }

    /**
     * @param WatchlistModel $watchlistModel
     *
     * @return string|boolean
     */
    public static function downloadAll(WatchlistModel $watchlistModel)
    {
        /** @var $objPage \Contao\PageModel */
        global $objPage;

        $objItems = WatchlistItemModel::findBy('pid', $watchlistModel->id);

        if ($objItems == null) {
            return false;
        }

        $basePath = $objPage->getFrontendUrl();
        $path     = 'files/tmp/';
        $strFile  = 'download_' . $watchlistModel->hash . '.zip';

        $objZip = new \ZipWriter($path . $strFile);

        foreach ($objItems as $item) {

            if ($item->type !== WatchlistItemModel::WATCHLIST_ITEM_TYPE_DOWNLOAD) {
                continue;
            }

            $objZip = static::generateArchiveOutput($item, $objZip);
        }

        $objZip->close();

        // Open the "save as …" dialogue
        $objFile = new \File($path . $strFile, true);

        return $basePath . '?file=' . $path . $strFile;
    }

    /**
     * adds file to zip
     *
     * @param WatchlistItemModel $objItem
     * @param \ZipWriter         $objZip
     *
     * @return \ZipWriter
     */
    protected static function generateArchiveOutput(WatchlistItemModel $objItem, \ZipWriter $objZip)
    {
        $objFile = \FilesModel::findById($objItem->uuid);

        if ($objFile === null) {
            return $objZip;
        }

        $objZip->addFile($objFile->path, $objFile->name);

        return $objZip;
    }
}