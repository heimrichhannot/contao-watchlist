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


class Watchlist extends \System implements \Iterator, \Countable
{
	/**
	 * @var array stores the list of items in the watchlist
	 */
	protected $arrItems;

	/**
	 * Object instance (Singleton)
	 * @var \User
	 */
	protected static $objInstance;

	/**
	 * @var int for tracking iterations
	 */
	protected $position = 0;

	/**
	 * @var int for storing the IDs, as a convenience
	 */
	protected $arrIds;

	protected $strHash;

	protected $strName;

	protected $strIp;

	protected $arrNotifications = array();

	protected $objModel = null;

	protected function __construct()
	{
		$this->strIp = (!\Config::get('disableIpCheck') ? \Environment::get('ip') : '');
		$this->strName = FE_USER_LOGGED_IN ? WATCHLIST_SESSION_FE : WATCHLIST_SESSION_BE;
		$this->strHash = sha1(session_id() . $this->strIp . $this->strName);

		if(($this->objModel = WatchlistModel::findByHashAndName($this->strHash, $this->strName)) === null)
		{
			$this->objModel = new WatchlistModel();
			$this->objModel->hash = $this->strHash;
			$this->objModel->name = $this->strName;
			$this->objModel->tstamp = time();
			$this->objModel->pid = \FrontendUser::getInstance()->id;
			$this->objModel->sessionID = session_id();
			$this->objModel->ip = $this->strIp;
			$this->objModel->save();
		}

		$objItems = WatchlistItemModel::findBy('pid', $this->objModel->id);

		if($objItems !== null)
		{
			while($objItems->next())
			{
				// set key by unique uuid
				$strKey = \String::binToUuid($objItems->uuid);
				$this->arrItems[$strKey] = $objItems->current();
				$this->arrIds[] = $strKey;
			}
		}

	}

	/**
	 * Instantiate a new user object (Factory)
	 *
	 * @return \User The object instance
	 */
	public static function getInstance()
	{
		if (static::$objInstance === null)
		{
			static::$objInstance = new static();
		}

		return static::$objInstance;
	}

	/**
	 * Store the watchlist object in session
	 */
	public function __destruct()
	{

		$this->position = 0;
	}

	/**
	 * Prevent cloning of the object (Singleton)
	 */
	final public function __clone(){}

	/**
	 * @return bool indicating if the watchlist is empty
	 */
	public function isEmpty()
	{
		return (empty($this->arrItems));
	}

	public function generateGlobalActions()
	{
		if ($this->isEmpty()) return;

		global $objPage;

		$objT = new \FrontendTemplate('watchlist_global_actions');

		$objT->delAllHref  = ampersand(\Controller::generateFrontendUrl($objPage->row()) . '?act=' . WATCHLIST_ACT_DELETE_ALL);
		$objT->delAllLink  = $GLOBALS['TL_LANG']['WATCHLIST']['delAllLink'];
		$objT->delAllTitle = $GLOBALS['TL_LANG']['WATCHLIST']['delAllTitle'];

		$objT->downloadAllHref  = ampersand(\Controller::generateFrontendUrl($objPage->row()) . '?act=' . WATCHLIST_ACT_DOWNLOAD_ALL);
		$objT->downloadAllLink  = $GLOBALS['TL_LANG']['WATCHLIST']['downloadAllLink'];
		$objT->downloadAllTitle = $GLOBALS['TL_LANG']['WATCHLIST']['downloadAllTitle'];

		return $objT->parse();
	}

	public function generateEditActions($arrData, $id)
	{
		global $objPage;

		$strClass = $GLOBALS['WLV'][$arrData['type']];

		if (!class_exists($strClass)) return;

		$strategy = new $strClass();

		$view = new WatchlistItemView($strategy);

		$objItem = new WatchlistItemModel();
		$objItem->pid = $this->getId();
		$objItem->uuid = $id;
		$objItem->pageId = $objPage->id;
		$objItem->cid = $arrData['id'];
		$objItem->type = $arrData['type'];

		return $view->generateEditActions($objItem, $arrData, $this);
	}

	public function generateAddActions($arrData, $id)
	{
		$strClass = $GLOBALS['WLV'][$arrData['type']];

		if (!class_exists($strClass)) return;

		$strategy = new $strClass();

		$view = new WatchlistItemView($strategy);

		return $view->generateAddActions($arrData, $id, $this);
	}

