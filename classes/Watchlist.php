<?php
/**
 * Created by PhpStorm.
 * User: kwagner
 * Date: 07.11.17
 * Time: 08:37
 */

namespace HeimrichHannot\Watchlist;


use Contao\ModuleModel;
use Contao\Session;
use HeimrichHannot\Ajax\AjaxAction;
use HeimrichHannot\Haste\Util\Url;
use HeimrichHannot\Request\Request;

class Watchlist
{
    const XHR_GROUP = 'wl';

    const XHR_WATCHLIST_ADD_ACTION                 = 'watchlistAddAction';
    const XHR_WATCHLIST_DELETE_ACTION              = 'watchlistDeleteAction';
    const XHR_WATCHLIST_DELETE_ALL_ACTION          = 'watchlistDeleteAllAction';
    const XHR_WATCHLIST_UPDATE_ACTION              = 'watchlistUpdateAction';
    const XHR_WATCHLIST_SELECT_ACTION              = 'watchlistSelectAction';
    const XHR_WATCHLIST_UPDATE_MODAL_ACTION        = 'watchlistUpdateModalAction';
    const XHR_WATCHLIST_DOWNLOAD_ALL_ACTION        = 'watchlistDownloadAllAction';
    const XHR_WATCHLIST_DOWNLOAD_LINK_ACTION       = 'watchlistDownloadLinkAction';
    const XHR_WATCHLIST_MULTIPLE_ADD_ACTION        = 'watchlistMultipleAddAction';
    const XHR_WATCHLIST_MULTIPLE_SELECT_ADD_ACTION = 'watchlistMultipleSelectAddAction';

    const XHR_PARAMETER_WATCHLIST_ITEM_ID    = 'id';
    const XHR_PARAMETER_WATCHLIST_ITEM_TITLE = 'title';
    const XHR_PARAMETER_WATCHLIST_ITEM_CID   = 'cid';
    const XHR_PARAMETER_WATCHLIST_ITEM_PAGE  = 'pageID';
    const XHR_PARAMETER_WATCHLIST_ITEM_TYPE  = 'type';
    const XHR_PARAMETER_WATCHLIST_NAME       = 'watchlist';
    const XHR_PARAMETER_WATCHLIST_DURABILITY = 'durability';

    const WATCHLIST_SELECT = 'watchlist_select';

    const NOTIFY_STATUS_ERROR   = 'watchlist-notify-error';
    const NOTIFY_STATUS_SUCCESS = 'watchlist-notify-success';

    /**
     * @param integer $id
     * @param mixed   $groups
     *
     * @return string
     */
    public static function getSelectAction($id, $groups = false)
    {
        $select = WatchlistModel::getAllWatchlistsByCurrentUser(true, $groups);

        $objT                  = new \FrontendTemplate('watchlist_select_actions');
        $objT->href            = AjaxAction::generateUrl(static::XHR_GROUP, static::XHR_WATCHLIST_UPDATE_MODAL_ACTION);
        $objT->select          = $select;
        $objT->id              = $id;
        $objT->selectWatchlist = $GLOBALS['TL_LANG']['WATCHLIST']['selectWatchlist'];

        return $objT->parse();
    }

    /**
     * @param array  $arrData
     * @param string $id
     * @param bool   $multiple
     *
     * @return string
     */
    public static function getAddAction($arrData, $id, $multiple = false)
    {
        global $objPage;

        if ($objPage === null) {
            return '';
        }
        if (\Validator::isUuid($id)) {
            $objFile = \FilesModel::findByUuid($id);
        } else {
            $objFile = \FilesModel::findBy('path', $id);
        }

        if ($objFile === null) {
            return '';
        }
        $strUuid = \StringUtil::binToUuid($objFile->uuid);

        if ($multiple) {
            return static::getMultipleWatchlistAddAction($strUuid, $arrData, $objPage);
        } else {
            return static::getSingleWatchlistAddAction($strUuid, $arrData, $objPage);
        }
    }

