-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Host: db2.ord.wordpress.org:3306
-- Generation Time: Sep 03, 2020 at 12:45 AM
-- Server version: 10.2.33-MariaDB-log
-- PHP Version: 7.4.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `wordpress`
--

-- --------------------------------------------------------

--
-- Table structure for table `wporg_locales`
--

CREATE TABLE `wporg_locales` (
  `locale_id` int(11) NOT NULL,
  `locale` varchar(20) NOT NULL DEFAULT '',
  `subdomain` varchar(20) NOT NULL DEFAULT '',
  `latest_release` varchar(16) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `wporg_locales`
--

INSERT INTO `wporg_locales` (`locale_id`, `locale`, `subdomain`, `latest_release`) VALUES
(172, 'bg_BG', 'bg', '5.3.2'),
(173, 'uk', 'uk', '5.5.1'),
(174, 'fr_FR', 'fr', '5.5.1'),
(175, 'tr_TR', 'tr', '5.5'),
(176, 'ja', 'ja', '5.5.1'),
(177, 'id_ID', 'id', '5.5'),
(178, 'de_DE', 'de', '5.5.1'),
(179, 'ko_KR', 'ko', '5.2.4'),
(180, 'uz_UZ', 'uz', '5.0.3'),
(181, 'fi', 'fi', '5.5.1'),
(182, 'ru_RU', 'ru', '5.5.1'),
(183, 'hr', 'hr', '5.5'),
(184, 'da_DK', 'da', '5.4.2'),
(185, 'ca', 'ca', '5.5.1'),
(186, 'sr_RS', 'sr', '5.5.1'),
(187, 'lv', 'lv', '5.4.2'),
(188, 'zh_CN', 'cn', '5.4.2'),
(189, 'pt_BR', 'br', '5.5.1'),
(190, 'pt_PT', 'pt', '5.5.1'),
(191, 'ar', 'ar', '5.5.1'),
(192, 'fa_IR', 'fa', '5.5.1'),
(193, 'et', 'et', '5.5'),
(194, 'el', 'el', '5.5'),
(195, 'ug_CN', 'ug', '4.7.2'),
(196, 'eo', 'eo', '5.5.1'),
(197, 'zh_TW', 'tw', '5.5.1'),
(198, 'es_ES', 'es', '5.5.1'),
(199, 'srd', 'srd', NULL),
(200, 'it_IT', 'it', '5.5.1'),
(201, 'he_IL', 'he', '5.5'),
(202, 'th', 'th', '5.4.2'),
(203, 'su_ID', 'su', '3.1.3'),
(204, 'hi_IN', 'hi', '4.9.7'),
(205, 'ckb', 'ku', '4.9.8'),
(206, 'sv_SE', 'sv', '5.5.1'),
(207, 'hu_HU', 'hu', '5.5'),
(208, 'ms_MY', 'ms', '4.9.8'),
(209, 'si_LK', 'si', '2.8.5'),
(210, 'eu', 'eu', '5.5.1'),
(211, 'pl_PL', 'pl', '5.5'),
(212, 'nl_NL', 'nl', '5.5.1'),
(213, 'sw', 'sw', '3.0.5'),
(214, 'vi', 'vi', '5.4.2'),
(215, 'sq', 'sq', '5.5.1'),
(216, 'cs_CZ', 'cs', '5.3.2'),
(217, 'bs_BA', 'bs', '5.4.2'),
(218, 'sk_SK', 'sk', '5.5.1'),
(219, 'kea', 'kea', NULL),
(220, 'es_CL', 'cl', '5.5.1'),
(221, 'es_VE', 've', '5.5.1'),
(222, 'es_PE', 'pe', '5.4.2'),
(223, 'ro_RO', 'ro', '5.5'),
(224, 'jv_ID', 'jv', '4.9.9'),
(225, 'cy', 'cy', '5.5'),
(226, 'fy', 'fy', '3.2.1'),
(406, 'ewe', 'ewe', NULL),
(229, 'ka_GE', 'ka', '5.4.1'),
(230, 'gl_ES', 'gl', '5.5.1'),
(231, 'mg_MG', 'mg', NULL),
(232, 'ta_LK', 'ta-lk', '3.9'),
(233, 'sl_SI', 'sl', '4.9.2'),
(234, 'mk_MK', 'mk', '5.2.3'),
(235, 'fa_AF', 'fa-af', NULL),
(236, 'my_MM', 'mya', '4.1'),
(237, 'ta_IN', 'ta', NULL),
(238, 'so_SO', 'so', NULL),
(239, 'li', 'li', NULL),
(240, 'hy', 'hy', '4.7.4'),
(241, 'me_ME', 'me', NULL),
(242, 'gu', 'gu', '4.9.8'),
(243, 'sa_IN', 'sa', NULL),
(244, 'nl_BE', 'nl-be', '5.5.1'),
(245, 'kk', 'kk', '4.9.5'),
(246, 'en_GB', 'en-gb', '5.5.1'),
(247, 'is_IS', 'is', '4.7.5'),
(248, 'te', 'te', '5.1.1'),
(249, 'gd', 'gd', '4.7.2'),
(250, 'os', 'os', '3.4.2'),
(251, 'en_CA', 'en-ca', '5.5.1'),
(252, 'dv', 'dv', NULL),
(253, 'az_TR', 'az-tr', NULL),
(254, 'ur', 'ur', '5.1.1'),
(255, 'co', 'co', NULL),
(352, 'de', 'test', NULL),
(257, 'lo', 'lo', ''),
(258, 'ne_NP', 'ne', '4.9.5'),
(259, 'ga', 'ga', ''),
(260, 'la', 'la', NULL),
(261, 'ml_IN', 'ml', '4.7.2'),
(262, 'kir', 'ky', '4.2'),
(263, 'mr', 'mr', '4.8.3'),
(264, 'tg', 'tg', NULL),
(265, 'bn_BD', 'bn', '4.8.3'),
(266, 'tl', 'tl', '4.5.2'),
(267, 'az', 'az', '4.7.2'),
(268, 'haz', 'haz', '4.4.2'),
(269, 'as', 'as', '4.8'),
(270, 'azb', 'azb', '4.7.2'),
(271, 'mri', 'mri', NULL),
(272, 'bel', 'bel', '4.9.5'),
(273, 'tuk', 'tuk', NULL),
(274, 'ory', 'ory', NULL),
(275, 'de_CH_informal', 'de_CH_informal', '5.5'),
(276, 'af', 'af', '5.4.2'),
(277, 'kn', 'kn', '4.9.10'),
(278, 'tzm', 'tzm', NULL),
(279, 'bcc', 'bcc', NULL),
(280, 'bo', 'bo', '5.5.1'),
(281, 'es_AR', 'es-ar', '5.5'),
(282, 'ps', 'ps', '4.1.2'),
(283, 'en_AU', 'en-au', '5.5'),
(284, 'km', 'km', '5.0.3'),
(285, 'fuc', 'fuc', NULL),
(286, 'lt_LT', 'lt', '5.2.4'),
(287, 'es_MX', 'es-mx', '5.5.1'),
(288, 'oci', 'oci', '4.8.3'),
(289, 'rhg', 'rhg', '4.7.2'),
(290, 'am', 'am', NULL),
(291, 'lin', 'lin', NULL),
(292, 'de_CH', 'de-ch', '5.5'),
(293, 'ido', 'ido', NULL),
(294, 'sah', 'sah', '4.7.2'),
(295, 'tt_RU', 'tt', '4.7.2'),
(296, 'pa_IN', 'pan', '5.3.4'),
(297, 'frp', 'frp', NULL),
(298, 'zh_HK', 'zh-hk', '5.4.2'),
(299, 'lb_LU', 'ltz', NULL),
(300, 'mn', 'khk', NULL),
(301, 'dzo', 'dzo', '4.7.2'),
(302, 'roh', 'roh', NULL),
(303, 'kab', 'kab', '5.5.1'),
(304, 'arq', 'arq', NULL),
(305, 'fr_BE', 'fr-be', '5.4.2'),
(306, 'fr_CA', 'fr-ca', '5.5'),
(307, 'szl', 'szl', '4.7.2'),
(308, 'de_DE_formal', 'de_DE_formal', '5.5.1'),
(309, 'bre', 'bre', NULL),
(310, 'es_CO', 'es-co', '5.5'),
(311, 'en_ZA', 'en-za', '5.5.1'),
(312, 'en_NZ', 'en-nz', '5.4.2'),
(313, 'ary', 'ary', '4.7.5'),
(314, 'fur', 'fur', NULL),
(316, 'yor', 'yor', NULL),
(317, 'ceb', 'ceb', '4.7.2'),
(355, 'art_xpirate', 'pirate', NULL),
(319, 'twd', 'twd', NULL),
(320, 'snd', 'snd', '5.3'),
(321, 'es_GT', 'es-gt', '5.1'),
(322, 'tah', 'tah', '4.7.2'),
(323, 'kal', 'kal', NULL),
(324, 'kin', 'kin', NULL),
(400, 'pap_CW', 'pap-cw', NULL),
(327, 'es_PR', 'es-pr', '5.4.1'),
(328, 'art_xemoji', 'emoji', NULL),
(329, 'hat', 'hat', NULL),
(330, 'ast', 'ast', NULL),
(331, 'fo', 'fao', NULL),
(332, 'tir', 'tir', NULL),
(333, 'hau', 'hau', NULL),
(334, 'xho', 'xho', NULL),
(335, 'es_CR', 'es-cr', '5.4.2'),
(336, 'scn', 'scn', NULL),
(337, 'sna', 'sna', NULL),
(338, 'syr', 'syr', NULL),
(339, 'sq_XK', 'sq-xk', NULL),
(342, 'nb_NO', 'nb', '5.5.1'),
(343, 'nn_NO', 'nn', '5.3.2'),
(347, 'mlt', 'mlt', NULL),
(348, 'kmr', 'kmr', NULL),
(349, 'pt_PT_ao90', 'pt_PT_ao90', '5.5.1'),
(350, 'nl_NL_formal', 'nl_NL_formal', '5.4.2'),
(351, 'kir', 'kir', NULL),
(356, 'skr', 'skr', '5.5'),
(357, 'lug', 'lug', NULL),
(397, 'de_AT', 'de-at', '5.5'),
(393, 'ca_valencia', 'ca_valencia', NULL),
(365, 'mfe', 'mfe', NULL),
(366, 'zh_SG', 'zh-sg', NULL),
(367, 'zul', 'zul', NULL),
(368, 'ssw', 'ssw', NULL),
(369, 'lmo', 'lmo', NULL),
(396, 'arg', 'arg', NULL),
(371, 'bal', 'bal', NULL),
(372, 'bho', 'bho', NULL),
(401, 'pap_AW', 'pap-aw', NULL),
(375, 'brx', 'brx', NULL),
(377, 'kaa', 'kaa', NULL),
(378, 'hsb', 'hsb', '5.5.1'),
(386, 'es_DO', 'es-do', NULL),
(385, 'es_HN', 'es-hn', NULL),
(381, 'ibo', 'ibo', NULL),
(382, 'nqo', 'nqo', NULL),
(384, 'pt_AO', 'pt-ao', '5.4.2'),
(387, 'pcm', 'pcm', NULL),
(389, 'es_UY', 'es-uy', '5.3.2'),
(390, 'cor', 'cor', NULL),
(391, 'lij', 'lij', NULL),
(394, 'gax', 'gax', NULL),
(395, 'bn_IN', 'bn-in', NULL),
(402, 'dsb', 'dsb', NULL),
(403, 'wol', 'wol', NULL),
(404, 'mai', 'mai', NULL),
(405, 'pcd', 'pcd', NULL),
(407, 'fon', 'fon', NULL),
(408, 'vec', 'vec', NULL),
(409, 'zgh', 'zgh', NULL),
(410, 'es_EC', 'es-ec', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `wporg_locales`
--
ALTER TABLE `wporg_locales`
  ADD PRIMARY KEY (`locale_id`),
  ADD UNIQUE KEY `subdomain_locale` (`subdomain`,`locale`),
  ADD KEY `locale` (`locale`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `wporg_locales`
--
ALTER TABLE `wporg_locales`
  MODIFY `locale_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=411;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
