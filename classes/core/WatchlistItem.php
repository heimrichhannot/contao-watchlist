<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2014 Heimrich & Hannot GmbH
 * @package Watchlist
 * @author Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\Watchlist;


use Contao\FilesModel;

class WatchlistItem extends WatchlistItemModel
{
	protected $id;
	protected $cid;
	protected $pageID;
	protected $type;
	protected $title;

	public function __construct($id, $pageID, $cid, $type, $title = '')
	{
		$this->id   = $id;
		$this->pageID  = $pageID;
		$this->cid = $cid;
		$this->type = $type;
		$this->title = $title;
	}

	public function getTitle()
	{
		if($this->title) return $this->title;

		// get view class by type
		$strClass = $GLOBALS['WLV'][$this->type];

		if (!class_exists($strClass)) return;

		$strategy = new $strClass();

		$view = new WatchlistItemView($strategy);

		return $view->getTitle($this);
	}

	public function getUid()
	{
		return $this->type . '_' . $this->id ;
	}

	public function getId()
	{
		return $this->id;
	}

	public function getCid()
	{
		return $this->cid;
	}

	public function getPageID()
	{
		return $this->pageID;
	}

	public function getType()
	{
		return $this->type;
	}

	public function getUuid()
	{
		$objModel = FilesModel::findById($this->id);

		if($objModel !== null)
		{
			return \String::binToUuid($objModel->uuid);
		}
	}

	public function save($pid)
	{
		$objModel = new WatchlistItemModel();
		$objModel->uuid = \String::uuidToBin($this->id);
		$objModel->pid = $pid;
		$objModel->pageID = $this->pageID;
		$objModel->cid = $this->cid;
		$objModel->tstamp = time();
		$objModel->title = $this->getTitle();
		$objModel->type = $this->type;
		return $objModel->save();
	}

}
