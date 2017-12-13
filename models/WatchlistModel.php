<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2015 Heimrich & Hannot GmbH
 *
 * @package markenportal
 * @author  Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\Watchlist;


use Contao\Session;

class WatchlistModel extends \Contao\Model
{
    protected static $strTable = 'tl_watchlist';

    /**
     * find watchlist model by hash and name
     *
     * @param       $strHash
     * @param       $strName
     * @param array $arrOptions
     *
     * @return static
     */
    public static function findByHashAndName($strHash, $strName, array $arrOptions = [])
    {
        $t = static::$strTable;

        return static::findOneBy(["$t.hash=?", "$t.name=?"], [$strHash, $strName], $arrOptions);
    }

    /**
     * find all watchlist models by current user
     *
     * @param bool $showDurability
     * @param bool $groups
     *
     * @return array
     */
    public static function getAllWatchlistsByCurrentUser($showDurability = false, $groups = false)
    {
        $watchlistArray = [];
        if (FE_USER_LOGGED_IN === true) {
            if ($groups) {
                $watchlist = static::getAllWatchlistByUserGroups($groups);
            } else {
                $watchlist = static::getAllWatchlistByCurrentUserGroups();
            }
        } else {
            $watchlist = static::findBy('sessionID', session_id());
        }

        if ($watchlist == null) {
            return $watchlistArray;
        }
        foreach ($watchlist as $value) {
            if ($showDurability) {
                if ($value->start <= 0 || $value->stop <= 0) {
                    $watchlistArray[$value->id] = $value->name . ' ( ' . $GLOBALS['TL_LANG']['WATCHLIST']['durability']['immortal'] . ' )';
                    continue;
                }
                $durability = ceil(($value->stop - time()) / (60 * 60 * 24));
                if (intval($durability) < 0) {
                    static::unsetWatchlist($value->id);
                    continue;
                }
                $watchlistArray[$value->id] = $value->name . ' ( ' . $durability . $GLOBALS['TL_LANG']['WATCHLIST']['durability']['days'] . ' )';
            } else {
                $watchlistArray[$value->id] = $value->name;
            }
        }

        return $watchlistArray;
    }

    /**
     * @param $id
     *
     * @return mixed
     */
    public static function deleteWatchlistById($id)
    {
        $watchlist = static::findById($id);

        if ($watchlist == null) {
            return 0;
        }

        $watchlistItems = WatchlistItemModel::findByPid($id);

        foreach ($watchlistItems as $watchlistItem) {
            $watchlistItem->delete();
        }

        return $watchlist->delete();
    }

    /**
     * unset the watchlist, set published = 0
     *
     * @param $id
     *
     * @return mixed
     */
    public static function unsetWatchlist($id)
    {
        $watchlist            = static::findById($id);
        $watchlist->published = 0;

        return $watchlist->save();
    }

    /**
     * returns an array of watchlist models where the members (pid) are in the same group as the current user
     *
     * @return array
     */
    public static function getAllWatchlistByCurrentUserGroups()
    {
        $watchlist       = [];
        $publicWatchlist = static::findPublished();
        if ($publicWatchlist == null) {
            return $watchlist;
        }
        foreach ($publicWatchlist as $watchlistModel) {
            $memberModel = \MemberModel::findById($watchlistModel->pid);
            if ($memberModel === null) {
                continue;
            }
            $watchlistGroups = deserialize($memberModel->groups, true);
            $groups          = deserialize(\FrontendUser::getInstance()->groups, true);
            if (array_intersect($watchlistGroups, $groups)) {
                $watchlist[] = $watchlistModel;
            }
        }

        return $watchlist;
    }

