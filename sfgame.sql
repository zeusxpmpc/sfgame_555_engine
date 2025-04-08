-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 19, 2025 at 01:21 PM
-- Wersja serwera: 10.4.32-MariaDB
-- Wersja PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sfgame`
--

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `acp`
--

CREATE TABLE `acp` (
  `id` int(11) NOT NULL,
  `login` varchar(50) NOT NULL,
  `pass` varchar(50) NOT NULL,
  `ssid` varchar(100) NOT NULL DEFAULT '0',
  `rank` int(11) NOT NULL DEFAULT 1,
  `lastactiv` int(11) NOT NULL DEFAULT 0,
  `lastlogin` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `guilds`
--

CREATE TABLE `guilds` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `ownerid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `items_fidget`
--

CREATE TABLE `items_fidget` (
  `id` int(11) NOT NULL,
  `itemid` smallint(6) NOT NULL DEFAULT 0,
  `itemtype` tinyint(4) NOT NULL DEFAULT 0,
  `itemclass` smallint(6) NOT NULL DEFAULT 0,
  `dmgmin` mediumint(9) NOT NULL DEFAULT 0,
  `dmgmax` smallint(6) NOT NULL DEFAULT 0,
  `attrtype1` tinyint(4) NOT NULL DEFAULT 0,
  `attrtype2` tinyint(4) NOT NULL DEFAULT 0,
  `attrtype3` tinyint(4) NOT NULL DEFAULT 0,
  `attrvalue1` int(11) NOT NULL DEFAULT 0,
  `attrvalue2` int(11) NOT NULL DEFAULT 0,
  `attrvalue3` int(11) NOT NULL DEFAULT 0,
  `silver` int(11) NOT NULL DEFAULT 0,
  `mush` int(11) NOT NULL DEFAULT 0,
  `enchantid` int(11) NOT NULL DEFAULT 0,
  `enchantvalue` int(11) NOT NULL DEFAULT 0,
  `toilet` bit(1) NOT NULL DEFAULT b'0',
  `slotid` tinyint(4) NOT NULL DEFAULT 0,
  `ownerid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `items_players`
--

CREATE TABLE `items_players` (
  `id` int(11) NOT NULL,
  `itemid` int(11) NOT NULL DEFAULT 0,
  `itemtype` tinyint(4) NOT NULL DEFAULT 0,
  `itemclass` smallint(6) NOT NULL DEFAULT 0,
  `dmgmin` mediumint(9) NOT NULL DEFAULT 0,
  `dmgmax` smallint(6) NOT NULL DEFAULT 0,
  `attrtype1` tinyint(4) NOT NULL DEFAULT 0,
  `attrtype2` tinyint(4) NOT NULL DEFAULT 0,
  `attrtype3` tinyint(4) NOT NULL DEFAULT 0,
  `attrvalue1` int(11) NOT NULL DEFAULT 0,
  `attrvalue2` int(11) NOT NULL DEFAULT 0,
  `attrvalue3` int(11) NOT NULL DEFAULT 0,
  `silver` int(11) NOT NULL DEFAULT 0,
  `mush` int(11) NOT NULL DEFAULT 0,
  `enchantid` int(11) NOT NULL DEFAULT 0,
  `enchantvalue` int(11) NOT NULL DEFAULT 0,
  `toilet` bit(1) NOT NULL DEFAULT b'0',
  `slotid` tinyint(4) NOT NULL DEFAULT 0,
  `ownerid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `items_quests`
--

CREATE TABLE `items_quests` (
  `id` int(11) NOT NULL,
  `itemid` smallint(6) NOT NULL DEFAULT 0,
  `itemtype` tinyint(4) NOT NULL DEFAULT 0,
  `itemclass` smallint(6) NOT NULL DEFAULT 0,
  `dmgmin` mediumint(9) NOT NULL DEFAULT 0,
  `dmgmax` smallint(6) NOT NULL DEFAULT 0,
  `attrtype1` tinyint(4) NOT NULL DEFAULT 0,
  `attrtype2` tinyint(4) NOT NULL DEFAULT 0,
  `attrtype3` tinyint(4) NOT NULL DEFAULT 0,
  `attrvalue1` int(11) NOT NULL DEFAULT 0,
  `attrvalue2` int(11) NOT NULL DEFAULT 0,
  `attrvalue3` int(11) NOT NULL DEFAULT 0,
  `silver` int(11) NOT NULL DEFAULT 0,
  `mush` int(11) NOT NULL DEFAULT 0,
  `enchantid` int(11) NOT NULL DEFAULT 0,
  `enchantvalue` int(11) NOT NULL DEFAULT 0,
  `toilet` bit(1) NOT NULL DEFAULT b'0',
  `slotid` tinyint(4) NOT NULL DEFAULT 0,
  `ownerid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `items_shakes`
--

CREATE TABLE `items_shakes` (
  `id` int(11) NOT NULL,
  `itemid` smallint(6) NOT NULL DEFAULT 0,
  `itemtype` tinyint(4) NOT NULL DEFAULT 0,
  `itemclass` smallint(6) NOT NULL DEFAULT 0,
  `dmgmin` mediumint(9) NOT NULL DEFAULT 0,
  `dmgmax` smallint(6) NOT NULL DEFAULT 0,
  `attrtype1` tinyint(4) NOT NULL DEFAULT 0,
  `attrtype2` tinyint(4) NOT NULL DEFAULT 0,
  `attrtype3` tinyint(4) NOT NULL DEFAULT 0,
  `attrvalue1` int(11) NOT NULL DEFAULT 0,
  `attrvalue2` int(11) NOT NULL DEFAULT 0,
  `attrvalue3` int(11) NOT NULL DEFAULT 0,
  `silver` int(11) NOT NULL DEFAULT 0,
  `mush` int(11) NOT NULL DEFAULT 0,
  `enchantid` int(11) NOT NULL DEFAULT 0,
  `enchantvalue` int(11) NOT NULL DEFAULT 0,
  `toilet` bit(1) NOT NULL DEFAULT b'0',
  `slotid` tinyint(4) NOT NULL DEFAULT 0,
  `ownerid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `players`
--

CREATE TABLE `players` (
  `id` int(11) NOT NULL,
  `name` varchar(12) NOT NULL,
  `pass` varchar(32) NOT NULL,
  `email` varchar(30) NOT NULL,
  `emailconfirm` bit(1) NOT NULL DEFAULT b'0',
  `regdate` int(11) NOT NULL DEFAULT 0,
  `ssid` tinytext NOT NULL,
  `sessiontime` int(11) NOT NULL DEFAULT 0,
  `lastip` varchar(15) NOT NULL,
  `lastonline` int(10) NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT 1,
  `lvl` smallint(6) NOT NULL DEFAULT 1,
  `pexp` int(11) NOT NULL DEFAULT 0,
  `pdesc` varchar(238) NOT NULL,
  `honor` int(11) NOT NULL DEFAULT 100,
  `rank` int(11) NOT NULL DEFAULT 0,
  `rankclass` int(11) NOT NULL DEFAULT 0,
  `raceid` tinyint(4) NOT NULL,
  `sexid` tinyint(4) NOT NULL,
  `classid` tinyint(4) NOT NULL,
  `silver` int(11) NOT NULL DEFAULT 100,
  `mush` smallint(6) NOT NULL DEFAULT 15,
  `mushbuy` mediumint(9) NOT NULL DEFAULT 0,
  `face` varchar(100) NOT NULL,
  `statusid` tinyint(1) NOT NULL DEFAULT 0,
  `statusextra` tinyint(4) NOT NULL DEFAULT 0,
  `statusend` int(11) NOT NULL DEFAULT 0,
  `workendsilver` int(11) NOT NULL DEFAULT 1,
  `questrerolltime` int(11) NOT NULL DEFAULT 0,
  `shoprerolltime` int(11) NOT NULL DEFAULT 0,
  `dungeontime` int(11) NOT NULL DEFAULT 0,
  `arenatime` int(11) NOT NULL DEFAULT 0,
  `firstquesttime` int(11) NOT NULL DEFAULT 0,
  `thirst` smallint(6) NOT NULL DEFAULT 6000,
  `beers` tinyint(4) NOT NULL DEFAULT 0,
  `questlvl` varchar(10) NOT NULL,
  `questtype` varchar(10) NOT NULL,
  `questenemy` varchar(20) NOT NULL,
  `questlocation` varchar(10) NOT NULL,
  `questtime` varchar(50) NOT NULL,
  `questexp` varchar(50) NOT NULL,
  `questsilver` varchar(50) NOT NULL,
  `attrstr` mediumint(9) NOT NULL DEFAULT 0,
  `attrdex` mediumint(9) NOT NULL DEFAULT 0,
  `attrint` mediumint(9) NOT NULL DEFAULT 0,
  `attrwit` mediumint(9) NOT NULL DEFAULT 0,
  `attrluck` mediumint(9) NOT NULL DEFAULT 0,
  `attrstrbuy` smallint(6) NOT NULL DEFAULT 0,
  `attrdexbuy` smallint(6) NOT NULL DEFAULT 0,
  `attrintbuy` smallint(6) NOT NULL DEFAULT 0,
  `attrwitbuy` smallint(6) NOT NULL DEFAULT 0,
  `attrluckbuy` smallint(6) NOT NULL DEFAULT 0,
  `potionid1` tinyint(4) NOT NULL DEFAULT 0,
  `potionid2` tinyint(4) NOT NULL DEFAULT 0,
  `potionid3` tinyint(4) NOT NULL DEFAULT 0,
  `potiontime1` int(11) NOT NULL DEFAULT 0,
  `potiontime2` int(11) NOT NULL DEFAULT 0,
  `potiontime3` int(11) NOT NULL DEFAULT 0,
  `mountid` tinyint(4) NOT NULL DEFAULT 0,
  `mounttime` int(11) NOT NULL DEFAULT 0,
  `towerlvl` tinyint(4) NOT NULL DEFAULT 1,
  `pvpwin` int(11) NOT NULL DEFAULT 0,
  `questscount` int(11) NOT NULL DEFAULT 0,
  `workhours` int(11) NOT NULL DEFAULT 0,
  `workgold` int(11) NOT NULL DEFAULT 0,
  `guildid` int(11) NOT NULL DEFAULT 0,
  `d1` tinyint(4) NOT NULL DEFAULT 0,
  `d2` tinyint(4) NOT NULL DEFAULT 0,
  `d3` tinyint(4) NOT NULL DEFAULT 0,
  `d4` tinyint(4) NOT NULL DEFAULT 0,
  `d5` tinyint(4) NOT NULL DEFAULT 0,
  `d6` tinyint(4) NOT NULL DEFAULT 0,
  `d7` tinyint(4) NOT NULL DEFAULT 0,
  `d8` tinyint(4) NOT NULL DEFAULT 0,
  `d9` tinyint(4) NOT NULL DEFAULT 0,
  `d10` tinyint(4) NOT NULL DEFAULT 0,
  `d11` tinyint(4) NOT NULL DEFAULT 0,
  `d12` tinyint(4) NOT NULL DEFAULT 0,
  `d13` tinyint(4) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Indeksy dla zrzutów tabel
--

--
-- Indeksy dla tabeli `acp`
--
ALTER TABLE `acp`
  ADD PRIMARY KEY (`id`);

--
-- Indeksy dla tabeli `guilds`
--
ALTER TABLE `guilds`
  ADD PRIMARY KEY (`id`);

--
-- Indeksy dla tabeli `items_fidget`
--
ALTER TABLE `items_fidget`
  ADD PRIMARY KEY (`id`);

--
-- Indeksy dla tabeli `items_players`
--
ALTER TABLE `items_players`
  ADD PRIMARY KEY (`id`);

--
-- Indeksy dla tabeli `items_quests`
--
ALTER TABLE `items_quests`
  ADD PRIMARY KEY (`id`);

--
-- Indeksy dla tabeli `items_shakes`
--
ALTER TABLE `items_shakes`
  ADD PRIMARY KEY (`id`);

--
-- Indeksy dla tabeli `players`
--
ALTER TABLE `players`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `acp`
--
ALTER TABLE `acp`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `guilds`
--
ALTER TABLE `guilds`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `items_fidget`
--
ALTER TABLE `items_fidget`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `items_players`
--
ALTER TABLE `items_players`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `items_quests`
--
ALTER TABLE `items_quests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `items_shakes`
--
ALTER TABLE `items_shakes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `players`
--
ALTER TABLE `players`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
