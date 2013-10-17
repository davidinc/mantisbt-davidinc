CREATE TABLE IF NOT EXISTS `mantis_TableField_item_list` (
  `id` int(11) NOT NULL,
  `field_id` int(11) NOT NULL,
  `bug_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `supplier` varchar(255) NOT NULL,
  `quantity` float NOT NULL,
  `unit_price` float NOT NULL,
  `currency` varchar(3) NOT NULL,
  `unit` varchar(10) NOT NULL,
  PRIMARY KEY  (`id`,`field_id`,`bug_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