	public function generate($grouped = true)
	{
		$objT = new \FrontendTemplate($grouped ? 'watchlist_grouped' : 'watchlist');

		if ($this->isEmpty()) {
			$objT->empty = $GLOBALS['TL_LANG']['WATCHLIST']['empty'];

			return $objT->parse();
		}

		$arrItems   = array();
		$arrParents = array();

		$i = 0;

		while (list($id, $item) = each($this->arrItems)) {
			// get view class by type
			$strClass = $GLOBALS['WLV'][$item->type];

			if (!class_exists($strClass)) continue;

			++$i;

			$strategy = new $strClass();

			$view = new WatchlistItemView($strategy);

			$cssClass = trim(($i == 1 ? 'first ' : '') . ($i == $this->count() ? 'last ' : '') . ($i % 2 == 0 ? 'odd ' : 'even '));

			if (!isset($arrParents[$item->pageID])) {

				$objParentT                  = new \FrontendTemplate('watchlist_parents');
				$objParentT->items           = $this->generateParentList(\PageModel::findByPk($item->pageID));
				$arrParents[$item->pageID] = $objParentT->parse();

			}

			$objItemT           = new \FrontendTemplate('watchlist_item');
			$objItemT->cssClass = $cssClass;
			$objItemT->item     = $view->generate($item, $this);


			if ($grouped) {
				$arrItems[$item->pageID]['page']       = $arrParents[$item->pageID];
				$arrItems[$item->pageID]['items'][$id] = $objItemT->parse();

			} else {
				$arrPids[$item->pageID] = $arrParents[$item->pageID];
				$arrItems[$id]            = $objItemT->parse();
			}
		}

		$objT->pids    = array_keys($arrParents);
		$objT->items   = $arrItems;
		$objT->actions = $this->generateGlobalActions();

		return $objT->parse();
	}

	public function generateNotifications()
	{
		$objT                = new \FrontendTemplate('watchlist_notify_default');
		$objT->notifications = $this->getNotifications();
		$this->clearNotifications();

		return $objT->parse();
	}

	protected function generateParentList($objPage)
	{
		$type   = null;
		$pageId = $objPage->id;
		$pages  = array($objPage->row());
		$items  = array();

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

			$items[] = array
			(
				'isRoot'   => true,
				'isActive' => false,
				'href'     => (($objFirstPage !== null) ? \Controller::generateFrontendUrl($objFirstPage->row()) : \Environment::get('base')),
				'title'    => specialchars($objPages->pageTitle ? : $objPages->title, true),
				'link'     => $objPages->title,
				'data'     => $objFirstPage->row(),
				'class'    => ''
			);

			array_pop($pages);
		}

