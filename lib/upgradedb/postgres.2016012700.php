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
	CREATE SEQUENCE netobjects_id_seq;
	CREATE TABLE netobjects (
		id integer DEFAULT nextval('netobjects_id_seq'::text) NOT NULL,
		type smallint			DEFAULT 0,
		name varchar(32)		NOT NULL DEFAULT '',
		location varchar(255)	NOT NULL DEFAULT '',
		location_city integer	DEFAULT NULL
			REFERENCES location_cities (id) ON DELETE SET NULL ON UPDATE CASCADE,
		location_street integer	DEFAULT NULL
			REFERENCES location_streets (id) ON DELETE SET NULL ON UPDATE CASCADE,
		location_house varchar(32)	DEFAULT NULL,
		location_flat varchar(32)	DEFAULT NULL,
		description text		NOT NULL DEFAULT '',
		producer varchar(64)	NOT NULL DEFAULT '',
		model varchar(32)		NOT NULL DEFAULT '',
		serialnumber varchar(32)	NOT NULL DEFAULT '',
		parameter varchar(32)	NOT NULL DEFAULT '',
		purchasetime integer	NOT NULL DEFAULT 0,
		guaranteeperiod smallint	DEFAULT 0,
		longitude numeric(10, 6)	DEFAULT NULL,
		latitude numeric(10, 6)		DEFAULT NULL,
		netnodeid integer		DEFAULT NULL
			REFERENCES netnodes(id) ON DELETE SET NULL ON UPDATE CASCADE,
		invprojectid integer	DEFAULT NULL
			REFERENCES invprojects(id) ON DELETE SET NULL ON UPDATE CASCADE,
		status smallint			DEFAULT 0,
		PRIMARY KEY (id)
	);
	CREATE INDEX netobjects_locaction_city_idx ON netobjects (location_city, location_street, location_house, location_flat);
	CREATE INDEX netobjects_locaction_street_idx ON netobjects (location_street);
");

$this->Execute("
	CREATE SEQUENCE netcables_id_seq;
	CREATE TABLE netcables (
		id integer DEFAULT nextval('netcables_id_seq'::text) NOT NULL,
		name varchar(32)	NOT NULL DEFAULT '',
		fibers smallint		NOT NULL,
		length integer		NOT NULL default 0,
		src integer			DEFAULT NULL
			REFERENCES netobjects (id) ON DELETE SET NULL ON UPDATE CASCADE,
		dst integer			DEFAULT NULL
			REFERENCES netobjects (id) ON DELETE SET NULL ON UPDATE CASCADE,
		description text	NOT NULL DEFAULT '',
		producer varchar(64)	NOT NULL DEFAULT '',
		model varchar(32)	NOT NULL DEFAULT '',
		purchasetime integer	NOT NULL DEFAULT 0,
		guaranteeperiod smallint	DEFAULT 0,
		invprojectid integer	DEFAULT NULL
			REFERENCES invprojects (id) ON DELETE SET NULL ON UPDATE CASCADE,
		status smallint		DEFAULT 0,
		PRIMARY KEY (id)
	)
");

$this->Execute("
	CREATE SEQUENCE netsplices_id_seq;
	CREATE TABLE netsplices (
		id integer DEFAULT nextval('netsplices_id_seq'::text) NOT NULL,
		objectid integer		DEFAULT NULL
			REFERENCES netobjects (id) ON DELETE CASCADE ON UPDATE CASCADE,
		srccableid integer		DEFAULT NULL
			REFERENCES netcables (id) ON DELETE CASCADE ON UPDATE CASCADE,
		srctube smallint		NOT NULL DEFAULT 0,
		srcfiber smallint		NOT NULL DEFAULT 0,
		dstcableidinteger		DEFAULT NULL
			REFERENCES netcables (id) ON DELETE CASCADE ON UPDATE CASCADE,
		dsttube smallint		NOT NULL DEFAULT 0,
		dstfiber smallint		NOT NULL DEFAULT 0,
		position smallint		DEFAULT NULL,
		description text		NOT NULL DEFAULT '',
		PRIMARY KEY (id)
	)
");

$this->Execute("UPDATE dbinfo SET keyvalue = ? WHERE keytype = ?", array('2016012700', 'dbversion'));

$this->CommitTrans();

?>
