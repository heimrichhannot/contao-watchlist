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


class Watchlist extends \Controller implements \Iterator, \Countable
{
	protected static $objInstance;

	/**
	 * @var array stores the list of items in the watchlist
	 */
	protected $items;

	/**
	 * @var int for tracking iterations
	 */
	protected $position = 0;

	/**
	 * @var int for storing the IDs, as a convenience
	 */
	protected $ids;

	protected $strHash;

	protected function __construct()
	{
		$this->strHash = sha1(session_id() . (!\Config::get('disableIpCheck') ? \Environment::get('ip') : '') . WATCHLIST_SESSION);
	}

	/**
	 * Session Singleton
	 * Load watchlist from Session if exists
	 * @return mixed|static
	 */
	public static function getInstance()
	{
		$objInstance = new static();

		if (\Session::getInstance()->get(WATCHLIST_SESSION)){
			$objInstance = unserialize(\Session::getInstance()->get(WATCHLIST_SESSION));
		}

		return $objInstance;
	}

	/**
	 * Store the watchlist object in session
	 */
	public function __destruct()
	{
		$this->position = 0;
		\Session::getInstance()->set(WATCHLIST_SESSION, serialize($this));
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
		return (empty($this->items));
	}

	public function generateActions($arrData)
	{
		global $objPage;

		$strClass = $GLOBALS['WLV'][$arrData['type']];

		if (!class_exists($strClass)) return;

		$strategy = new $strClass();

		$view = new WatchlistItemView($strategy);

		$objItem = new WatchlistItem($arrData['id'], $objPage->id, $arrData['type']);

		return $view->generateAddActions($objItem, $this->strHash);
	}

	public function generate($grouped = true)
	{
		$objT = new \FrontendTemplate($grouped ? 'watchlist_grouped' : 'watchlist');

		if ($this->isEmpty()) {
			$objT->empty = 'No items';

			return $objT->parse();
		}

		$arrItems   = array();
		$arrParents = array();

		$i = 0;

		while (list($id, $item) = each($this->items)) {
			// get view class by type
			$strClass = $GLOBALS['WLV'][$item->getType()];

			if (!class_exists($strClass)) continue;

			++$i;

			$strategy = new $strClass();

			$view = new WatchlistItemView($strategy);

			$cssClass = trim(($i == 1 ? 'first ' : '') . ($i == $this->count() ? 'last ' : '') . ($i % 2 == 0 ? 'odd ' : 'even '));

			if (!isset($arrParents[$item->getPid()])) {

				$objParentT                  = new \FrontendTemplate('watchlist_parents');
				$objParentT->items           = $this->generateParentList(\PageModel::findByPk($item->getPid()));
				$arrParents[$item->getPid()] = $objParentT->parse();

			}

			$objItemT           = new \FrontendTemplate('watchlist_item');
			$objItemT->cssClass = $cssClass;
			$objItemT->item     = $view->generate($item, $this->strHash);


			if ($grouped) {
				$arrItems[$item->getPid()]['page']       = $arrParents[$item->getPid()];
				$arrItems[$item->getPid()]['items'][$id] = $objItemT->parse();

			} else {
				$arrPids[$item->getPid()] = $arrParents[$item->getPid()];
				$arrItems[$id]            = $objItemT->parse();
			}
		}

		$objT->pids  = array_keys($arrParents);
		$objT->items = $arrItems;

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
				'href'     => (($objFirstPage !== null) ? $this->generateFrontendUrl($objFirstPage->row()) : \Environment::get('base')),
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
						$href = $this->generateFrontendUrl($objNext->row());
						break;
					}
				// DO NOT ADD A break; STATEMENT

				default:
					$href = $this->generateFrontendUrl($pages[$i]);
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
			'href'     => $this->generateFrontendUrl($pages[0]),
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
	 * @param WatchlistItem $item
	 * @throws \Exception
	 */
	public function addItem(WatchlistItem $item)
	{

		// Need the item id:
		$id = $item->getId();

		// Throw an exception if there's no id:
		if (!$id) throw new \Exception('The watchlist requires items with unique ID values.');

		// Add or update:
		if (isset($this->items[$id])) {
			$this->updateItem($item);
		} else {
			$this->items[$id] = $item;
			$this->ids[]      = $id; // Store the id, too!
		}
	}

	/**
	 * Changes an item already in the watchlist
	 * @param WatchlistItem $item
	 */
	public function updateItem(WatchlistItem $item)
	{
		// Need the unique item id:
		$id = $item->getId();

		$this->items[$id] = $item;
	}

	/**
	 * Removes an item from the cart
	 * @param int $id of the item
	 */
	public function deleteItem($id)
	{
		// Need the unique item id:
		// Remove it:
		if (isset($this->items[$id])) {
			unset($this->items[$id]);

			// Remove the stored id, too:
			$index = array_search($id, $this->ids);
			unset($this->ids[$index]);

			// Recreate that array to prevent holes:
			$this->ids = array_values($this->ids);
		}
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
		$index = $this->ids[$this->position];

		// Return the item:
		return $this->items[$index];
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
		return (isset($this->ids[$this->position]));
	}

	/**
	 * Required by Countable
	 * @return int number of items in the list
	 */
	public function count()
	{
		return count($this->items);
	}
}