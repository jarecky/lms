<?php
$this->BeginTrans();

$this->Execute("alter table netwires change type type int(3) unsigned not null default 1");

$this->Execute("UPDATE dbinfo SET keyvalue = ? WHERE keytype = ?", array('2016042800', 'dbversion'));

$this->CommitTrans();


