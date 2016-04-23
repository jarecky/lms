<?php


$this->BeginTrans();

$this->Execute("ALTER TABLE netlinks RENAME netlinks_old");

$this->Execute("CREATE TABLE `netlinks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `srcport` int(11) UNSIGNED ZEROFILL NOT NULL DEFAULT '0',
  `dstport` int(11) UNSIGNED ZEROFILL DEFAULT '0',
  `type` tinyint(1) NOT NULL DEFAULT '0',
  `speed` int(11) NOT NULL DEFAULT '100000',
  `technology` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `port` (`srcport`,`dstport`),
  CONSTRAINT `netlinks_ibfk_1` FOREIGN KEY (`srcport`) REFERENCES `netports` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `netlinks_ibfk_2` FOREIGN KEY (`dstport`) REFERENCES `netports` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci");

$this->Execute("ALTER TABLE nodes ADD netport int(11) UNSIGNED ZEROFILL NOT NULL DEFAULT '0' AFTER netdev");

$this->Execute("UPDATE dbinfo SET keyvalue = ? WHERE keytype = ?", array('2016042300', 'dbversion'));

$this->CommitTrans();


