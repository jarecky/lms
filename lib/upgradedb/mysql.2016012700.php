<?php

/*
 * LMS version 1.11-git
 *
 *  (C) Copyright 2001-2016 LMS Developers
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License Version 2 as
 *  published by the Free Software Foundation.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307,
 *  USA.
 *
 */

$this->BeginTrans();

$this->Execute("
	CREATE TABLE netobjects (
		id int(11)				NOT NULL auto_increment,
		type tinyint			DEFAULT '0',
		name varchar(32)		NOT NULL DEFAULT '',
		location varchar(255)	NOT NULL DEFAULT '',
		location_city int(11)	DEFAULT NULL,
		location_street int(11)	DEFAULT NULL,
		location_house varchar(32)	DEFAULT NULL,
		location_flat varchar(32)	DEFAULT NULL,
		description text			NOT NULL DEFAULT '',
		producer varchar(64)		NOT NULL DEFAULT '',
		model varchar(32)			NOT NULL DEFAULT '',
		serialnumber varchar(32)	NOT NULL DEFAULT '',
		parameter varchar(32)		NOT NULL DEFAULT '',
		purchasetime int(11)		NOT NULL DEFAULT '0',
		guaranteeperiod tinyint		UNSIGNED DEFAULT '0',
		longitude decimal(10, 6)	DEFAULT NULL,
		latitude decimal(10, 6)		DEFAULT NULL,
		netnodeid int(11)			DEFAULT NULL,
		invprojectid int(11)		DEFAULT NULL,
		status tinyint		DEFAULT '0',
		PRIMARY KEY (id),
		INDEX location_city (location_city, location_street, location_house, location_flat),
		INDEX location_street (location_street),
		FOREIGN KEY (location_city) REFERENCES location_cities (id) ON DELETE SET NULL ON UPDATE CASCADE,
		FOREIGN KEY (location_street) REFERENCES location_streets (id) ON DELETE SET NULL ON UPDATE CASCADE,
		FOREIGN KEY (netnodeid) REFERENCES netnodes (id) ON DELETE SET NULL ON UPDATE CASCADE,
		FOREIGN KEY (invprojectid) REFERENCES invprojects (id) ON DELETE SET NULL ON UPDATE CASCADE
	) ENGINE=InnoDB
");

$this->Execute("
	CREATE TABLE netcables (
		id int(11)			NOT NULL auto_increment,
		name varchar(32)	NOT NULL DEFAULT '',
		fibers int(5)		NOT NULL,
		length int(11)		NOT NULL default '0',
		src int(11)			DEFAULT NULL,
		dst int(11)			DEFAULT NULL,
		description text	NOT NULL DEFAULT '',
		producer varchar(64)	NOT NULL DEFAULT '',
		model varchar(32)		NOT NULL DEFAULT '',
		purchasetime int(11)	NOT NULL DEFAULT '0',
		guaranteeperiod tinyint	UNSIGNED DEFAULT '0',
		invprojectid int(11)	DEFAULT NULL,
		status tinyint			DEFAULT '0',
		PRIMARY KEY (id),
		FOREIGN KEY (src) REFERENCES netobjects (id) ON DELETE SET NULL ON UPDATE CASCADE,
		FOREIGN KEY (dst) REFERENCES netobjects (id) ON DELETE SET NULL ON UPDATE CASCADE,
		FOREIGN KEY (invprojectid) REFERENCES invprojects (id) ON DELETE SET NULL ON UPDATE CASCADE
	) ENGINE=InnoDB
");

$this->Execute("
	CREATE TABLE netsplices (
		id int(11)			NOT NULL auto_increment,
		objectid int(11)	DEFAULT NULL,
		srccableid int(11)	DEFAULT NULL,
		srctube tinyint		UNSIGNED NOT NULL DEFAULT '0',
		srcfiber tinyint	UNSIGNED NOT NULL DEFAULT '0',
		dstcableid int(11)	DEFAULT NULL,
		dsttube tinyint		UNSIGNED NOT NULL DEFAULT '0',
		dstfiber tinyint	UNSIGNED NOT NULL DEFAULT '0',
		position smallint	UNSIGNED DEFAULT NULL,
		description text	NOT NULL DEFAULT '',
		PRIMARY KEY (id),
		FOREIGN KEY (objectid) REFERENCES netobjects (id) ON DELETE CASCADE ON UPDATE CASCADE,
		FOREIGN KEY (srccableid) REFERENCES netcables (id) ON DELETE CASCADE ON UPDATE CASCADE,
		FOREIGN KEY (dstcableid) REFERENCES netcables (id) ON DELETE CASCADE ON UPDATE CASCADE
	) ENGINE=InnoDB
");

$this->Execute("UPDATE dbinfo SET keyvalue = ? WHERE keytype = ?", array('2016012700', 'dbversion'));

$this->CommitTrans();

?>