    /**
     * returns an array of watchlist models where the members (pid) are in the same group as the given user group
     *
     * @return array
     */
    public static function getAllWatchlistByUserGroups($groups)
    {
        $t = static::$strTable;

        $watchlist  = [];
        $userGroups = deserialize(\FrontendUser::getInstance()->groups, true);
        $groups     = deserialize($groups, true);
        if (!array_intersect($userGroups, $groups)) {
            return $watchlist;
        }
        $publicWatchlist = static::findPublished();
        if ($publicWatchlist == null) {
            return $watchlist;
        }
        foreach ($publicWatchlist as $watchlistModel) {
            $memberModel = \MemberModel::findById($watchlistModel->pid);
            if ($memberModel === null) {
                continue;
            }
            $watchlistGroups = deserialize($memberModel->groups, true);
            if (array_intersect($watchlistGroups, $groups)) {
                $watchlist[] = $watchlistModel;
            }
        }

        return $watchlist;
    }

    /**
     * returns an array of watchlist models where the members (pid) are in the same group as the given user group
     *
     * @return \Model|null
     */
    public static function getOneWatchlistByUserGroups($groups)
    {
        $watchlist = static::getAllWatchlistByUserGroups($groups);

        if (empty($watchlist)) {
            return null;
        }

        return $watchlist[0];
    }

    /**
     * Find published watchlist
     *
     * @param int   $intLimit
     * @param array $arrOptions
     *
     * @return \Model\Collection|null|static
     */
    public static function findPublished($intLimit = 0, array $arrOptions = [])
    {
        $t            = static::$strTable;
        $time         = time();
        $arrColumns[] = "($t.start='' OR $t.start<='$time') AND ($t.stop='' OR $t.stop>'" . ($time + 60) . "') AND $t.published='1'";

        if ($intLimit > 0) {
            $arrOptions['limit'] = $intLimit;
        }

        return static::findBy($arrColumns, null, $arrOptions);
    }

    /**
     * @param       $pid
     * @param int   $intLimit
     * @param array $arrOptions
     *
     * @return static
     */
    public static function findOnePublishedByPid($pid, $intLimit = 0, array $arrOptions = [])
    {
        $t            = static::$strTable;
        $arrColumns   = ["$t.pid=?"];
        $time         = time();
        $arrColumns[] = "($t.start='' OR $t.start<='$time') AND ($t.stop='' OR $t.stop>'" . ($time + 60) . "') AND $t.published='1'";

        if ($intLimit > 0) {
            $arrOptions['limit'] = $intLimit;
        }

        return static::findOneBy($arrColumns, $pid, $arrOptions);
    }

    /**
     * creates a new watchlist, default published
     *
     * @param string  $name
     * @param integer $pid
     * @param string  $durability
     *
     * @return mixed
     */
    public static function createWatchlist($name, $pid, $durability = null)
    {
        $watchlist            = new static();
        $watchlist->pid       = $pid;
        $watchlist->name      = $name;
        $watchlist->uuid      = \Contao\StringUtil::binToUuid(\Contao\Database::getInstance()->getUuid());
        $watchlist->ip        = (!\Config::get('disableIpCheck') ? \Environment::get('ip') : '');
        $watchlist->sessionID = session_id();
        $watchlist->tstamp    = time();
        $watchlist->published = 1;
        $watchlist->hash      = sha1(session_id() . $watchlist->ip . $name);

        if ($durability == $GLOBALS['TL_LANG']['WATCHLIST']['durability']['default']) {
            $watchlist->start = strtotime('today');
            //add 29 days to timestamp to receive a different of 30 days
            $watchlist->stop = strtotime('tomorrow') + 60 * 60 * 24 * 29;
        }

        return $watchlist->save();
    }

    /**
     * @return int
     */
    public function countItems()
    {
        $objItems = WatchlistItemModel::findBy('pid', $this->id);
        if ($objItems == null) {
            return 0;
        }

        return $objItems->count();
    }