    /**
     * @param string $strUuid
     * @param array  $arrData
     * @param        $objPage
     *
     * @return string
     */
    protected static function getSingleWatchlistAddAction($strUuid, array $arrData, $objPage)
    {
        $added     = false;
        $watchlist = WatchlistModel::getWatchlistModel();
        if ($watchlist == null) {
            return '';
        }

        $watchlistItem = WatchlistItemModel::findByUuid($strUuid);

        if ($watchlistItem !== null) {
            $added = true;
        }

        $objT = new \FrontendTemplate('watchlist_add_action');

        $objT->addHref  = \HeimrichHannot\Ajax\AjaxAction::generateUrl(static::XHR_GROUP, static::XHR_WATCHLIST_ADD_ACTION, ['id' => $strUuid, 'cid' => $arrData['id'], 'type' => $arrData['type'], 'pageID' => $objPage->id, 'title' => $arrData['name'], 'watchlist' => $watchlist->id]);
        $objT->addTitle = $GLOBALS['TL_LANG']['WATCHLIST']['addTitle'];
        $objT->addLink  = $GLOBALS['TL_LANG']['WATCHLIST']['addLink'];
        $objT->added    = $added ? 'watchlist-delete-item watchlist-added' : 'watchlist-add';
        $objT->id       = $strUuid;

        return $objT->parse();
    }

    /**
     * @param       $strUuid
     * @param array $arrData
     * @param       $objPage
     *
     * @return string
     */
    protected static function getMultipleWatchlistAddAction($strUuid, array $arrData, $objPage)
    {
        $objT = new \FrontendTemplate('watchlist_multiple_add_action');

        if (FE_USER_LOGGED_IN === true) {
            $objT->durability      = [$GLOBALS['TL_LANG']['WATCHLIST']['durability']['default'], $GLOBALS['TL_LANG']['WATCHLIST']['durability']['immortal']];
            $objT->durabilityLabel = $GLOBALS['TL_LANG']['WATCHLIST']['durability']['label'];
        }

        $objT->addHref         = \HeimrichHannot\Ajax\AjaxAction::generateUrl(static::XHR_GROUP, static::XHR_WATCHLIST_MULTIPLE_ADD_ACTION, ['id' => $strUuid, 'cid' => $arrData['id'], 'type' => $arrData['type'], 'pageID' => $objPage->id, 'title' => $arrData['name']]);
        $objT->selectAddHref   = \HeimrichHannot\Ajax\AjaxAction::generateUrl(static::XHR_GROUP, static::XHR_WATCHLIST_MULTIPLE_SELECT_ADD_ACTION, ['id' => $strUuid, 'cid' => $arrData['id'], 'type' => $arrData['type'], 'pageID' => $objPage->id, 'title' => $arrData['name']]);
        $objT->addTitle        = $GLOBALS['TL_LANG']['WATCHLIST']['addTitle'];
        $objT->addLink         = $GLOBALS['TL_LANG']['WATCHLIST']['addLink'];
        $objT->newWatchlist    = $GLOBALS['TL_LANG']['WATCHLIST']['newWatchlist'];
        $objT->selectWatchlist = $GLOBALS['TL_LANG']['WATCHLIST']['selectWatchlist'];
        $objT->watchlistTitle  = sprintf($GLOBALS['TL_LANG']['WATCHLIST']['watchlistModalTitle'], $arrData['name']);
        $objT->active          = true;
        $objT->abort           = $GLOBALS['TL_LANG']['WATCHLIST']['abort'];
        $objT->id              = $strUuid;
        $objT->select          = static::getSelectAction($strUuid);

        return $objT->parse();
    }