		// Build the breadcrumb menu
		for ($i = (count($pages) - 1); $i > 0; $i--) {
			if (($pages[$i]['hide'] && !$this->showHidden) || (!$pages[$i]['published'] && !BE_USER_LOGGED_IN)) {
				continue;
			}

			// Get href
			switch ($pages[$i]['type']) {
				case 'redirect':
					$href = $pages[$i]['url'];

					if (strncasecmp($href, 'mailto:', 7) === 0) {
						$href = \String::encodeEmail($href);
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

			$items[] = array
			(
				'isRoot'   => false,
				'isActive' => false,
				'href'     => $href,
				'title'    => specialchars($pages[$i]['pageTitle'] ? : $pages[$i]['title'], true),
				'link'     => $pages[$i]['title'],
				'data'     => $pages[$i],
				'class'    => ''
			);
		}

		// Active page
		$items[] = array
		(
			'isRoot'   => false,
			'isActive' => true,
			'href'     => \Controller::generateFrontendUrl($pages[0]),
			'title'    => specialchars($pages[0]['pageTitle'] ? : $pages[0]['title']),
			'link'     => $pages[0]['title'],
			'data'     => $pages[0],
			'class'    => 'last'
		);

		$items[0]['class'] = 'first';

		return $items;
	}

	/**
	 * Adds a new item to the watchlist
	 * @param WatchlistItemModel $objItem
	 * @throws \Exception
	 */
	public function addItem(WatchlistItemModel $objItem)
	{
		// Throw an exception if there's no id:
		if (!\Validator::isStringUuid($objItem->uuid)) throw new \Exception('The watchlist requires items with an unique file uuid.');

		$strUuid = $objItem->uuid;

		$objItem->uuid = \String::uuidToBin($objItem->uuid); // transform string to bin

		// Add or delete:
		if (isset($this->arrItems[$strUuid]))
		{
			\Input::setGet('act', WATCHLIST_ACT_DELETE);
			$this->deleteItem($strUuid);
		}
		else
		{
			$objItem = $objItem->save();
			$this->arrItems[$strUuid] = $objItem;
			$this->arrIds[]   = $strUuid; // Store the id, too!
			$this->addNotification(sprintf($GLOBALS['TL_LANG']['WATCHLIST']['notify_add_item'], $objItem->getTitle()), WATCHLIST_NOTIFICATION_ADD_ITEM);
		}
	}

	/**
	 * Changes an item already in the watchlist
	 * @param WatchlistItemModel $objItem
	 */
	public function updateItem(WatchlistItemModel $objItem, $strUuid)
	{
		$this->arrItems[$strUuid] = $objItem;
	}

	/**
	 * Removes an item from the cart
	 * @param int $id of the item
	 */
	public function deleteItem($id)
	{
		// Need the unique item id:
		// Remove it:
		if (isset($this->arrItems[$id])) {
			$objItem = $this->arrItems[$id];

			unset($this->arrItems[$id]);

			// Remove the stored id, too:
			$index = array_search($id, $this->arrIds);
			unset($this->arrIds[$index]);

			// Recreate that array to prevent holes:
			$this->arrIds = array_values($this->arrIds);

			$this->addNotification(sprintf($GLOBALS['TL_LANG']['WATCHLIST']['notify_delete_item'], $objItem->getTitle()), WATCHLIST_NOTIFICATION_DELETE_ITEM);

			$objItem->delete();
		}
	}

	public function deleteAll()
	{
		$this->arrItems  = array();
		$this->arrIds = array();
		$this->addNotification($GLOBALS['TL_LANG']['WATCHLIST']['notify_delete_all'], WATCHLIST_NOTIFICATION_DELETE_ALL);

		// Delete the watchlist
		$this->objModel->delete();

		// Delete all items
		foreach($this->arrItems as $objItem)
		{
			$objItem->delete();
		}
	}

	public function downloadAll()
	{
		$strFile = 'download_' . $this->strHash;

		$objZip = new \ZipWriter('system/tmp/' . $strFile);

		while (list($id, $item) = each($this->arrItems)) {
			// get view class by type
			$strClass = $GLOBALS['WLV'][$item->type];

			if (!class_exists($strClass)) continue;

			$strategy = new $strClass();

			$view = new WatchlistItemView($strategy);

			$objZip = $view->generateArchiveOutput($item, $objZip);
		}

		$objZip->close();

		// Open the "save as …" dialogue
		$objFile = new \File('system/tmp/' . $strFile, true);
		$objFile->sendToBrowser($strFile . '.zip');
	}

	public function getHash()
	{
		return $this->strHash;
	}

	/**
	 * Required by Iterator
	 * @return mixed the current value
	 */
	public function current()
	{
		// Get the index for the current position:
		$index = $this->arrIds[$this->position];

		// Return the item:
		return $this->arrItems[$index];
	}

	/**
	 * Required by Iterator
	 * @return int|mixed the current key
	 */
	public function key()
	{
		return $this->position;
	}

	/**
	 * Required by Iterator; increments the position
	 */
	public function next()
	{
		$this->position++;
	}

	/**
	 * Required by Iterator; returns the position to the first spot
	 */
	public function rewind()
	{
		$this->position = 0;
	}

	/**
	 * Required by Iterator
	 * @return bool indiating if a value is indexed at this position
	 */
	public function valid()
	{
		return (isset($this->arrIds[$this->position]));
	}

	/**
	 * Required by Countable
	 * @return int number of items in the list
	 */
	public function count()
	{
		return count($this->arrItems);
	}

	public function addNotification($strText, $key)
	{
		$this->arrNotifications[$key] = $strText;
	}

	public function getNotifications()
	{
		return $this->arrNotifications;
	}


	public function clearNotifications()
	{
		$this->arrNotifications = array();
	}

	public function getItems()
	{
		return $this->arrItems;
	}

	public function getId()
	{
		return $this->objModel->id;
	}

	public function getIds()
	{
		return $this->arrIds;
	}

	public function isInList($id)
	{
		return isset($this->arrItems[$id]);
	}
}