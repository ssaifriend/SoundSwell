-- phpMyAdmin SQL Dump
-- version 3.4.7.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Oct 23, 2014 at 09:49 AM
-- Server version: 5.1.53
-- PHP Version: 5.2.17

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `soundswell`
--

-- --------------------------------------------------------

--
-- Table structure for table `article`
--

CREATE TABLE IF NOT EXISTS `article` (
  `nSeqNo` int(11) NOT NULL AUTO_INCREMENT,
  `vcBoard` varchar(50) NOT NULL,
  `vcTitle` varchar(255) NOT NULL,
  `tContents` text NOT NULL,
  `nUserNo` int(11) NOT NULL,
  `vcNickname` varchar(100) NOT NULL,
  `nHit` int(11) NOT NULL,
  `dtRegdate` datetime NOT NULL,
  `emDel` enum('Y','N') NOT NULL DEFAULT 'N',
  `emNotice` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`nSeqNo`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `article_comment`
--

CREATE TABLE IF NOT EXISTS `article_comment` (
  `nSeqNo` int(11) NOT NULL AUTO_INCREMENT,
  `nArticleNo` int(11) NOT NULL,
  `vcContents` varchar(255) NOT NULL,
  `nUserNo` int(11) NOT NULL,
  `vcNickname` varchar(100) NOT NULL,
  `dtRegdate` datetime NOT NULL,
  `emDel` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`nSeqNo`),
  KEY `nArticleNo` (`nArticleNo`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `member`
--

CREATE TABLE IF NOT EXISTS `member` (
  `nSeqNo` int(11) NOT NULL AUTO_INCREMENT,
  `vcUserId` varchar(30) NOT NULL,
  `cPassword` char(32) NOT NULL,
  `vcNickname` varchar(30) NOT NULL,
  `vcEmail` varchar(50) NOT NULL,
  `dtRegdate` datetime NOT NULL,
  `emAdmin` enum('Admin','') NOT NULL DEFAULT '',
  `emDel` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`nSeqNo`),
  UNIQUE KEY `vcUserId` (`vcUserId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `option`
--

CREATE TABLE IF NOT EXISTS `option` (
  `nUserNo` int(11) NOT NULL,
  `vcKey` varchar(15) NOT NULL,
  `vcValue` varchar(100) NOT NULL,
  UNIQUE KEY `nUserNo` (`nUserNo`,`vcKey`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `randomTemp`
--

CREATE TABLE IF NOT EXISTS `randomTemp` (
  `hash` varchar(50) NOT NULL,
  `data` text NOT NULL,
  UNIQUE KEY `hash` (`hash`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `rank`
--

CREATE TABLE IF NOT EXISTS `rank` (
  `nSeqNo` int(11) NOT NULL AUTO_INCREMENT,
  `nUserNo` int(11) NOT NULL,
  `cBMS` char(32) NOT NULL,
  `nRecordNo` int(11) NOT NULL,
  `nScore` int(11) NOT NULL,
  `fGrade` decimal(5,2) NOT NULL,
  `emKeyType` enum('1','2','3') NOT NULL,
  `emDel` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`nSeqNo`),
  KEY `cBMS` (`cBMS`),
  KEY `nUserNo` (`nUserNo`),
  KEY `emKeyType` (`emKeyType`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `record`
--

CREATE TABLE IF NOT EXISTS `record` (
  `nSeqNo` int(11) NOT NULL AUTO_INCREMENT,
  `nUserNo` int(11) NOT NULL,
  `nSwell` int(11) NOT NULL,
  `nWell` int(11) NOT NULL,
  `nGood` int(11) NOT NULL,
  `nBad` int(11) NOT NULL,
  `nMiss` int(11) NOT NULL,
  `nBonus` int(11) NOT NULL,
  `nScore` int(11) NOT NULL,
  `dtStart` datetime NOT NULL,
  `dtEnd` datetime NOT NULL,
  `cBMS` char(32) NOT NULL,
  `fGrade` decimal(5,2) NOT NULL,
  `nBPM` smallint(6) NOT NULL,
  `emKeyType` enum('1','2','3') NOT NULL DEFAULT '2',
  `emNote` enum('N','R','M','S','H') NOT NULL DEFAULT 'N',
  `emDead` enum('Y','N') NOT NULL DEFAULT 'N',
  `emDel` enum('Y','N') NOT NULL DEFAULT 'N',
  `nLongDetailNo` int(11) NOT NULL,
  `nNormalDetailNo` int(11) NOT NULL,
  PRIMARY KEY (`nSeqNo`),
  KEY `cBMS` (`cBMS`),
  KEY `nUserNo` (`nUserNo`),
  KEY `emKeyType` (`emKeyType`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `record_detail`
--

CREATE TABLE IF NOT EXISTS `record_detail` (
  `nSeqNo` int(11) NOT NULL AUTO_INCREMENT,
  `nSwell` int(11) NOT NULL,
  `nWell` int(11) NOT NULL,
  `nGood` int(11) NOT NULL,
  `nBad` int(11) NOT NULL,
  `nMiss` int(11) NOT NULL,
  `nBonus` int(11) NOT NULL,
  `nScore` int(11) NOT NULL,
  `emType` enum('L','N') NOT NULL,
  PRIMARY KEY (`nSeqNo`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `stat_login`
--

CREATE TABLE IF NOT EXISTS `stat_login` (
  `nSeqNo` int(11) NOT NULL AUTO_INCREMENT,
  `nUserNo` int(11) NOT NULL,
  `dtRegdate` datetime NOT NULL,
  `vcIP` varchar(15) NOT NULL,
  PRIMARY KEY (`nSeqNo`),
  KEY `nUserNo` (`nUserNo`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `stat_referer`
--

CREATE TABLE IF NOT EXISTS `stat_referer` (
  `nSeqNo` int(11) NOT NULL AUTO_INCREMENT,
  `vcReferer` varchar(255) NOT NULL,
  `vcUrl` varchar(255) NOT NULL,
  `vcUA` varchar(255) NOT NULL,
  `dtRegdate` datetime NOT NULL,
  PRIMARY KEY (`nSeqNo`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
