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


class WatchlistItem
{

	protected $id;
	protected $cid;
	protected $pid;
	protected $type;

	public function __construct($id, $pid, $cid, $type)
	{
		$this->id   = $id;
		$this->pid  = $pid;
		$this->cid = $cid;
		$this->type = $type;
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

	public function getPid()
	{
		return $this->pid;
	}

	public function getType()
	{
		return $this->type;
	}
}