# Watchlist

A contao watchlist, for download elements.

## Features

* use single or multiple watchlist
* generate link to watchlist (durability 30 days)
* define durability of the watchlist
* users of the same group can use same watchlist
* download watchlist items and watchlist

## Installation
Install via composer

```
composer require heimrichhannot/contao-watchlist
```
Afterwards call the Contao install procedure to update the database.

## Add Item Button

```
$template->addWatchlist = Watchlist::getAddAction($array, $uuid, $multiple);
```

```
$array = [
	'name' => 'name of the item',
	'type' => 'type of the item (e.g. download)',
	'id' => 'id of the item'
];
```

* $uuid is the uuid of the \Contao\FileModel
* $multiple is true or false for the usage of the single or multiple watchlist
