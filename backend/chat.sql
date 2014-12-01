-- phpMyAdmin SQL Dump
-- version 4.0.4
-- http://www.phpmyadmin.net
--
-- 主机: localhost
-- 生成日期: 2014 年 12 月 01 日 02:05
-- 服务器版本: 5.6.12-log
-- PHP 版本: 5.4.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- 数据库: `chat`
--
CREATE DATABASE IF NOT EXISTS `chat` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `chat`;

-- --------------------------------------------------------

--
-- 表的结构 `message`
--

CREATE TABLE IF NOT EXISTS `message` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `from_user` int(11) NOT NULL,
  `time` varchar(20) CHARACTER SET utf8 NOT NULL,
  `msg` text NOT NULL,
  `is_read` tinyint(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fromUser` (`from_user`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `user`
--

CREATE TABLE IF NOT EXISTS `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uname` varchar(20) CHARACTER SET utf8 NOT NULL,
  `password` varchar(40) CHARACTER SET utf8 NOT NULL,
  `nickname` varchar(20) CHARACTER SET utf8 NOT NULL,
  `email` varchar(40) CHARACTER SET utf8 NOT NULL,
  `photo` varchar(100) CHARACTER SET utf8 NOT NULL DEFAULT './picture/default.png',
  `platform` varchar(10) CHARACTER SET utf8 NOT NULL DEFAULT 'none',
  `is_login` tinyint(2) NOT NULL DEFAULT '0',
  `last_login` varchar(20) CHARACTER SET utf8 NOT NULL,
  `ip` varchar(15) CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=75 ;

--
-- 转存表中的数据 `user`
--

INSERT INTO `user` (`id`, `uname`, `password`, `nickname`, `email`, `photo`, `platform`, `is_login`, `last_login`, `ip`) VALUES
(70, 'Dddd', '1245667', '', 'Njhhhgft', './picture/default.png', 'none', 0, '', ''),
(71, 'test1', '123456', '', 'a1098035@163.com', './picture/default.png', 'none', 0, '', ''),
(72, 'å­¤ç‹¬çš„è‡ªç”±', '', '', '', './picture/14163971736.png', 'QQ', 0, '', ''),
(73, 'Al', '', '', '', './picture/14163972304.png', 'QQ', 0, '', ''),
(74, 'xwy', '12330355', '', '729367450@qq.com', './picture/default.png', 'none', 0, '', '');

--
-- 限制导出的表
--

--
-- 限制表 `message`
--
ALTER TABLE `message`
  ADD CONSTRAINT `message_ibfk_1` FOREIGN KEY (`from_user`) REFERENCES `user` (`id`) ON DELETE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
