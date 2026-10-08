-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 08, 2026 at 10:48 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `uek295`
--

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `category_id` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL,
  `name` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`category_id`, `active`, `name`) VALUES
(1, 1, 'Gartenwerkzeuge'),
(2, 1, 'Bewässerung'),
(3, 1, 'Erde und Dünger'),
(4, 1, 'Gemüsesamen'),
(5, 1, 'Blumensamen'),
(6, 1, 'Pflanzen und Kräuter'),
(7, 1, 'Töpfe und Pflanzgefässe'),
(8, 1, 'Gartenmöbel'),
(9, 1, 'Gartendekoration'),
(10, 1, 'Gartenpflege');

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE `product` (
  `product_id` int(11) NOT NULL,
  `sku` varchar(100) NOT NULL,
  `active` tinyint(1) NOT NULL,
  `id_category` int(11) DEFAULT NULL,
  `name` varchar(500) NOT NULL,
  `image` varchar(1000) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(65,2) NOT NULL,
  `stock` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`product_id`, `sku`, `active`, `id_category`, `name`, `image`, `description`, `price`, `stock`) VALUES
(1, 'GARTEN-0001', 1, 1, 'Handschaufel', '', 'Kleine Pflanzschaufel mit ergonomischem Griff.', 8.90, 18),
(2, 'GARTEN-0002', 1, 1, 'Spaten', '', 'Spaten mit Stahlblatt und Holzstiel.', 34.90, 35),
(3, 'GARTEN-0003', 1, 1, 'Grabegabel', '', 'Grabegabel zum Lockern der Gartenerde.', 29.90, 52),
(4, 'GARTEN-0004', 1, 1, 'Gartenrechen', '', 'Rechen mit 14 Zinken.', 19.90, 69),
(5, 'GARTEN-0005', 1, 1, 'Laubbesen', '', 'Fächerbesen für Laub und Rasenschnitt.', 16.90, 86),
(6, 'GARTEN-0006', 1, 1, 'Gartenschere', '', 'Bypass-Schere für den Rückschnitt.', 24.90, 103),
(7, 'GARTEN-0007', 1, 1, 'Astschere', '', 'Astschere mit langen Griffen.', 49.90, 120),
(8, 'GARTEN-0008', 1, 1, 'Unkrautstecher', '', 'Handwerkzeug zum Entfernen von Unkraut.', 12.90, 0),
(9, 'GARTEN-0009', 1, 1, 'Pflanzholz', '', 'Pflanzholz zum Vorbereiten von Saatlöchern.', 6.90, 34),
(10, 'GARTEN-0010', 0, 1, 'Gartenhandschuhe Grösse M', '', 'Handschuhe mit beschichteten Handflächen.', 9.90, 51),
(11, 'GARTEN-0011', 1, 2, 'Gartenschlauch 20 m', '', 'Flexibler Gartenschlauch für die Bewässerung.', 39.90, 68),
(12, 'GARTEN-0012', 1, 2, 'Gartenschlauch 30 m', '', 'Gartenschlauch für grössere Gartenflächen.', 54.90, 85),
(13, 'GARTEN-0013', 1, 2, 'Giesskanne 5 l', '', 'Giesskanne mit abnehmbarem Brausekopf.', 12.90, 102),
(14, 'GARTEN-0014', 1, 2, 'Giesskanne 10 l', '', 'Grosse Giesskanne aus Kunststoff.', 19.90, 119),
(15, 'GARTEN-0015', 1, 2, 'Rasensprenger', '', 'Viereckregner für Rasenflächen.', 29.90, 16),
(16, 'GARTEN-0016', 1, 2, 'Sprühpistole', '', 'Handbrause mit mehreren Sprühbildern.', 17.90, 0),
(17, 'GARTEN-0017', 1, 2, 'Schlauchwagen', '', 'Mobiler Wagen zur Schlauchaufbewahrung.', 69.90, 50),
(18, 'GARTEN-0018', 1, 2, 'Tropfbewässerungsset', '', 'Set zur Bewässerung von Beeten.', 44.90, 67),
(19, 'GARTEN-0019', 1, 2, 'Bewässerungscomputer', '', 'Zeitschaltgerät für die Gartenbewässerung.', 59.90, 84),
(20, 'GARTEN-0020', 0, 2, 'Regentonne 200 l', '', 'Behälter zum Sammeln von Regenwasser.', 89.90, 101),
(21, 'GARTEN-0021', 1, 3, 'Blumenerde 20 l', '', 'Erde für Balkon- und Topfpflanzen.', 7.90, 118),
(22, 'GARTEN-0022', 1, 3, 'Blumenerde 40 l', '', 'Universalerde für Pflanzgefässe.', 12.90, 15),
(23, 'GARTEN-0023', 1, 3, 'Gemüseerde 40 l', '', 'Substrat für Gemüsebeete und Hochbeete.', 14.90, 32),
(24, 'GARTEN-0024', 1, 3, 'Anzuchterde 10 l', '', 'Feines Substrat für die Aussaat.', 6.90, 0),
(25, 'GARTEN-0025', 1, 3, 'Kräutererde 20 l', '', 'Substrat für Kräuter in Töpfen.', 9.90, 66),
(26, 'GARTEN-0026', 1, 3, 'Rindenmulch 50 l', '', 'Mulchmaterial zur Abdeckung von Beeten.', 11.90, 83),
(27, 'GARTEN-0027', 1, 3, 'Kompost 40 l', '', 'Kompost zur Bodenverbesserung.', 8.90, 100),
(28, 'GARTEN-0028', 1, 3, 'Tomatendünger 1 l', '', 'Flüssigdünger für Tomatenpflanzen.', 10.90, 117),
(29, 'GARTEN-0029', 1, 3, 'Rasendünger 5 kg', '', 'Granulat zur Pflege von Rasenflächen.', 24.90, 14),
(30, 'GARTEN-0030', 0, 3, 'Universaldünger 1 l', '', 'Flüssigdünger für Gartenpflanzen.', 8.90, 31),
(31, 'GARTEN-0031', 1, 4, 'Karottensamen', '', 'Saatgut für Karotten im Gemüsebeet.', 2.90, 48),
(32, 'GARTEN-0032', 1, 4, 'Radieschensamen', '', 'Saatgut für knackige Radieschen.', 2.50, 0),
(33, 'GARTEN-0033', 1, 4, 'Salatsamen', '', 'Saatgut für grünen Kopfsalat.', 2.90, 82),
(34, 'GARTEN-0034', 1, 4, 'Tomatensamen', '', 'Saatgut für rote Gartentomaten.', 3.90, 99),
(35, 'GARTEN-0035', 1, 4, 'Gurkensamen', '', 'Saatgut für Salatgurken.', 3.50, 116),
(36, 'GARTEN-0036', 1, 4, 'Zucchinisamen', '', 'Saatgut für grüne Zucchini.', 3.90, 13),
(37, 'GARTEN-0037', 1, 4, 'Kürbissamen', '', 'Saatgut für Speisekürbisse.', 4.50, 30),
(38, 'GARTEN-0038', 1, 4, 'Bohnensamen', '', 'Saatgut für Buschbohnen.', 3.90, 47),
(39, 'GARTEN-0039', 1, 4, 'Erbsensamen', '', 'Saatgut für Gartenerbsen.', 3.50, 64),
(40, 'GARTEN-0040', 0, 4, 'Spinatsamen', '', 'Saatgut für Blattspinat.', 2.90, 0),
(41, 'GARTEN-0041', 1, 5, 'Sonnenblumensamen', '', 'Saatgut für gelbe Sonnenblumen.', 2.90, 98),
(42, 'GARTEN-0042', 1, 5, 'Ringelblumensamen', '', 'Saatgut für orange Ringelblumen.', 2.50, 115),
(43, 'GARTEN-0043', 1, 5, 'Wildblumenmischung', '', 'Saatgutmischung für eine bunte Blumenfläche.', 5.90, 12),
(44, 'GARTEN-0044', 1, 5, 'Kornblumensamen', '', 'Saatgut für blaue Kornblumen.', 2.90, 29),
(45, 'GARTEN-0045', 1, 5, 'Mohnsamen', '', 'Saatgut für roten Ziermohn.', 2.50, 46),
(46, 'GARTEN-0046', 1, 5, 'Kapuzinerkressesamen', '', 'Saatgut für Kapuzinerkresse.', 3.50, 63),
(47, 'GARTEN-0047', 1, 5, 'Zinniensamen', '', 'Saatgut für farbige Zinnien.', 3.90, 80),
(48, 'GARTEN-0048', 1, 5, 'Cosmeasamen', '', 'Saatgut für Schmuckkörbchen.', 2.90, 0),
(49, 'GARTEN-0049', 1, 5, 'Lavendelsamen', '', 'Saatgut für Lavendel.', 3.90, 114),
(50, 'GARTEN-0050', 0, 5, 'Tagetessamen', '', 'Saatgut für Studentenblumen.', 2.50, 11),
(51, 'GARTEN-0051', 1, 6, 'Basilikum im Topf', '', 'Basilikumpflanze im Kulturtopf.', 4.90, 28),
(52, 'GARTEN-0052', 1, 6, 'Petersilie im Topf', '', 'Petersilienpflanze im Kulturtopf.', 4.50, 45),
(53, 'GARTEN-0053', 1, 6, 'Schnittlauch im Topf', '', 'Schnittlauchpflanze für den Kräutergarten.', 4.50, 62),
(54, 'GARTEN-0054', 1, 6, 'Rosmarin im Topf', '', 'Rosmarinpflanze im Kulturtopf.', 6.90, 79),
(55, 'GARTEN-0055', 1, 6, 'Thymian im Topf', '', 'Thymianpflanze für Beet oder Balkon.', 5.90, 96),
(56, 'GARTEN-0056', 1, 6, 'Minze im Topf', '', 'Minzpflanze im Kulturtopf.', 4.90, 0),
(57, 'GARTEN-0057', 1, 6, 'Salbei im Topf', '', 'Salbeipflanze für den Kräutergarten.', 5.90, 10),
(58, 'GARTEN-0058', 1, 6, 'Erdbeerpflanze', '', 'Junge Erdbeerpflanze im Topf.', 3.90, 27),
(59, 'GARTEN-0059', 1, 6, 'Tomatenpflanze', '', 'Junge Tomatenpflanze für die Weiterkultur.', 5.90, 44),
(60, 'GARTEN-0060', 0, 6, 'Lavendelpflanze', '', 'Lavendelpflanze im Kulturtopf.', 7.90, 61),
(61, 'GARTEN-0061', 1, 7, 'Terrakottatopf 15 cm', '', 'Pflanztopf aus Terrakotta.', 5.90, 78),
(62, 'GARTEN-0062', 1, 7, 'Terrakottatopf 25 cm', '', 'Terrakottatopf für grössere Pflanzen.', 12.90, 95),
(63, 'GARTEN-0063', 1, 7, 'Kunststofftopf 20 cm', '', 'Leichter Pflanztopf aus Kunststoff.', 6.90, 112),
(64, 'GARTEN-0064', 1, 7, 'Pflanzkübel 40 cm', '', 'Grosser Pflanzkübel für die Terrasse.', 29.90, 0),
(65, 'GARTEN-0065', 1, 7, 'Balkonkasten 60 cm', '', 'Pflanzkasten für Balkonblumen.', 14.90, 26),
(66, 'GARTEN-0066', 1, 7, 'Balkonkasten 80 cm', '', 'Langer Pflanzkasten für den Balkon.', 19.90, 43),
(67, 'GARTEN-0067', 1, 7, 'Hängeampel 25 cm', '', 'Pflanzgefäss mit Aufhängung.', 16.90, 60),
(68, 'GARTEN-0068', 1, 7, 'Anzuchtschale', '', 'Schale zur Anzucht von Jungpflanzen.', 7.90, 77),
(69, 'GARTEN-0069', 1, 7, 'Hochbeet aus Holz', '', 'Holzhochbeet für Gemüse und Kräuter.', 149.90, 94),
(70, 'GARTEN-0070', 0, 7, 'Topfuntersetzer 20 cm', '', 'Untersetzer für Pflanztöpfe.', 3.90, 111),
(71, 'GARTEN-0071', 1, 8, 'Gartenstuhl klappbar', '', 'Klappstuhl für Garten und Terrasse.', 49.90, 8),
(72, 'GARTEN-0072', 1, 8, 'Gartentisch rund', '', 'Runder Tisch für den Aussenbereich.', 129.90, 0),
(73, 'GARTEN-0073', 1, 8, 'Gartenbank aus Holz', '', 'Holzbank mit zwei Sitzplätzen.', 179.90, 42),
(74, 'GARTEN-0074', 1, 8, 'Sonnenliege', '', 'Verstellbare Liege für die Terrasse.', 119.90, 59),
(75, 'GARTEN-0075', 1, 8, 'Sonnenschirm 3 m', '', 'Sonnenschirm mit drei Metern Durchmesser.', 89.90, 76),
(76, 'GARTEN-0076', 1, 8, 'Sonnenschirmständer', '', 'Ständer für einen Gartensonnenschirm.', 39.90, 93),
(77, 'GARTEN-0077', 1, 8, 'Balkontisch klappbar', '', 'Platzsparender Tisch für den Balkon.', 69.90, 110),
(78, 'GARTEN-0078', 1, 8, 'Sitzkissen grün', '', 'Sitzkissen für Gartenstühle.', 14.90, 7),
(79, 'GARTEN-0079', 1, 8, 'Aufbewahrungsbox 300 l', '', 'Box zur Aufbewahrung von Gartenutensilien.', 99.90, 24),
(80, 'GARTEN-0080', 0, 8, 'Hängematte', '', 'Hängematte für entspannte Gartenstunden.', 44.90, 0),
(81, 'GARTEN-0081', 1, 9, 'Solarleuchte', '', 'Dekorative Leuchte mit Solarmodul.', 9.90, 58),
(82, 'GARTEN-0082', 1, 9, 'Solarlichterkette', '', 'Lichterkette für Garten und Balkon.', 24.90, 75),
(83, 'GARTEN-0083', 1, 9, 'Windspiel', '', 'Hängendes Windspiel für den Garten.', 19.90, 92),
(84, 'GARTEN-0084', 1, 9, 'Vogelhaus', '', 'Futterhaus für Gartenvögel.', 29.90, 109),
(85, 'GARTEN-0085', 1, 9, 'Vogeltränke', '', 'Dekorative Wasserschale für Vögel.', 24.90, 6),
(86, 'GARTEN-0086', 1, 9, 'Insektenhotel', '', 'Dekoratives Insektenhotel aus Holz.', 34.90, 23),
(87, 'GARTEN-0087', 1, 9, 'Gartenfigur Igel', '', 'Kleine Igel-Figur zur Gartendekoration.', 16.90, 40),
(88, 'GARTEN-0088', 1, 9, 'Gartenlaterne', '', 'Laterne für eine Kerze.', 22.90, 0),
(89, 'GARTEN-0089', 1, 9, 'Rankgitter', '', 'Rankhilfe für Kletterpflanzen.', 29.90, 74),
(90, 'GARTEN-0090', 0, 9, 'Pflanzenschilder 10 Stück', '', 'Beschriftbare Schilder für Beete.', 5.90, 91),
(91, 'GARTEN-0091', 1, 10, 'Handrasenmäher', '', 'Manueller Spindelmäher für kleine Rasenflächen.', 99.90, 108),
(92, 'GARTEN-0092', 1, 10, 'Akku-Rasenmäher', '', 'Rasenmäher mit Akku und Ladegerät.', 299.90, 5),
(93, 'GARTEN-0093', 1, 10, 'Rasentrimmer', '', 'Trimmer zur Pflege von Rasenkanten.', 79.90, 22),
(94, 'GARTEN-0094', 1, 10, 'Heckenschere elektrisch', '', 'Elektrische Heckenschere für den Rückschnitt.', 89.90, 39),
(95, 'GARTEN-0095', 1, 10, 'Laubsack 120 l', '', 'Wiederverwendbarer Sack für Gartenabfälle.', 12.90, 56),
(96, 'GARTEN-0096', 1, 10, 'Komposter 400 l', '', 'Komposter für pflanzliche Gartenabfälle.', 69.90, 0),
(97, 'GARTEN-0097', 1, 10, 'Unkrautvlies 10 m²', '', 'Vlies zur Abdeckung von Beetflächen.', 19.90, 90),
(98, 'GARTEN-0098', 1, 10, 'Rasensamen 1 kg', '', 'Saatgutmischung für Rasenflächen.', 14.90, 107),
(99, 'GARTEN-0099', 1, 10, 'Rasenkante 10 m', '', 'Flexible Einfassung für Rasen und Beete.', 24.90, 4),
(100, 'GARTEN-0100', 0, 10, 'Gartensieb', '', 'Sieb zum Aufbereiten von Gartenerde.', 18.90, 21);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `fk_product_category` (`id_category`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=101;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `product`
--
ALTER TABLE `product`
  ADD CONSTRAINT `fk_product_category` FOREIGN KEY (`id_category`) REFERENCES `category` (`category_id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
