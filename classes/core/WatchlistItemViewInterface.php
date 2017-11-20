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


use HeimrichHannot\Watchlist\Controller\WatchlistController;

interface WatchlistItemViewInterface
{
    public function generate(WatchlistItemModel $objItem, WatchlistController $objWatchlist);

    public function generateEditActions(WatchlistItemModel $objItem, WatchlistController $objWatchlist);

    public function generateAddActions($arrData, $id, WatchlistController $objWatchlist);

    public function generateArchiveOutput(WatchlistItemModel $objItem, \ZipWriter $objZip);

    public function getTitle(WatchlistItemModel $objItem);
}