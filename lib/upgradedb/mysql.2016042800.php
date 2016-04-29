<?php
$this->BeginTrans();

$this->Execute("SET foreign_key_checks = 0");

$this->Execute("ALTER TABLE  `netdeviceschema` CHANGE  `portcount`  `technology` INT( 11 ) NULL");

$this->Execute("UPDATE `netdeviceschema` SET technology=null WHERE technology=0");

$this->Execute("UPDATE dbinfo SET keyvalue = ? WHERE keytype = ?", array('2016042800', 'dbversion'));

$this->CommitTrans();


