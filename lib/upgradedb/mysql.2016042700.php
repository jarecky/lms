<?php


$this->BeginTrans();

$this->Execute("alter table netports change type type tinyint(4) unsigned not null default 0");
$this->Execute("alter table netports change connectortype connectortype tinyint(4) unsigned not null default 0");

$this->Execute("UPDATE dbinfo SET keyvalue = ? WHERE keytype = ?", array('2016042700', 'dbversion'));

$this->CommitTrans();