    /**
     * @param WatchlistItemModel $objItem
     *
     * @return string
     */
    public static function getEditActions(WatchlistItemModel $objItem)
    {
        $objPage = \PageModel::findByPk($objItem->pageID);

        if ($objPage === null) {
            return '';
        }

        $objT           = new \FrontendTemplate('watchlist_edit_actions');
        $objT->delHref  = AjaxAction::generateUrl(static::XHR_GROUP, static::XHR_WATCHLIST_DELETE_ACTION, ['id' => \StringUtil::binToUuid($objItem->uuid)]);
        $objT->delTitle = $GLOBALS['TL_LANG']['WATCHLIST']['delTitle'];
        $objT->delLink  = $GLOBALS['TL_LANG']['WATCHLIST']['delLink'];
        $objT->id       = \StringUtil::binToUuid($objItem->uuid);

        return $objT->parse();
    }

    /**
     * @return string
     */
    public static function getGlobalActions()
    {
        $objT = new \FrontendTemplate('watchlist_global_actions');

        $objT->delAllHref  = AjaxAction::generateUrl(static::XHR_GROUP, static::XHR_WATCHLIST_DELETE_ALL_ACTION);
        $objT->delAllLink  = $GLOBALS['TL_LANG']['WATCHLIST']['delAllLink'];
        $objT->delAllTitle = $GLOBALS['TL_LANG']['WATCHLIST']['delAllTitle'];

        // set id to 0, to get the current watchlist id from session
        $objT->downloadAllHref  = AjaxAction::generateUrl(static::XHR_GROUP, static::XHR_WATCHLIST_DOWNLOAD_ALL_ACTION, ['id' => 0]);
        $objT->downloadAllLink  = $GLOBALS['TL_LANG']['WATCHLIST']['downloadAllLink'];
        $objT->downloadAllTitle = $GLOBALS['TL_LANG']['WATCHLIST']['downloadAllTitle'];
        $objT->useDownloadLink  = false;

        return $objT->parse();
    }

    /**
     * @param integer $downloadLink
     *
     * @return string
     */
    public static function getDownloadLinkAction($downloadLink)
    {
        $objT = new \FrontendTemplate('watchlist_downloadLink_action');

        $objT->useDownloadLink   = true;
        $objT->downloadLinkHref  = AjaxAction::generateUrl(static::XHR_GROUP, static::XHR_WATCHLIST_DOWNLOAD_LINK_ACTION, ['id' => $downloadLink]);
        $objT->downloadLinkTitle = $GLOBALS['TL_LANG']['WATCHLIST']['downloadLinkTitle'];

        return $objT->parse();
    }

    /**
     * @param $notification
     * @param $status
     *
     * @return string
     */
    public static function getNotifications($notification, $status)
    {
        $objT               = new \FrontendTemplate('watchlist_notify');
        $objT->notification = $notification;
        $objT->cssClass     = $status = static::NOTIFY_STATUS_SUCCESS ? $status : static::NOTIFY_STATUS_ERROR;

        return $objT->parse();
    }

    /**
     * @param integer $moduleId
     *
     * @return string
     */
    public static function getMultipleWatchlist($watchlist, $moduleId)
    {
        $objT = new \FrontendTemplate('watchlist_multiple');

        $objT->action            = AjaxAction::generateUrl(static::XHR_GROUP, static::XHR_WATCHLIST_SELECT_ACTION);
        $objT->watchlistHeadline = $GLOBALS['TL_LANG']['WATCHLIST']['headline'];
        $module                  = ModuleModel::findById($moduleId);

        if ($watchlist == null || $module === null) {
            $objT->empty = $GLOBALS['TL_LANG']['WATCHLIST']['empty'];

            return $objT->parse();
        }

        if ($module->useGroupWatchlist) {
            $objT->select = WatchlistModel::getAllWatchlistsByCurrentUser(true, $module->groupWatchlist);
        } else {
            $objT->select = WatchlistModel::getAllWatchlistsByCurrentUser(true);
        }

        $objT->selected = Session::getInstance()->get(static::WATCHLIST_SELECT);
        $items          = WatchlistItemModel::findBy('pid', $watchlist->id);

        $objT->actions = Watchlist::getGlobalActions();
        if ($items == null || $items->count() <= 0) {
            $objT->empty = $GLOBALS['TL_LANG']['WATCHLIST']['empty'];

            return $objT->parse();
        }
        $objT->watchlist = static::getWatchlist($watchlist, $moduleId, false);

        return $objT->parse();
    }

