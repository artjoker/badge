-- Adminer 4.2.4 MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `config`;
CREATE TABLE `config` (
  `key` varchar(32) NOT NULL,
  `value` text NOT NULL,
  UNIQUE KEY `key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `config` (`key`, `value`) VALUES
('ALARM',	'userko@mail.ua'),
('API_PUSH_KEY',	''),
('BRAND',	'Artjoker'),
('CURRENCY',	'CURRENCY'),
('EMAIL_BODY_ORDER',	'<p>EMAIL_BODY_ORDER</p>'),
('EMAIL_BODY_ORDER_CHANGE',	'<p>EMAIL_BODY_ORDER_CHANGE</p>'),
('EMAIL_BODY_ORDER_ITEM',	'<p>EMAIL_BODY_ORDER_ITEM</p>'),
('EMAIL_BODY_RECOVERY',	'<p>EMAIL_BODY_RECOVERY</p>'),
('EMAIL_BODY_REG',	'<p>EMAIL_BODY_REG</p>'),
('EMAIL_SUBJECT_ORDER',	'EMAIL_SUBJECT_ORDER'),
('EMAIL_SUBJECT_ORDER_CHANGE',	'EMAIL_SUBJECT_ORDER_CHANGE'),
('EMAIL_SUBJECT_RECOVERY',	'EMAIL_SUBJECT_RECOVERY'),
('EMAIL_SUBJECT_REG',	'EMAIL_SUBJECT_REG'),
('GMAP_KEY',	'GMAP_KEY'),
('LANG',	'ru'),
('LIMIT',	'10'),
('MAIL_HOST',	'mail.artjoker.ua'),
('MAIL_PASS',	''),
('MAIL_PORT',	'587'),
('MAIL_SECURE',	''),
('MAIL_USER',	'art.digital@artjoker.ua');

DROP TABLE IF EXISTS `devices`;
CREATE TABLE `devices` (
  `device_user` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID пользователя',
  `device_id` varchar(128) NOT NULL COMMENT 'device_id',
  `gcm_id` varchar(128) NOT NULL COMMENT 'gcm',
  `apns_id` varchar(255) NOT NULL COMMENT 'apns',
  UNIQUE KEY `device_user_device_id` (`device_user`,`device_id`),
  CONSTRAINT `devices_ibfk_1` FOREIGN KEY (`device_user`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `managers`;
CREATE TABLE `managers` (
  `manager_id` int(11) NOT NULL AUTO_INCREMENT,
  `manager_name` varchar(128) NOT NULL,
  `manager_email` varchar(128) NOT NULL,
  `manager_pass` varchar(32) NOT NULL,
  `manager_active` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`manager_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `managers` (`manager_id`, `manager_name`, `manager_email`, `manager_pass`, `manager_active`) VALUES
(1,	'Владимир',	'vovko@artjoker.ua',	'782624e4fe4453b71d62932b6527c6c3',	1);

DROP TABLE IF EXISTS `session`;
CREATE TABLE `session` (
  `sess_id` char(32) NOT NULL,
  `user_id` int(11) NOT NULL,
  `dt` timestamp NOT NULL ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `session_key` (`sess_id`),
  UNIQUE KEY `user_id` (`user_id`),
  CONSTRAINT `session_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `timetable`;
CREATE TABLE `timetable` (
  `user_id` int(11) NOT NULL,
  `day` date NOT NULL,
  `time` time NOT NULL,
  KEY `user_id` (`user_id`),
  CONSTRAINT `timetable_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_name` varchar(128) NOT NULL,
  `user_pass` varchar(128) NOT NULL,
  `user_email` varchar(64) NOT NULL,
  `user_dob` date NOT NULL,
  `user_active` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `users` (`user_id`, `user_name`, `user_pass`, `user_email`, `user_dob`, `user_active`) VALUES
(1,	'Владимир Ланцев',	'0d7d9d91317e107009754a2b06c8ef32b6a7337f70dae30db95f4c9d32a950e5848d9a82aba4c96121489cbbe9fc9bb8c77c67387cf5dabad6628745b9bf1ae4',	'vovko@artjoker.ua',	'1987-04-09',	1),
(2,	'Екатерина Девяткина',	'',	'katya@artjoker.ua',	'0000-00-00',	1),
(3,	'Наталья Бринза',	'',	'natasha@artjoker.ua',	'0000-00-00',	1),
(4,	'Виктор Жигунов',	'',	'vitya@artjoker.ua',	'0000-00-00',	1),
(5,	'Mike',	'0d7d9d91317e107009754a2b06c8ef32b6a7337f70dae30db95f4c9d32a950e5848d9a82aba4c96121489cbbe9fc9bb8c77c67387cf5dabad6628745b9bf1ae4',	'mail@mail.ua',	'1987-11-12',	0);

-- 2016-03-20 16:20:18
