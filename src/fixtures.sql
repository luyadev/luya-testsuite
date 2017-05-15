DROP TABLE IF EXISTS `dummy_fixture` CASCADE;


CREATE TABLE `dummy_fixture` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `string` varchar(250) NOT NULL,
  `integer` int(11) DEFAULT '0',
  `float` float(2) NOT NULL,
  `boolean` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