    /**
     * @param      $watchlist
     * @param      $moduleId
     * @param bool $grouped
     *
     * @return string
     */
    public static function getWatchlist($watchlist, $moduleId, $grouped = true)
    {
        $objT   = new \FrontendTemplate($grouped ? 'watchlist_grouped' : 'watchlist');
        $module = ModuleModel::findById($moduleId);

        if ($watchlist == null || $module == null) {
            $objT->empty = $GLOBALS['TL_LANG']['WATCHLIST']['empty'];

            return $objT->parse();
        }

        $items = WatchlistItemModel::findBy('pid', $watchlist->id);
        if ($items == null || $items->count() <= 0) {
            $objT->empty = $GLOBALS['TL_LANG']['WATCHLIST']['empty'];

            return $objT->parse();
        }

        $preparedWatchlistItems = static::prepareWatchlistItems($items, $module, $grouped);

        $objT->pids  = array_keys($preparedWatchlistItems['arrParents']);
        $objT->items = $preparedWatchlistItems['arrItems'];
        $objT->css   = $preparedWatchlistItems['isImage'] = true ? 'watchlist-item-image-list' : '';

        return $objT->parse();
    }

    /**
     * @param $objPage
     * @param $module
     *
     * @return array
     */
    protected static function getParentList($objPage, $module)
    {
        $type   = null;
        $pageId = $objPage->id;
        $pages  = [$objPage->row()];
        $items  = [];

        // Get all pages up to the root page
        $objPages = \PageModel::findParentsById($objPage->pid);

        if ($objPages !== null) {
            while ($pageId > 0 && $type != 'root' && $objPages->next()) {
                $type    = $objPages->type;
                $pageId  = $objPages->pid;
                $pages[] = $objPages->row();
            }
        }

        // Get the first active regular page and display it instead of the root page
        if ($type == 'root') {
            $objFirstPage = \PageModel::findFirstPublishedByPid($objPages->id);

            $items[] = [
                'isRoot'   => true,
                'isActive' => false,
                'href'     => (($objFirstPage !== null) ? \Controller::generateFrontendUrl($objFirstPage->row()) : \Environment::get('base')),
                'title'    => specialchars($objPages->pageTitle ?: $objPages->title, true),
                'link'     => $objPages->title,
                'data'     => $objFirstPage->row(),
                'class'    => '',
            ];

            array_pop($pages);
        }

        // Build the breadcrumb menu
        for ($i = (count($pages) - 1); $i > 0; $i--) {
            if (($pages[$i]['hide'] && !$module->showHidden) || (!$pages[$i]['published'] && !BE_USER_LOGGED_IN)) {
                continue;
            }

            // Get href
            switch ($pages[$i]['type']) {
                case 'redirect':
                    $href = $pages[$i]['url'];

                    if (strncasecmp($href, 'mailto:', 7) === 0) {
                        $href = \StringUtil::encodeEmail($href);
                    }
                    break;

                case 'forward':
                    $objNext = \PageModel::findPublishedById($pages[$i]['jumpTo']);

                    if ($objNext !== null) {
                        $href = \Controller::generateFrontendUrl($objNext->row());
                        break;
                    }
                // DO NOT ADD A break; STATEMENT

                default:
                    $href = \Controller::generateFrontendUrl($pages[$i]);
                    break;
            }

            $items[] = [
                'isRoot'   => false,
                'isActive' => false,
                'href'     => $href,
                'title'    => specialchars($pages[$i]['pageTitle'] ?: $pages[$i]['title'], true),
                'link'     => $pages[$i]['title'],
                'data'     => $pages[$i],
                'class'    => '',
            ];
        }

        // Active page
        $items[] = [
            'isRoot'   => false,
            'isActive' => true,
            'href'     => \Controller::generateFrontendUrl($pages[0]),
            'title'    => specialchars($pages[0]['pageTitle'] ?: $pages[0]['title']),
            'link'     => $pages[0]['title'],
            'data'     => $pages[0],
            'class'    => 'last',
        ];

        $items[0]['class'] = 'first';

        return $items;
    }

