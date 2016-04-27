<?php


$this->BeginTrans();

$this->Execute("alter table netports change type type int(5) unsigned not null default '0'");
$this->Execute("alter table netports change connectortype connectortype int(5) unsigned not null default 0");
$this->Execute("alter table netports change technology technology int(11) default null");

$this->Execute("UPDATE dbinfo SET keyvalue = ? WHERE keytype = ?", array('2016042701', 'dbversion'));

$this->CommitTrans();


