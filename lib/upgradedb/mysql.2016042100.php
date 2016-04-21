<?php
$this->BeginTrans();

$this->Execute("SET foreign_key_checks = 0");

$this->Execute("ALTER TABLE  `netelements` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci");

$this->Execute("ALTER TABLE  `netelements` CHANGE  `name`  `name` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
		CHANGE  `description`  `description` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
		CHANGE  `producer`  `producer` VARCHAR( 64 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL");
		
$this->Execute("ALTER TABLE  `netdevices` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci");

$this->Execute("ALTER TABLE  `netdevices` CHANGE  `shortname`  `shortname` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL");

$this->Execute("UPDATE dbinfo SET keyvalue = ? WHERE keytype = ?", array('2016042100', 'dbversion'));

$this->CommitTrans();


