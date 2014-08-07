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


use Contao\FrontendTemplate;

class Watchlist implements \Iterator, \Countable
{
	/**
	 * @var array stores the list of items in the watchlist
	 */
	protected $items = array();

	/**
	 * @var int for tracking iterations
	 */
	protected $position = 0;

	protected $ids = array();

	function __construct()
	{
		$this->items = array();
		$this->ids   = array();
	}

	/**
	 * @return bool indicating if the watchlist is empty
	 */
	public function isEmpty()
	{
		return (empty($this->items));
	}

	public function generate()
	{
		$objT = new \FrontendTemplate('watchlist');

		if ($this->isEmpty()) {
			$objT->empty = 'No items';

			return $objT->parse();
		}

		$arrItems = array();

		foreach ($this->items as $id => $item) {
			switch ($item->getType()) {
				case 'download':
					$strategy = new WatchlistItemDownload();
					break;
			}

			$view          = new WatchlistItemView($strategy);
			$arrItems[$id] = $view->generate($item);
		}

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