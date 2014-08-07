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
	protected $pid;
	protected $type;

	public function __construct($id, $pid, $type)
	{
		$this->id   = $id;
		$this->pid  = $pid;
		$this->type = $type;
	}

	public function getId()
	{
		return $this->id;
	}

	public function generate()
	{
		return $this->pid;
	}

	public function getType()
	{
		return $this->type;
	}
}