    /**
     * @param $user
     *
     * @return \Model|null
     */
    public static function getMultipleWatchlistModelByUser($user, int $moduleId)
    {
        $module      = \Contao\ModuleModel::findById($moduleId);
        $watchlistId = Session::getInstance()->get(Watchlist::WATCHLIST_SELECT);
        if ($watchlistId == null) {

            if ($module->useGroupWatchlist) {
                $watchlistModel = static::getOneWatchlistByUserGroups($module->groupWatchlist);
            } else {
                $watchlistModel = static::findOnePublishedByPid($user->id, 1);
            }
        } else {
            if ($module->useGroupWatchlist) {
                $watchlistModel = static::findOneBy(['id=?', 'published=?'], [$watchlistId, '1']);
            } else {
                $watchlistModel = static::findOneBy(['id=?', 'published=?', 'pid=?'], [$watchlistId, '1', $user->id]);
            }
        }
        if ($watchlistModel === null) {

            if ($module->useGroupWatchlist) {
                $watchlistModel = static::getOneWatchlistByUserGroups($module->groupWatchlist);
            } else {
                $watchlistModel = static::findOnePublishedByPid($user->id, 1);
            }
        }

        if (null === $watchlistModel) {
            return null;
        }

        Session::getInstance()->set(Watchlist::WATCHLIST_SELECT, $watchlistModel->id);

        return $watchlistModel;
    }

    /**
     * @param int $moduleId
     *
     * @return array|\Model|null
     */
    public static function getMultipleWatchlistModel(int $moduleId)
    {
        if (FE_USER_LOGGED_IN === true) {
            $watchlistModel = static::getMultipleWatchlistModelByUser(\FrontendUser::getInstance(), $moduleId);
        } else {
            $watchlistModel = static::getMultipleWatchlistModelBySession();
        }

        return $watchlistModel;
    }

    /**
     * returns a watchlist from anonymous user
     *
     * @return \Model|null
     */
    public static function getMultipleWatchlistModelBySession()
    {
        $strIp       = (!\Config::get('disableIpCheck') ? \Environment::get('ip') : '');
        $strName     = FE_USER_LOGGED_IN ? WATCHLIST_SESSION_FE : WATCHLIST_SESSION_BE;
        $strHash     = sha1(session_id() . $strIp . $strName);
        $watchlistId = Session::getInstance()->get(Watchlist::WATCHLIST_SELECT);
        if ($watchlistId == null) {
            $watchlistModel = WatchlistModel::findByHashAndName($strHash, $strName);
            Session::getInstance()->set(Watchlist::WATCHLIST_SELECT, $watchlistModel->id);
        } else {
            $watchlistModel = WatchlistModel::findOneBy(['id=?', 'hash'], [$watchlistId, $strHash]);
        }
        if ($watchlistModel === null) {
            $watchlistModel = WatchlistModel::createWatchlist($strName, \FrontendUser::getInstance()->id);
        }

        return $watchlistModel;
    }

    /**
     * @param $id
     *
     * @return WatchlistModel|mixed
     */
    public static function getWatchlistModelByUserId($id)
    {
        $watchlistModel = static::findOnePublishedByPid($id);
        if ($watchlistModel === null) {
            $strName        = FE_USER_LOGGED_IN ? WATCHLIST_SESSION_FE : WATCHLIST_SESSION_BE;
            $watchlistModel = static::createWatchlist($strName, \FrontendUser::getInstance()->id);
        }

        return $watchlistModel;
    }


    /**
     * returns a watchlist from anonymous user
     *
     * @return \Model|null
     */
    public static function getWatchlistModelBySession()
    {
        $strIp          = (!\Config::get('disableIpCheck') ? \Environment::get('ip') : '');
        $strName        = FE_USER_LOGGED_IN ? WATCHLIST_SESSION_FE : WATCHLIST_SESSION_BE;
        $strHash        = sha1(session_id() . $strIp . $strName);
        $watchlistModel = static::findByHashAndName($strHash, $strName);
        if ($watchlistModel === null) {
            $watchlistModel = static::createWatchlist($strName, $strIp);
        }

        return $watchlistModel;
    }

    /**
     * @return WatchlistModel
     */
    public static function getWatchlistModel()
    {
        if (FE_USER_LOGGED_IN === true) {
            $watchlistModel = static::getWatchlistModelByUserId(\FrontendUser::getInstance()->id);
        } else {
            $watchlistModel = static::getWatchlistModelBySession();
        }
        Session::getInstance()->set(Watchlist::WATCHLIST_SELECT, $watchlistModel->id);

        return $watchlistModel;
    }
}