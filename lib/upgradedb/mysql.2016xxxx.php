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

$this->Execute("ALTER TABLE netnodes ADD ownerid int(11) NOT NULL DEFAULT '0'");

$this->Execute("ALTER TABLE netdevicemodels ADD type int(11) NOT NULL DEFAULT '1' AFTER netdeviceproducerid");

$this->Execute("CREATE TABLE netdeviceschema (
  id int(11) NOT NULL AUTO_INCREMENT,
  model int(11) NOT NULL,
  label varchar(20) COLLATE utf8_bin NOT NULL,
  port_type tinyint(1) NOT NULL,
  portcount tinyint(4) NOT NULL,
  continous tinyint(1) NOT NULL DEFAULT '0',
  connector tinyint(1) NOT NULL,
  PRIMARY KEY (id),
  KEY model (model)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin");

$this->Execute("ALTER TABLE netdeviceschema
  ADD CONSTRAINT netdeviceschema_ibfk_1 FOREIGN KEY (model)
  REFERENCES netdevicemodels (id) ON DELETE CASCADE ON UPDATE CASCADE");

$this->Execute("CREATE TABLE netelements (
  id            int(11)         NOT NULL auto_increment,
  name          varchar(32)     NOT NULL DEFAULT '',
  type          tinyint(1)      NOT NULL DEFAULT '0',
  description   text            NOT NULL DEFAULT '',
  producer      varchar(64)     NOT NULL DEFAULT '',
  model         varchar(32)     NOT NULL DEFAULT '',
  serialnumber  varchar(32)     NOT NULL DEFAULT '',
  purchasetime  int(11)         NOT NULL DEFAULT '0',
  guaranteeperiod tinyint unsigned DEFAULT '0',
  netnodeid     int(11)         DEFAULT NULL,
  invprojectid  int(11)         DEFAULT NULL,
  netdevicemodelid int(11) DEFAULT NULL,
  status        tinyint         DEFAULT '0',
  PRIMARY KEY (id),
  FOREIGN KEY (netnodeid) REFERENCES netnodes (id) ON DELETE SET NULL ON UPDATE CASCADE,
  FOREIGN KEY (invprojectid) REFERENCES invprojects (id) ON DELETE SET NULL ON UPDATE CASCADE,
  FOREIGN KEY (netdevicemodelid) REFERENCES netdevicemodels (id) ON UPDATE CASCADE ON DELETE restrict
) ENGINE=InnoDB");

$this->Execute("RENAME TABLE netdevices TO netdevices_old");
$this->Execute("CREATE TABLE netdevices (
  id            int(11)         NOT NULL auto_increment,
  netelemid     int(11)         NOT NULL DEFAULT '0',
  shortname     varchar(32)     NOT NULL DEFAULT '',
  nastype       int(11)         NOT NULL DEFAULT '0',
  clients       int(11)         NOT NULL DEFAULT '0',
  user          varchar(20)     not null default '',
  secret        varchar(60)     NOT NULL DEFAULT '',
  community     varchar(50)     NOT NULL DEFAULT '',
  channelid     int(11)         DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX channelid (channelid),
  FOREIGN KEY (channelid) REFERENCES ewx_channels (id) ON DELETE SET NULL ON UPDATE CASCADE,
  FOREIGN KEY (netelemid) REFERENCES netelements (id) ON DELETE restrict ON UPDATE CASCADE
) ENGINE=InnoDB");

$this->Execute("CREATE TABLE netports (
  id            int(11)         UNSIGNED ZEROFILL NOT NULL auto_increment,
  netelemid     int(11)         NOT NULL DEFAULT '0',
  type          tinyint(2)      NOT NULL DEFAULT '0',
  label         varchar(32)     NOT NULL DEFAULT '',
  connectortype tinyint(3)      NOT NULL DEFAULT '0',
  technology    int(11)         NOT NULL DEFAULT '0',
  capacity      int(11)         UNSIGNED NOT NULL DEFAULT '1',
  PRIMARY KEY (id),
  INDEX netelemid(netelemid),
  FOREIGN KEY (netelemid) REFERENCES netelements (id) ON DELETE restrict ON UPDATE CASCADE,
  UNIQUE KEY label (label, netelemid)
) ENGINE=InnoDB");

$this->Execute("RENAME TABLE netradiosectors TO netradiosectors_old");
$this->Execute("CREATE TABLE netradiosectors (
  id            int(11)         NOT NULL auto_increment,
  netportid     int(11)         UNSIGNED ZEROFILL NOT NULL DEFAULT '0',
  name          varchar(64)     NOT NULL,
  azimuth       decimal(9,2)    DEFAULT 0 NOT NULL,
  width         decimal(9,2)    DEFAULT 0 NOT NULL,
  altitude      smallint        DEFAULT 0 NOT NULL,
  rsrange       int(11)         DEFAULT 0 NOT NULL,
  license       varchar(64)     DEFAULT NULL,
  frequency     numeric(9,5)    DEFAULT NULL,
  frequency2    numeric(9,5)    DEFAULT NULL,
  bandwidth     numeric(9,5)    DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX netportid (netportid),
  FOREIGN KEY (netportid) REFERENCES netports (id) ON DELETE CASCADE ON UPDATE CASCADE,
  UNIQUE KEY name (name, netportid)
) ENGINE=INNODB");

$this->Execute("CREATE TABLE netreserves (
  id            int(11)         NOT NULL auto_increment,
  netcableid    int(11)         NOT NULL DEFAULT '0',
  netnodeid     int(11)         NOT NULL DEFAULT '0',
  priority      tinyint(3)      NOT NULL DEFAULT '1',
  count         smallint(4)     NOT NULL DEFAULT '0',
  PRIMARY KEY (id),
  FOREIGN KEY (netcableid) REFERENCES netelements (id) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (netnodeid) REFERENCES netnodes (id) ON DELETE CASCADE ON UPDATE CASCADE,
  UNIQUE KEY reserve (netcableid,priority)
) ENGINE=InnoDB");

$this->Execute("CREATE TABLE netcables (
  id            int(11)         NOT NULL auto_increment,
  netelemid     int(11)         NOT NULL DEFAULT '0',
  type          tinyint(2)      NOT NULL DEFAULT '0',
  label         varchar(100)    NOT NULL DEFAULT '',
  capacity      smallint(4)     NOT NULL DEFAULT '0',
  distance      int(4)          UNSIGNED NOT NULL DEFAULT '0',
  colorschemaid tinyint(2)      NOT NULL DEFAULT '0',
  dstnodeid     int(11)         DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX netelemid(netelemid),
  FOREIGN KEY (netelemid) REFERENCES netelements (id) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (dstnodeid) REFERENCES netnodes (id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB");

$this->Execute("CREATE TABLE netwires (
  id            int(11)         UNSIGNED ZEROFILL NOT NULL auto_increment,
  netcableid    int(11)         NOT NULL DEFAULT '0',
  type          tinyint(2)      NOT NULL DEFAULT '0',
  bundle        tinyint(2)      NOT NULL DEFAULT '1',
  wire          tinyint(2)      NOT NULL DEFAULT '1',
  PRIMARY KEY (id),
  INDEX netcableid (netcableid),
  FOREIGN KEY (netcableid) REFERENCES netelements (id) ON DELETE CASCADE ON UPDATE CASCADE,
  UNIQUE KEY wire (netcableid,bundle,wire)
) ENGINE=INNODB");

$this->Execute("CREATE TABLE netconnections (
  id            int(11)         NOT NULL auto_increment,
  wires         varchar(100)    NOT NULL DEFAULT '',
  ports         varchar(100)    NOT NULL DEFAULT '',
  parameter     float(6,3)      DEFAULT NULL,
  description   varchar(50)     NOT NULL DEFAULT '',
  PRIMARY KEY (id),
  UNIQUE KEY wires (wires,ports)
) ENGINE=INNODB");


// PRZENIESIENIE DANYCH DO NETELEMENTS
// 1. Przeniesienie danych z netdevices_old do netlements+netdevices
// 1a. Utworzenie netnodes dla netdevices bez odniesienia do netnodes
// 2. Utworzenie w netports portów w/g definicji z netdevices_old+netlinks
// 3. Przeniesienie danych z netradiosectors_old do netradiosectors
// 4. Przeniesienie danych z netlinks do netconnections (same patchcordy)

#$this->Execute("DROP TABLE netradiosectors_old");
#$this->Execute("DROP TABLE netdevices_old");
#$this->Execute("DROP TABLE netlinks");
#$this->Execute("UPDATE dbinfo SET keyvalue = ? WHERE keytype = ?", array('2016xxxx00', 'dbversion'));

$this->CommitTrans();

?>
