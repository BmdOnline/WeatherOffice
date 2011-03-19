-- phpMyAdmin SQL Dump
-- version 3.3.2deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 19. März 2011 um 12:57
-- Server Version: 5.1.41
-- PHP-Version: 5.3.2-1ubuntu4.7

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Datenbank: `open2300`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `additionalsensors`
--

CREATE TABLE IF NOT EXISTS `additionalsensors` (
  `id` int(11) NOT NULL,
  `name` varchar(30) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `linenumber` int(11) NOT NULL,
  `unit` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
