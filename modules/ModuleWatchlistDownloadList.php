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
use HeimrichHannot\Request\Request;
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

        if (Request::getGet('file')) {
            \Contao\Controller::sendFileToBrowser(Request::getGet('file'));
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
        $watchlist = WatchlistModel::findOneBy(['uuid=?', 'published=?'], [$id, '1']);
        if (!$this->checkWatchlistValidity($watchlist)) {
            /** @var \PageError404 $objHandler */
            $objHandler = new $GLOBALS['TL_PTY']['error_404']();
            $objHandler->generate($objPage->id);
        }
        $array = $this->getWatchlistItems($watchlist);
        if (empty($array['items'])) {
            $this->Template->empty = $GLOBALS['TL_LANG']['WATCHLIST']['empty'];
        }
        $watchlistController                  = new WatchlistController();
        $this->Template->downloadAllButton    = $array['downloadAllButton'];
        $this->Template->items                = $array['items'];
        $this->Template->downloadAllHref      = $watchlistController->downloadAll($watchlist);
        $this->Template->downloadAllLink      = $GLOBALS['TL_LANG']['WATCHLIST']['downloadAll'];
        $this->Template->downloadAllTitle     = $GLOBALS['TL_LANG']['WATCHLIST']['downloadAllSecondTitle'];
        $this->Template->downloadListHeadline = $GLOBALS['TL_LANG']['WATCHLIST']['downloadListHeadline'];
    }

    /**
     * @param $watchlist
     *
     * @return array
     */
    public function getWatchlistItems($watchlist)
    {
        $arrItems          = [];
        $downloadAllButton = false;

        if ($watchlist === null) {
            return $arrItems;
        }

        $items = WatchlistItemModel::findByPid($watchlist->id);
        if ($items === null) {
            return $arrItems;
        }
        foreach ($items as $item) {
            if ($item->type == WatchlistItemModel::WATCHLIST_ITEM_TYPE_DOWNLOAD) {
                $downloadAllButton = true;
            }
            $arrItems[] = $this->prepareItem($item);
        }

        return ['items' => $arrItems, 'downloadAllButton' => $downloadAllButton];
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

        if ($objFileModel->copyright !== null && deserialize($objFileModel->copyright, true)[0] !== null) {
            $copyright = deserialize($objFileModel->copyright, true);
        }

        $objT->copyright     = $copyright;
        $objT->title         = $item->title;
        $objT->id            = \StringUtil::binToUuid($item->uuid);
        $objT->filesize      = \System::getReadableSize($objFile->filesize, 1);
        $objT->downloadLink  = $basePath . '&file=' . $objFile->path;
        $objT->downloadTitle = $GLOBALS['TL_LANG']['WATCHLIST']['download'];
        $objT->noDownload    = $GLOBALS['TL_LANG']['WATCHLIST']['noDownload'];

        // HOOK: add custom logic
        if (isset($GLOBALS['TL_HOOKS']['parseWatchlistItems']) && is_array($GLOBALS['TL_HOOKS']['parseWatchlistItems'])) {
            foreach ($GLOBALS['TL_HOOKS']['parseWatchlistItems'] as $callback) {
                $this->import($callback[0]);
                $this->{$callback[0]}->{$callback[1]}($objT, $item, $this);
            }
        }

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