    /**
     * @param array       $items
     * @param boolean     $grouped
     * @param ModuleModel $module
     *
     * @return array
     */
    public static function prepareWatchlistItems($items, $module, $grouped)
    {
        $arrItems   = [];
        $arrParents = [];
        $i          = 0;
        $isImage    = false;

        foreach ($items as $id => $item) {

            ++$i;

            $cssClass = trim(($i == 1 ? 'first ' : '') . ($i == $items->count() ? 'last ' : '') . ($i % 2 == 0 ? 'odd ' : 'even '));

            if (!isset($arrParents[$item->pageID])) {

                $objParentT                = new \FrontendTemplate('watchlist_parents');
                $objParentT->items         = static::getParentList(\PageModel::findByPk($item->pageID), $module);
                $arrParents[$item->pageID] = $objParentT->parse();
            }

            $objItemT           = new \FrontendTemplate('watchlist_item');
            $objItemT->cssClass = $cssClass;
            $result             = static::parseItem($item, $module);
            $objItemT->item     = $result['item'];
            $isImage            = $result['isImage'];
            $objItemT->id       = \Contao\StringUtil::binToUuid($item->uuid);


            if ($grouped) {
                $arrItems[$item->pageID]['page']       = $arrParents[$item->pageID];
                $arrItems[$item->pageID]['items'][$id] = $objItemT->parse();

            } else {
                $arrPids[$item->pageID] = $arrParents[$item->pageID];
                $arrItems[$id]          = $objItemT->parse();
            }
        }

        return ['arrItems' => $arrItems, 'arrParents' => $arrParents, 'isImage' => $isImage];
    }

    /**
     * @param WatchlistItemModel $item
     * @param ModuleModel        $module
     *
     * @return array
     */
    protected static function parseItem(WatchlistItemModel $item, $module)
    {
        /** @var $objPage \Contao\PageModel */
        global $objPage;

        $isImage  = false;
        $basePath = $objPage->getFrontendUrl();


        if (\Input::get('auto_item')) {
            $basePath .= '/' . \Input::get('auto_item');
        }

        $objFileModel = \FilesModel::findById($item->uuid);

        if ($objFileModel === null) {
            return ['isImage' => $isImage, 'item' => ''];
        }

        $objFile = new \Contao\File($objFileModel->path, true);

        $objContent = \ContentModel::findByPk($item->cid);

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

        $objT->icon    = TL_ASSETS_URL . 'assets/contao/images/' . $objFile->icon;
        $objT->isImage = $objFile->isImage;
        if ($objFile->isImage) {
            $isImage     = true;
            $objT->image = $objFile->path;

            // resize image if set
            if ($module->imgSize != '') {
                $image = [];

                $size = deserialize($module->imgSize);

                if ($size[0] > 0 || $size[1] > 0 || is_numeric($size[2])) {
                    $image['size'] = $module->imgSize;
                }

                if ($objFileModel->path) {
                    $image['singleSRC'] = $objFileModel->path;
                    \Controller::addImageToTemplate($objT, $image);
                }
            }
        }

        $objT->link      = ($objItemTitle = $item->title) ? $objItemTitle : $linkTitle;
        $objT->download  = $item->type == WatchlistItemModel::WATCHLIST_ITEM_TYPE_DOWNLOAD ? true : false;
        $objT->href      = $basePath . '?file=' . $objFile->path;
        $objT->filesize  = \System::getReadableSize($objFile->filesize, 1);
        $objT->mime      = $objFile->mime;
        $objT->extension = $objFile->extension;
        $objT->path      = $objFile->dirname;
        $objT->id        = \StringUtil::binToUuid($item->uuid);

        $objT->actions = Watchlist::getEditActions($item);

        return ['item' => $objT->parse(), 'isImage' => $isImage];
    }
}