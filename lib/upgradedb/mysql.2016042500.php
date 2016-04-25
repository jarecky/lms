<?php


$this->BeginTrans();

$this->Execute("ALTER TABLE netlinks ADD node int(11) DEFAULT NULL AFTER dstport");
$this->Execute("ALTER TABLE netlinks CHANGE dstport dstport int(11) UNSIGNED ZEROFILL DEFAULT NULL");
$this->Execute("ALTER TABLE netlinks ADD UNIQUE KEY links (srcport,dstport,node)");
$this->Execute("ALTER TABLE netlinks DROP KEY port");
$this->Execute("ALTER TABLE netlinks ADD CONSTRAINT `netlinks_ibfk_3` FOREIGN KEY (`node`) REFERENCES `nodes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE");

$this->Execute("ALTER TABLE nodes DROP netport");
$this->Execute("ALTER TABLE nodes DROP FOREIGN KEY nodes_ibfk_5");
$this->Execute("ALTER TABLE nodes ADD netlink int(11) DEFAULT NULL AFTER netdev");
$this->Execute("ALTER TABLE nodes ADD CONSTRAINT `nodes_ibfk_5` FOREIGN KEY (`netlink`) REFERENCES `netlinks` (`id`) ON DELETE SET NULL ON UPDATE CASCADE");


$this->Execute("CREATE TABLE `netlinkassingments` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`connection` int(11) NOT NULL DEFAULT 0,	
	`link` int(11) NOT NULL DEFAULT 0,	
	PRIMARY KEY (`id`),
	KEY `netlinks_ibfk_1` (`connection`),
	KEY `netlinks_ibfk_2` (`link`),
	CONSTRAINT `netlinkassingments_ibfk_1` FOREIGN KEY (`connection`) REFERENCES `netconnections` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT `netlinkassingments_ibfk_2` FOREIGN KEY (`link`) REFERENCES `netlinks` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci");

$this->Execute("UPDATE dbinfo SET keyvalue = ? WHERE keytype = ?", array('2016042500', 'dbversion'));

$this->CommitTrans();


