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
	/**
	 * @var array stores the list of items in the watchlist
	 */
	protected $items;

	/**
	 * @var int for tracking iterations
	 */
	protected $position = 0;

	protected $ids;

	protected static $strCookie = WATCHLIST_FE_COOKIE;

	protected $strIp;

	protected $strHash;

	protected function __construct()
	{
		$this->strIp = \Environment::get('ip');
		$this->generateSession();
	}

	public static function getInstance()
	{
		if (($strCookie = \Input::cookie(static::$strCookie)) != '' && \Session::getInstance()->get(WATCHLIST_SESSION)) {
			$objInstance = unserialize(\Session::getInstance()->get(WATCHLIST_SESSION));
			return $objInstance;
		}

		return new static();
	}

	/**
	 * Store the object in session
	 */
	public function __destruct()
	{
		$this->position = 0;
		\Session::getInstance()->set(WATCHLIST_SESSION, serialize($this));
	}

	protected function generateSession()
	{
		$time = time();

		// Generate the cookie hash
		$this->strHash = sha1(session_id() . (!\Config::get('disableIpCheck') ? $this->strIp : '') . static::$strCookie);

		// Clean up old sessions
		\Database::getInstance()->prepare("DELETE FROM tl_session WHERE tstamp<? OR hash=?")
			->execute(($time - \Config::get('sessionTimeout')), $this->strHash);

		// Save the session in the database
		\Database::getInstance()->prepare("INSERT INTO tl_session (pid, tstamp, name, sessionID, ip, hash) VALUES (?, ?, ?, ?, ?, ?)")
			->execute(FE_USER_LOGGED_IN ? $this->User->id : 0, $time, static::$strCookie, session_id(), $this->strIp, $this->strHash);

		// Set the authentication cookie
		$this->setCookie(static::$strCookie, $this->strHash, ($time + WATCHLIST_FE_COOKIE_LIFETIME), null, null, false, true);
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

	public function generate($grouped = true)
	{
		$objT = new \FrontendTemplate($grouped ? 'watchlist_grouped' : 'watchlist');

		if ($this->isEmpty()) {
			$objT->empty = 'No items';

			return $objT->parse();
		}

		$arrItems = array();
		$arrPids  = array();

		$i = 0;

		while (list($id, $item) = each($this->items)) {
			// get view class by type
			$strClass = $GLOBALS['WLV'][$item->getType()];

			if (!class_exists($strClass)) continue;

			$strategy = new $strClass();

			$view = new WatchlistItemView($strategy);

			$strClass = ($i = 0 ? : 'first') . ($i == $this->count() ? : 'last') . ($i % 2 == 0 ? 'even' : 'odd');

			if ($grouped) {
				if (!isset($arrItems[$item->getPid()])) {
					$arrItems[$item->getPid()]['page'] = \PageModel::findByPk($item->getPid());
				}

				$arrItems[$item->getPid()]['items'][$id] = $view->generate($item);
			} else {
				$arrItems[$id] = $view->generate($item);
				if (!isset($arrPids[$item->getPid()])) {
					$arrPids[$item->getPid()] = \PageModel::findByPk($item->getPid());
				}
			}

			$i++;
		}

		$objT->pids  = $arrPids;
		$objT->items = $arrItems;

		return $objT->parse();
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
	 * @param WatchlistItem $item
	 */
	public function deleteItem(WatchlistItem $item)
	{
		// Need the unique item id:
		$id = $item->getId();

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