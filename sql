-- phpMyAdmin SQL Dump
-- version 3.5.1
-- http://www.phpmyadmin.net
--
-- Darbinė stotis: localhost
-- Atlikimo laikas: 2013 m. Kov 13 d. 13:48
-- Serverio versija: 5.5.24-log
-- PHP versija: 5.3.13

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Duomenų bazė: `anecdote_test`
--
CREATE DATABASE `anecdote_test` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `anecdote_test`;

-- --------------------------------------------------------

--
-- Sukurta duomenų struktūra lentelei `anecdote_characters`
--

CREATE TABLE IF NOT EXISTS `anecdote_characters` (
  `anecdote_id` int(11) NOT NULL,
  `character_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Sukurta duomenų kopija lentelei `anecdote_characters`
--

INSERT INTO `anecdote_characters` (`anecdote_id`, `character_id`) VALUES
(24, 1),
(24, 2),
(36, 1),
(36, 2),
(37, 1),
(37, 2),
(38, 3),
(38, 4),
(39, 5),
(39, 4),
(40, 6);

-- --------------------------------------------------------

--
-- Sukurta duomenų struktūra lentelei `anecdotes`
--

CREATE TABLE IF NOT EXISTS `anecdotes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `topic_id` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `contents` text,
  `language` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=41 ;

--
-- Sukurta duomenų kopija lentelei `anecdotes`
--

INSERT INTO `anecdotes` (`id`, `topic_id`, `title`, `contents`, `language`) VALUES
(3, NULL, 'Pakeliui', 'Ejo eziukas misku ir sutiko lape.\r\nLape jam sako: -Eziuk, kur eini?\r\nEziukas: -I vaistine einu!\r\nLape: -As irgi!\r\nEziukas: -Tai einam dviese!\r\nLape: -Einam dviese!', 'ru'),
(4, NULL, 'Pakeliui', 'Ejo eziukas misku ir sutiko lape.\r\nLape jam sako: -Eziuk, kur eini?\r\nEziukas: -I vaistine einu!\r\nLape: -As irgi!\r\nEziukas: -Tai einam dviese!\r\nLape: -Einam dviese!', 'de'),
(25, NULL, 'Apie eziuka', 'Ejo eziukas misku ir sutiko lape.\r\nLape jam sako: -Eziuk, kur eini?\r\nEziukas: -I vaistine einu!\r\nLape: -As irgi!\r\nEziukas: -Tai einam dviese!\r\nLape: -Einam dviese!', 'lt'),
(37, 1, 'Pakeliui', 'Ejo eziukas misku ij sutiko jape.\r\nJape jam sako: -Eziuk, kuj eini?\r\nEziukas: -I vaistine einu!\r\nJape: -As ijgi!\r\nEziukas: -Tai einam dviese!\r\nJape: -Einam dviese!', 'lt'),
(39, 2, 'Grietinė', 'Žmona važiuoja į komandiruotę. Paklausė kaip žinoti ar vyras "neina į kairę". Draugė patarė po lova padėti grietinės stiklainį, prie lovos virš stiklainio pririšti šaukštą.\r\nTaip ir padarė.\r\nPo trijų dienų grįžta žmona ir mato...\r\nStiklainyje - sviestas!', 'lt'),
(40, 3, 'Geležis iš meteoritų', 'Čeliabinsko kalnakasiai tokie rūstūs, kad net geležį  kasa iš kosmoso.', 'lt');

-- --------------------------------------------------------

--
-- Sukurta duomenų struktūra lentelei `characters`
--

CREATE TABLE IF NOT EXISTS `characters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `language` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

--
-- Sukurta duomenų kopija lentelei `characters`
--

INSERT INTO `characters` (`id`, `name`, `language`) VALUES
(1, 'Eziukas', 'lt'),
(2, 'Lape', 'lt'),
(4, 'vyras', 'lt'),
(5, 'Žmona', 'lt'),
(6, 'Kalnakasiai', 'lt');

-- --------------------------------------------------------

--
-- Sukurta duomenų struktūra lentelei `topics`
--

CREATE TABLE IF NOT EXISTS `topics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `language` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

--
-- Sukurta duomenų kopija lentelei `topics`
--

INSERT INTO `topics` (`id`, `name`, `language`) VALUES
(1, 'Gyvūnai', 'lt'),
(2, 'Šeima', 'lt'),
(3, 'Darbas', 'lt');
--
-- Duomenų bazė: `anecdotes`
--
CREATE DATABASE `anecdotes` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `anecdotes`;

-- --------------------------------------------------------

--
-- Sukurta duomenų struktūra lentelei `anecdote_characters`
--

CREATE TABLE IF NOT EXISTS `anecdote_characters` (
  `anecdote_id` int(11) NOT NULL,
  `character_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Sukurta duomenų kopija lentelei `anecdote_characters`
--

INSERT INTO `anecdote_characters` (`anecdote_id`, `character_id`) VALUES
(40, 6),
(25, 1),
(25, 2),
(39, 4),
(39, 5),
(41, 7),
(41, 8),
(46, 15),
(49, 1),
(52, 1),
(52, 2),
(3, 19),
(3, 18);

-- --------------------------------------------------------

--
-- Sukurta duomenų struktūra lentelei `anecdotes`
--

CREATE TABLE IF NOT EXISTS `anecdotes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `topic_id` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `contents` text,
  `language` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=54 ;

--
-- Sukurta duomenų kopija lentelei `anecdotes`
--

INSERT INTO `anecdotes` (`id`, `topic_id`, `title`, `contents`, `language`) VALUES
(3, 17, 'По пути', 'Шел ёжик по лесу и встретил лису.\r\nЛиса говорит: - Ёжик, куда идешь?\r\nЁжик: - Иду в аптеку!\r\nЛиса: И я тоже!\r\nЁжик: Так пошли вместе!\r\nЛиса: - Пошли вместе!', 'ru'),
(25, 1, 'Pakeliui', 'Ėjo ežiukas mišku ir sutiko lapę.\r\nLapė jam sako: -Ežiuk, kur eini?\r\nEžiukas: -Į vaistinę einu!\r\nLape: -Aš irgi!\r\nEžiukas: -Tai einam dviese!\r\nLape: -Einam dviese!', 'lt'),
(39, 2, 'Grietinė', 'Žmona važiuoja į komandiruotę. Paklausė kaip žinoti ar vyras "neina į kairę". Draugė patarė po lova padėti grietinės stiklainį, prie lovos virš stiklainio pririšti šaukštą.\r\nTaip ir padarė.\r\nPo trijų dienų grįžta žmona ir mato...\r\nStiklainyje - sviestas!', 'lt'),
(40, 3, 'Geležis iš meteoritų', 'Čeliabinsko kalnakasiai tokie rūstūs, kad net geležį  kasa iš kosmoso.', 'lt'),
(41, 4, 'Dešrelių mėgėjas', 'Ateina žmogelis pas daktarą ir sako: - Daktare, mane visi laiko išprotėjusiu todėl, kad mėgstu dešreles.\r\n- Ir kas čia tokio? Aš irgi mėgstu dešreles.\r\n- Tikrai?!! Tada, eime, parodysiu jums savo dešrelių kolekciją.\r\n', 'lt'),
(46, 9, 'Klizma', 'Vyksta egzaminas medicinos fakultete. Pro auditorijos duris išeina liūdnas studentas. Prie jo prišoka studentai ir klausia: - Na, ar išlaikei?\r\nStudentas: - Neišlaikiau.\r\nStudentai: - Kodėl neišlaikei, kokio klausimo nežinojai?\r\nStudentas: - Dėstytojas paklausė ko reikia norint padaryti klizmą? Aš atsakiau reikia turėti užpakalį.', 'lt'),
(49, 1, 'Skylėtos kojinės', 'Atėjo ežiukas pas zuikį ir sako:\r\n- Sveikas! Prašau, paskolink juodą flomasterį.\r\n- O kam tau?\r\n- Einu į svečius, nusidažysiu pirštą, nes kojinės skylėtos.', 'lt'),
(53, 0, 'a', 'a', 'de');

-- --------------------------------------------------------

--
-- Sukurta duomenų struktūra lentelei `characters`
--

CREATE TABLE IF NOT EXISTS `characters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `language` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=20 ;

--
-- Sukurta duomenų kopija lentelei `characters`
--

INSERT INTO `characters` (`id`, `name`, `language`) VALUES
(1, 'Ežiukas', 'lt'),
(2, 'Lapė', 'lt'),
(4, 'Vyras', 'lt'),
(5, 'Žmona', 'lt'),
(6, 'Kalnakasiai', 'lt'),
(7, 'Daktaras', 'lt'),
(8, 'Pamišelis', 'lt'),
(15, 'Studentas', 'lt'),
(18, 'Лиса', 'ru'),
(19, 'Ёжик', 'ru');

-- --------------------------------------------------------

--
-- Sukurta duomenų struktūra lentelei `topics`
--

CREATE TABLE IF NOT EXISTS `topics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `language` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=18 ;

--
-- Sukurta duomenų kopija lentelei `topics`
--

INSERT INTO `topics` (`id`, `name`, `language`) VALUES
(1, 'Gyvūnai', 'lt'),
(2, 'Šeima', 'lt'),
(3, 'Darbas', 'lt'),
(4, 'Daktarai', 'lt'),
(9, 'Studijos', 'lt'),
(11, 'Draugai', 'lt'),
(17, 'Животные', 'ru');

-- --------------------------------------------------------

--
-- Sukurta duomenų struktūra lentelei `user_preferred_characters`
--

CREATE TABLE IF NOT EXISTS `user_preferred_characters` (
  `user_id` int(11) NOT NULL,
  `character_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Sukurta duomenų kopija lentelei `user_preferred_characters`
--

INSERT INTO `user_preferred_characters` (`user_id`, `character_id`) VALUES
(1, 19),
(1, 18);

-- --------------------------------------------------------

--
-- Sukurta duomenų struktūra lentelei `user_preferred_topics`
--

CREATE TABLE IF NOT EXISTS `user_preferred_topics` (
  `user_id` int(11) NOT NULL,
  `topic_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Sukurta duomenų struktūra lentelei `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `login` varchar(255) DEFAULT NULL,
  `phash` varchar(32) DEFAULT NULL,
  `sid` varchar(32) DEFAULT NULL,
  `bgcolor` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `aid` varchar(32) DEFAULT NULL,
  `active` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Sukurta duomenų kopija lentelei `users`
--

INSERT INTO `users` (`id`, `login`, `phash`, `sid`, `bgcolor`, `email`, `aid`, `active`) VALUES
(1, 'demo', '6c5ac7b4d3bd3311f033f971196cfa75', 'mqqq159mjnc1vcib9hgl88em81', NULL, 'demo@example.com', '', 1);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
