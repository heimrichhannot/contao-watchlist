<?php
/**
 * Created by PhpStorm.
 * User: kwagner
 * Date: 23.10.17
 * Time: 14:58
 */

namespace HeimrichHannot\Watchlist;


use Contao\FrontendTemplate;
use HeimrichHannot\Ajax\AjaxAction;
use HeimrichHannot\Watchlist\Controller\WatchlistController;

class ModuleWatchlistDownloadList extends \Module
{
    protected $strTemplate = 'mod_watchlist_download_list';

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
        $GLOBALS['TL_JAVASCRIPT']['watchlist'] = 'system/modules/watchlist/assets/js/jquery.watchlist.js|static';

        if (\Input::get('file')) {
            \Contao\Controller::sendFileToBrowser(\Input::get('file'));
        }

        if (!\Input::get('watchlist')) {
            return '';
        }


        return parent::generate();
    }

    protected function compile()
    {
        /** @var \PageModel $objPage */
        global $objPage;

        $id        = \Input::get('watchlist');
        $watchlist = WatchlistModel::findBy(['uuid=?', 'published=?'], [$id, '1']);
        if (!$this->checkWatchlistValidity($watchlist)) {
            /** @var \PageError404 $objHandler */
            $objHandler = new $GLOBALS['TL_PTY']['error_404']();
            $objHandler->generate($objPage->id);
        }
        $items = $this->getWatchlistItems($watchlist);
        if (empty($items)) {
            $this->Template->empty = $GLOBALS['TL_LANG']['WATCHLIST']['empty'];
        }
        $this->Template->items                = $items;
        $this->Template->downloadAllHref      = AjaxAction::generateUrl(WatchlistController::XHR_GROUP, WatchlistController::XHR_WATCHLIST_DOWNLOAD_ALL_ACTION, ['id' => $watchlist->id]);
        $this->Template->downloadAllLink      = $GLOBALS['TL_LANG']['WATCHLIST']['downloadAll'];
        $this->Template->downloadAllTitle     = $GLOBALS['TL_LANG']['WATCHLIST']['downloadAllSecondTitle'];
        $this->Template->downloadListHeadline = $GLOBALS['TL_LANG']['WATCHLIST']['downloadListHeadline'];
    }

    public function getWatchlistItems($watchlist)
    {
        $arrItems = [];

        if ($watchlist === null) {
            return $arrItems;
        }

        $items = WatchlistItemModel::findByPid($watchlist->id);
        if ($items === null) {
            return $arrItems;
        }

        foreach ($items as $item) {
            $arrItems[] = $this->prepareItem($item);
        }

        return $arrItems;
    }

    public function prepareItem($item)
    {
        /** @var $objPage \Contao\PageModel */
        global $objPage;

        $basePath = $objPage->getFrontendUrl();
        if (\Input::get('watchlist')) {
            $basePath .= '?watchlist=' . \Input::get('watchlist');
        }

        $objT           = new FrontendTemplate('watchlist_download_list_item');
        $objT->download = true;

        $objFileModel = \FilesModel::findById($item->uuid);

        if ($objFileModel === null) {
            return;
        }

        $objFile = new \File($objFileModel->path, true);

        $objT->isImage = $objFile->isImage;

        if ($objFile->isImage) {
            $objT->image = $objFile->path;

            // resize image if set
            if ($this->imgSize != '') {
                $image = [];

                $size = deserialize($this->imgSize);

                if ($size[0] > 0 || $size[1] > 0 || is_numeric($size[2])) {
                    $image['size'] = $this->imgSize;
                }

                if ($objFileModel->path) {
                    $image['singleSRC'] = $objFileModel->path;
                    \Controller::addImageToTemplate($objT, $image);
                }
            }
        }

        if ($item->type !== WatchlistItemModel::WATCHLIST_ITEM_TYPE_DOWNLOAD) {
            $objT->download = false;
        }
        $copyright = null;

        if ($objFileModel->copyright !== null) {
            $copyright = deserialize($objFileModel->copyright, true);
        }

        $objT->copyright     = $copyright;
        $objT->title         = $item->title;
        $objT->id            = \StringUtil::binToUuid($item->uuid);
        $objT->filesize      = \System::getReadableSize($objFile->filesize, 1);
        $objT->downloadLink  = $basePath . '&file=' . $objFile->path;
        $objT->downloadTitle = $GLOBALS['TL_LANG']['WATCHLIST']['download'];
        $objT->noDownload    = $GLOBALS['TL_LANG']['WATCHLIST']['noDownload'];

        return $objT->parse();
    }

    /**
     * check if the startedShare date is <= 30
     *
     * @param $watchlist
     *
     * @return bool
     */
    protected function checkWatchlistValidity($watchlist)
    {
        $difference = (strtotime('tomorrow') - $watchlist->startedShare) / (60 * 60 * 24);

        if ($difference <= 30) {
            return true;
        }

        return false;
    }
}