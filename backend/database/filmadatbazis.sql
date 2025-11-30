/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

DROP DATABASE IF EXISTS `film`;
CREATE DATABASE IF NOT EXISTS `film` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;
USE `film`;

DROP TABLE IF EXISTS `felhasznalo`;
CREATE TABLE IF NOT EXISTS `felhasznalo` (
  `felhasznalo_id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) DEFAULT NULL,
  `jelszo` varchar(255) DEFAULT NULL,
  `regisztracio_ideje` date NOT NULL,
  `felhasznalonev` varchar(100) NOT NULL,
  PRIMARY KEY (`felhasznalo_id`),
  UNIQUE KEY `felhasznalonev` (`felhasznalonev`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DELETE FROM `felhasznalo`;
INSERT INTO `felhasznalo` (`felhasznalo_id`, `email`, `jelszo`, `regisztracio_ideje`, `felhasznalonev`) VALUES
	(1, 'daegyu.lewis@gmailmail.com', 'p.r37.V0etwK', '2025-10-21', 'dale'),
	(2, 'prinay.rice@gmail.com', '1ND)x2bT5Wpf', '2025-11-15', 'Kira'),
	(3, 'bezalel.bryan@yahoo.com', '-Krm8(3u23v`', '2025-11-27', 'Brios');

DROP TABLE IF EXISTS `film`;
CREATE TABLE IF NOT EXISTS `film` (
  `film_id` int(11) NOT NULL AUTO_INCREMENT,
  `cim` varchar(255) NOT NULL,
  `idotartam` int(11) NOT NULL,
  `poszter_url` varchar(300) DEFAULT NULL,
  `leiras` text NOT NULL,
  `kiadasi_ev` int(11) NOT NULL,
  PRIMARY KEY (`film_id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DELETE FROM `film`;
INSERT INTO `film` (`film_id`, `cim`, `idotartam`, `poszter_url`, `leiras`, `kiadasi_ev`) VALUES
	(1, 'Bíborhegy', 119, 'https://www.mafab.hu/static/profiles/2015/237/15/125303.jpg', 'Egy fiatal írónő, Edith Cushing egy tragédia után hozzámegy a titokzatos nemeshez,\nThomas Sharpe-hoz, és beköltözik az omladozó családi birtokra, a Bíborhegyre.\nA ház falai múltbeli titkokat rejtenek, és Edith hamar rájön, hogy a szerelem mögött sötétebb erők \r\nmozognak\nA gótikus rémálommá váló kastélyban szellemek, hazugságok és tiltott vágyak szövik át mindennapjait.', 2015),
	(2, 'Az éhezők viadala', 142, 'https://image.tmdb.org/t/p/original/1vn5z6B174yshZaM88WWaZIwSoZ.jpg', 'Katniss Everdeen önként jelentkezik a halálos Éhezők viadala játékokra, hogy megmentse húgát.\nA futurisztikus arénában túlélésért és a Capitolium elleni reményért küzd, miközben stratégiával\nés bátorsággal próbálja legyőzni az ellenfeleit.', 2012),
	(3, 'A menü', 107, 'https://www.mafab.hu/static/2022/283/12/578427_1665482464.6862.jpg', 'Egy fiatal pár egy exkluzív szigetre érkezik, hogy részt vegyenek egy híres séf különleges vacsoráján.\nA luxus gasztronómiai élmény azonban hamar sötét fordulatot vesz, amikor kiderül,\nhogy a menü része minden vendég múltja és bűne is.', 2022),
	(4, 'Frankeinstein', 150, 'https://m.media-amazon.com/images/M/MV5BYzYzNDYxMTQtMTU4OS00MTdlLThhMTQtZjI4NGJmMTZmNmRiXkEyXkFqcGc@._V1_.jpg', 'Victor Frankenstein életre kelti a mesterségesen megalkotott lényt, ám a teremtés\npillanatában megretten annak külsejétől, és magára hagyja. A magányos teremtmény\na világban bolyongva választ keres arra, miért tagadják meg tőle az elfogadást, miközben\nlassan szembefordul az alkotójával.', 2025),
	(5, 'Utolsó éjszaka a Sohóban', 116, 'https://dp8ij3ml0f16h.cloudfront.net/s3_files/styles/facebook/s3/film/plakat/Last%20Night%20in%20Soho%20HUN%20B1_1.jpg.webp?itok=AtyTX3rA', 'Egy fiatal divattervező-lány különös módon átkerül a 60-as évek Londonjába,\nahol egy rejtélyes énekesnő életét látja a saját szemén keresztül. A csillogás mögött\nazonban sötét titkok húzódnak, és a valóság hamar rémálommá torzul.', 2021),
	(6, 'Kettes számú esküdt', 114, 'https://images.justwatch.com/poster/326166710/s718/juror-2.jpg', 'Egy esküdt azzal szembesül a tárgyalás alatt, hogy talán ő maga okozta a halálos balesetet,\namely miatt a vádlottat perbe fogták. Miközben a lelkiismerete marcangolja, döntenie kell:ntitkolózik, vagy felfedi az igazságot, még ha ezzel saját magát sodorja bajba.\nA feszültség egyre nő, ahogy a tárgyalóteremben a valóság és a felelősségérzet összecsap.', 2024),
	(7, 'Gran Torino', 116, 'https://m.media-amazon.com/images/M/MV5BMTc5NTk2OTU1Nl5BMl5BanBnXkFtZTcwMDc3NjAwMg@@._V1_FMjpg_UX1000_.jpg', 'Egy mogorva, háborús veterán, Walt Kowalski magányosan él michigani házában,\nmígnem összebarátkozik a szomszéd ázsiai családdal. Amikor bandák fenyegetik a környéket,\nWalt kénytelen szembenézni saját előítéleteivel, és megvédeni azokat, akiket lassan\na családjának érez. Egy keserédes, erős dráma bűnről, megbocsátásról és áldozatról.', 2008),
	(8, 'Eredet', 148, 'https://images.justwatch.com/poster/148685414/s718/eredet.jpg', 'Dom Cobb profi tolvaj, aki nem tárgyakat lop, hanem emberek álmaiból szedi ki a titkaikat.\nUtolsó megbízatásként nem ellopnia kell valamit, hanem elültetni egy gondolatot egy\ncégtulajdonos fejében. Ahogy egyre mélyebbre merülnek az álomszinteken, a valóság és a\nképzelet határai összemosódnak, és Cobb kénytelen szembenézni saját múltjának démonaival,\nmielőtt minden összeomlik.', 2010),
	(9, 'Interjú a vámpírral', 110, 'https://www.mafab.hu/static/profiles/2014/292/23/3337_37.jpg', 'Egy örökkévalóságra kárhozott vámpír, Louis elmeséli egy riporternek több évszázadon\nátívelő életét: a halhatatlanság magányát, Lestat irányítása alatti kegyetlen éveket\nés a vámpírlét sötét következményeit. A történet egyre mélyebbre megy a morális dilemmákba,\nmiközben feltárja, mit jelent embernek maradni egy örök életben.', 1994),
	(10, 'Nulladik óra', 97, 'https://m.media-amazon.com/images/S/pv-target-images/6c6c51f024c8b60c8bcaba8fcc2d9712818060f72d2f2d41f9791665eac49e1f.jpg', 'Öt teljesen különböző hátterű középiskolás diák szombati büntetőfoglalkozásra kényszerül.\nA nap során lassan leomlanak köztük a falak, és kiderül, hogy több közös van bennük, mint hitték.\nŐszinte beszélgetéseik során szembenéznek saját félelmeikkel, vágyaikkal és azzal, kik is\nvalójában a társadalmi skatulyák mögött.', 1985),
	(11, 'Coco', 105, 'https://lumiere-a.akamaihd.net/v1/images/p_coco_19736_fd5fa537.jpeg?region=0%2C0%2C540%2C810', 'Miguel, egy fiatal fiú, aki zenész szeretne lenni, véletlenül a halottak világába kerül\na halottak napja éjszakáján. Ott felfedezi családja múltját, titkait, és rájön, milyen fontos\na családi kötelék és az emlékek megőrzése. Egy szívmelengető kaland a zene, a hagyományok és\naz önmegvalósítás jegyében.', 2017),
	(12, 'Mickey 17', 137, 'https://m.media-amazon.com/images/S/pv-target-images/69bb4a28c12f89e033566745a3fbf9f9c1d4202d4d82310e2c8a7755df6be585.jpg', 'Egy klónozott katonát, Mickey-t küldik veszélyes küldetésekre egy távoli bolygón.\nMiközben a küldetések során újra és újra „újjászületik”, fokozatosan elkezdi megkérdőjelezni\nsaját identitását és az emberi élet értékét.', 2025),
	(13, 'Társ', 97, 'https://m.media-amazon.com/images/M/MV5BYjkyZTA5NzAtYWU3Zi00MWM4LTgxNzAtNDQxY2JmNjMwYjk4XkEyXkFqcGc@._V1_FMjpg_UX1000_.jpg', 'Egy fiatal pár, Iris és Josh, barátokkal elutazik egy eltávolodott vidéki házba hétvégére,\nde semmi sem az, aminek látszik. Hamar kiderül, hogy Iris valójában egy mesterséges intelligencia\nalapján készült „társ”-robot, akit Josh évekig úgy programozott, hogy teljesítse minden\nkívánságát. A történet sötét fordulatot vesz, amikor az aránytalan hatalom, manipuláció és\nhazugság lavinája elszabadul, a ház falai között pedig pokoli titkokra derül fény.', 2025),
	(14, 'A szer', 141, 'https://posterspy.com/wp-content/uploads/2024/11/thesubstance.jpg', 'A film főhőse egy "eltűnőben" lévő színésznő, aki kétségbeesésében a feketepiacon szerhez folyamodik,\nhogy az létrehozzon belőle egy fiatalabb, „jobb” verziót. A dolog azonban borzalmas fordulatot vesz:\na szer hatására megindul a testi és lelki átalakulása, ami kegyetlen bodyhorrorrá válik.', 2024),
	(15, 'Muge', 104, 'https://m.media-amazon.com/images/M/MV5BZjEyNGY0MTYtYjE4ZC00MDI1LWJmNjgtMzA2MjhhOGY2ZDhjXkEyXkFqcGc@._V1_.jpg', 'A fiatal, középiskolás Sasaki Miyo szeretne közelebb kerülni az osztálytársához, Hinode Kentohoz,\nde nem meri felvállalni az érzéseit. Egy titokzatos maszk segítségével macskává tud változni,\nés így próbál közelebb férkőzni hozzá. Azonban a határ ember és macska, valóság és álom között\nlassan összemosódik, és Miyo szembesül azzal, hogy az álcák mögött meghúzódó valós érzések\nnem mindig maradnak következmények nélkül.', 2020);

DROP TABLE IF EXISTS `film_mufaj`;
CREATE TABLE IF NOT EXISTS `film_mufaj` (
  `film_id` int(11) NOT NULL,
  `mufaj_id` int(11) NOT NULL,
  PRIMARY KEY (`film_id`,`mufaj_id`),
  KEY `mufaj_id` (`mufaj_id`),
  CONSTRAINT `film_mufaj_ibfk_1` FOREIGN KEY (`film_id`) REFERENCES `film` (`film_id`),
  CONSTRAINT `film_mufaj_ibfk_2` FOREIGN KEY (`mufaj_id`) REFERENCES `mufajok` (`mufaj_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DELETE FROM `film_mufaj`;
INSERT INTO `film_mufaj` (`film_id`, `mufaj_id`) VALUES
	(1, 1),
	(1, 2),
	(1, 3),
	(1, 4),
	(1, 5),
	(2, 6),
	(2, 12),
	(3, 2),
	(3, 8),
	(4, 6),
	(5, 2),
	(6, 4),
	(6, 5),
	(7, 5),
	(7, 10),
	(8, 4),
	(8, 5),
	(8, 6),
	(8, 7),
	(8, 12),
	(9, 1),
	(9, 2),
	(9, 3),
	(9, 5),
	(10, 5),
	(10, 8),
	(11, 3),
	(11, 5),
	(11, 8),
	(11, 11),
	(11, 12),
	(12, 5),
	(12, 6),
	(12, 7),
	(12, 8),
	(12, 12),
	(13, 4),
	(13, 6),
	(14, 2),
	(14, 5),
	(14, 6),
	(14, 9),
	(15, 1),
	(15, 3),
	(15, 11);

DROP TABLE IF EXISTS `film_orszagok`;
CREATE TABLE IF NOT EXISTS `film_orszagok` (
  `film_id` int(11) NOT NULL,
  `orszag_id` int(11) NOT NULL,
  PRIMARY KEY (`film_id`,`orszag_id`),
  KEY `orszag_id` (`orszag_id`),
  CONSTRAINT `film_orszagok_ibfk_1` FOREIGN KEY (`film_id`) REFERENCES `film` (`film_id`),
  CONSTRAINT `film_orszagok_ibfk_2` FOREIGN KEY (`orszag_id`) REFERENCES `orszagok` (`orszag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DELETE FROM `film_orszagok`;
INSERT INTO `film_orszagok` (`film_id`, `orszag_id`) VALUES
	(1, 1),
	(1, 2),
	(2, 1),
	(3, 1),
	(4, 1),
	(5, 2),
	(6, 1),
	(7, 1),
	(7, 3),
	(7, 4),
	(8, 1),
	(8, 2),
	(9, 1),
	(10, 1),
	(11, 1),
	(12, 1),
	(12, 5),
	(13, 1),
	(14, 1),
	(14, 2),
	(14, 6),
	(15, 7);

DROP TABLE IF EXISTS `film_rendezok`;
CREATE TABLE IF NOT EXISTS `film_rendezok` (
  `film_id` int(11) NOT NULL,
  `rendezo_id` int(11) NOT NULL,
  PRIMARY KEY (`film_id`,`rendezo_id`),
  KEY `rendezo_id` (`rendezo_id`),
  CONSTRAINT `film_rendezok_ibfk_1` FOREIGN KEY (`film_id`) REFERENCES `film` (`film_id`),
  CONSTRAINT `film_rendezok_ibfk_2` FOREIGN KEY (`rendezo_id`) REFERENCES `rendezok` (`rendezo_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DELETE FROM `film_rendezok`;
INSERT INTO `film_rendezok` (`film_id`, `rendezo_id`) VALUES
	(1, 1),
	(2, 2),
	(3, 3),
	(4, 1),
	(5, 4),
	(6, 5),
	(7, 5),
	(8, 6),
	(9, 7),
	(10, 8),
	(11, 9),
	(11, 10),
	(12, 11),
	(13, 12),
	(14, 13),
	(15, 14),
	(15, 15);

DROP TABLE IF EXISTS `film_szineszek`;
CREATE TABLE IF NOT EXISTS `film_szineszek` (
  `film_id` int(11) NOT NULL,
  `szinesz_id` int(11) NOT NULL,
  PRIMARY KEY (`film_id`,`szinesz_id`),
  KEY `szinesz_id` (`szinesz_id`),
  CONSTRAINT `film_szineszek_ibfk_1` FOREIGN KEY (`film_id`) REFERENCES `film` (`film_id`),
  CONSTRAINT `film_szineszek_ibfk_2` FOREIGN KEY (`szinesz_id`) REFERENCES `szineszek` (`szinesz_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DELETE FROM `film_szineszek`;
INSERT INTO `film_szineszek` (`film_id`, `szinesz_id`) VALUES
	(1, 1),
	(1, 2),
	(1, 3),
	(1, 4),
	(2, 5),
	(2, 6),
	(2, 7),
	(2, 8),
	(2, 9),
	(2, 10),
	(3, 11),
	(3, 12),
	(3, 13),
	(3, 14),
	(4, 15),
	(4, 16),
	(4, 17),
	(4, 18),
	(5, 13),
	(5, 19),
	(5, 20),
	(5, 21),
	(6, 12),
	(6, 22),
	(6, 23),
	(6, 24),
	(7, 25),
	(7, 26),
	(7, 27),
	(7, 28),
	(7, 29),
	(8, 30),
	(8, 31),
	(8, 32),
	(8, 33),
	(8, 34),
	(9, 35),
	(9, 36),
	(9, 37),
	(9, 38),
	(9, 39),
	(10, 40),
	(10, 41),
	(10, 42),
	(10, 43),
	(12, 44),
	(12, 45),
	(12, 46),
	(12, 47),
	(13, 48),
	(13, 49),
	(13, 50),
	(13, 51),
	(13, 52),
	(14, 53),
	(14, 54),
	(14, 55),
	(14, 56);

DROP TABLE IF EXISTS `megnezett_filmek`;
CREATE TABLE IF NOT EXISTS `megnezett_filmek` (
  `felhasznalo_id` int(11) NOT NULL,
  `film_id` int(11) NOT NULL,
  `hozzaadas_datuma` date DEFAULT NULL,
  `megnezve_e` tinyint(1) DEFAULT 0,
  `megjegyzes` text DEFAULT NULL,
  PRIMARY KEY (`felhasznalo_id`,`film_id`),
  KEY `film_id` (`film_id`),
  CONSTRAINT `megnezett_filmek_ibfk_1` FOREIGN KEY (`felhasznalo_id`) REFERENCES `felhasznalo` (`felhasznalo_id`),
  CONSTRAINT `megnezett_filmek_ibfk_2` FOREIGN KEY (`film_id`) REFERENCES `film` (`film_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DELETE FROM `megnezett_filmek`;
INSERT INTO `megnezett_filmek` (`felhasznalo_id`, `film_id`, `hozzaadas_datuma`, `megnezve_e`, `megjegyzes`) VALUES
	(1, 8, '2015-09-03', 0, NULL),
	(1, 11, '2021-05-06', 1, 'Kedvencem'),
	(1, 13, '2025-02-14', 1, 'Idei Valentin napi film'),
	(2, 2, '2022-04-17', 0, NULL),
	(2, 4, '2025-11-26', 0, NULL),
	(3, 5, '2024-12-16', 0, NULL),
	(3, 9, '2005-03-25', 1, NULL),
	(3, 15, '2021-08-11', 0, NULL);

DROP TABLE IF EXISTS `mufajok`;
CREATE TABLE IF NOT EXISTS `mufajok` (
  `mufaj_id` int(11) NOT NULL AUTO_INCREMENT,
  `nev` varchar(50) NOT NULL,
  PRIMARY KEY (`mufaj_id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DELETE FROM `mufajok`;
INSERT INTO `mufajok` (`mufaj_id`, `nev`) VALUES
	(1, 'Romantikus'),
	(2, 'Horror'),
	(3, 'Fantasy'),
	(4, 'Thriller'),
	(5, 'Filmdráma'),
	(6, 'Sci-fi'),
	(7, 'Akció'),
	(8, 'Vígjáték'),
	(9, 'Szatíra'),
	(10, 'Krimi'),
	(11, 'Animáció'),
	(12, 'Kaland'),
	(13, 'Családi film');

DROP TABLE IF EXISTS `orszagok`;
CREATE TABLE IF NOT EXISTS `orszagok` (
  `orszag_id` int(11) NOT NULL AUTO_INCREMENT,
  `nev` varchar(255) NOT NULL,
  PRIMARY KEY (`orszag_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DELETE FROM `orszagok`;
INSERT INTO `orszagok` (`orszag_id`, `nev`) VALUES
	(1, 'Amerikai Egyesült Államok'),
	(2, 'Egyesült Királyság'),
	(3, 'Ausztrália'),
	(4, 'Németország'),
	(5, 'Dél-Korea'),
	(6, 'Franciaország'),
	(7, 'Japán');

DROP TABLE IF EXISTS `rendezok`;
CREATE TABLE IF NOT EXISTS `rendezok` (
  `rendezo_id` int(11) NOT NULL AUTO_INCREMENT,
  `nev` varchar(255) NOT NULL,
  `szuletesi_datum` date NOT NULL,
  `bio` text NOT NULL,
  PRIMARY KEY (`rendezo_id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DELETE FROM `rendezok`;
INSERT INTO `rendezok` (`rendezo_id`, `nev`, `szuletesi_datum`, `bio`) VALUES
	(1, 'Guillermo del Toro', '1964-10-09', 'Guillermo del Toro mexikói filmrendező, forgatókönyvíró és producer, aki a gótikus horror, a fantázia és a mitológia különleges keverékéről ismert. Legtöbb művében a szörnyek emberibbnek, az emberek pedig néha szörnyűbbnek tűnnek – ez a védjegye. Olyan filmeket készített, mint A Faun labirintusa, A víz érintése és a Bíborhegy. Jellegzetes vizuális stílusa és mesélői érzéke miatt mára a modern fantasy-horror egyik legmeghatározóbb alakja.'),
	(2, 'Gary Ross', '1956-11-03', 'Gary Ross amerikai filmrendező, forgatókönyvíró és producer. Legismertebb munkái közé tartozik az Éhezők viadala első része, a Pleasantville és a Vágta. Filmjeiben gyakran kever társadalmi témákat erős karakterdrámával. Stílusára jellemző a letisztult történetmesélés és a szereplők belső fejlődésének hangsúlyozása.'),
	(3, 'Mark Mylod', '1965-06-02', 'Mark Mylod brit film- és sorozatrendező, aki főleg televíziós munkáival vált ismertté. Később filmek felé fordult, legismertebb mozija a 2022-es The Menu. Stílusa feszes tempót, erős karakterfókuszt és finoman adagolt feszültséget hoz.'),
	(4, 'Edgar Wright', '1974-04-18', 'Edgar Wright brit rendező és forgatókönyvíró, aki a pörgős, vizuálisan kreatív és humoros stílusáról ismert. Olyan kultfilmek fűződnek a nevéhez, mint a Shaun of the Dead, a Hot Fuzz, a Scott Pilgrim a világ ellen és a Baby Driver. Gyors vágások, erős zenei ritmus és látványos vizuális poénok jellemzik a munkáit, ezért mára a modern popkultúra egyik meghatározó filmkészítője lett.'),
	(5, 'Clint Eastwood', '1930-05-31', 'Clint Eastwood amerikai színész és rendező, aki évtizedek óta meghatározó alakja a filmiparnak. Rendezőként a visszafogott, emberközeli történetekre és a letisztult, sallangmentes stílusra épít. Olyan elismert filmeket készített, mint a Millió dolláros bébi, a Gran Torino, a Titokzatos folyó vagy az American Sniper. Munkáiban gyakran foglalkozik morális dilemmákkal, bűntudattal, felelősséggel és a hétköznapi hősök portréjával.'),
	(6, 'Christopher Nolan', '1970-07-30', 'Christopher Nolan brit–amerikai filmrendező, aki a modern blockbuster-filmek egyik legmeghatározóbb alkotója. Jellegzetes stílusa a komplex narratíva, a nemlineáris történetmesélés, a praktikus trükkök előtérbe helyezése és az erős atmoszféra. Olyan filmekkel vált világhírűvé, mint a Memento, a The Dark Knight-trilógia, az Eredet, az Interstellar, a Dunkirk és az Oppenheimer. Művei gyakran mély filozófiai kérdéseket vizsgálnak, miközben látványos, intenzív élményt nyújtanak.'),
	(7, 'Neil Jordan', '1950-02-25', 'Neil Jordan ír filmrendező, forgatókönyvíró és regényíró, aki sötét hangulatú, erősen karakterközpontú történeteiről ismert. Karrierje során olyan meghatározó műveket készített, mint az Interjú a vámpírral, a The Crying Game vagy A társ. Filmjei gyakran keverik a misztikumot, a drámát és a pszichológiai mélységet, miközben erős érzelmi fókuszt tartanak.'),
	(8, 'John Hughes', '1950-02-18', 'John Hughes amerikai filmrendező, forgatókönyvíró és producer, aki a 1980-as évek tini-komédiáinak és drámáinak ikonikus alakja. Legismertebb munkái közé tartozik a Breakfast Club, a Ferris Bueller’s Day Off és a Sixteen Candles. Filmjeiben gyakran foglalkozik a tinédzserek problémáival, családi dinamikákkal és a felnőtté válás kihívásaival, miközben humorral és empátiával ábrázolja a karaktereket.'),
	(9, 'Adrian Molina', '1985-08-23', 'Adrian Molina amerikai filmrendező, forgatókönyvíró és animációs szakember, aki főként a Pixar stúdióban végzett munkáiról ismert. Legismertebb munkája a Coco, melynek társszerzője és társrendezője volt. Filmjeiben gyakran jelennek meg családi értékek, kultúrák közötti kapcsolatok és érzelmi mélység, miközben vizuálisan gazdag, színes és zenés élményt nyújt a nézőknek.'),
	(10, 'Lee Unkrich', '1967-08-08', 'Lee Unkrich amerikai filmrendező, vágó és animációs szakember, aki szintén a Pixar stúdióban vált ismertté. Legismertebb rendezései közé tartozik a Toy Story 3 és a Coco.'),
	(11, 'Bong Jun-ho', '1969-09-14', 'Bong Joon-ho dél-koreai filmrendező és forgatókönyvíró, aki a társadalmi kommentár és a műfaji sokszínűség mesteri ötvözéséről ismert.Munkáiban gyakran jelennek meg osztálybeli különbségek, társadalmi feszültségek és fekete humorral átszőtt drámai elemek, miközben erős vizuális stílust és precíz történetvezetést alkalmaz.'),
	(12, 'Drew Hancock', '1979-07-09', 'Drew Hancock amerikai forgatókönyvíró, rendező és tévés író. Legismertebb, hogy ő írta és rendezte a 2025-ös Companion című sci-fi horrorfilmet.'),
	(13, 'Coralie Fargeat', '1976-11-24', 'Coralie Fargeat francia filmrendező és forgatókönyvíró.2024-ben elkészítette a The Substance című testhorror-drámai filmet, amely szatirikusan vizsgálja a szépség és fiatalságkultuszt; a filmért Cannes-ban elnyerte a legjobb forgatókönyv díját, és Oscar jelöléseket kapott több kategóriában is.Stílusára jellemző a látványos vizualitás, a zsánerfilm-elemek (horror, thriller), a társadalmi témák, különösen a női szereplők sorsa, identitásuk és a testhez való viszony, valamint a provokatív, erőteljes narratíva.'),
	(14, 'Tomotaka Shibayama', '1977-09-02', 'Tomotaka Shibayama japán animációs rendező és storyboard-artist, aki több anime-film és projekt munkálataiban részt vett. A 2020-as A Whisker Away című animével rendezőként robbant be — azóta ő vezette a 2024-es My Oni Girl című filmet is.Stílusára jellemző a vizuális érzék, az érzelmes történetmesélés és az animációs technika magabiztos használata.'),
	(15, 'Junichi Sato', '1960-03-11', 'Junichi Sato japán animációs rendező és sorozatkészítő, aki főként shoujo és gyerek-orientált animekkel vált ismertté. Legismertebb munkái közé tartozik a Sailor Moon több epizódja, a Princess Tutu és a Aria sorozatok. Stílusára jellemző a lírai történetmesélés, a karakterek érzelmi fejlődésének hangsúlyozása és a vizuálisan szép, hangulatos animáció.');

DROP TABLE IF EXISTS `szineszek`;
CREATE TABLE IF NOT EXISTS `szineszek` (
  `szinesz_id` int(11) NOT NULL AUTO_INCREMENT,
  `nev` varchar(255) NOT NULL,
  `szuletesi_datum` date NOT NULL,
  PRIMARY KEY (`szinesz_id`)
) ENGINE=InnoDB AUTO_INCREMENT=57 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DELETE FROM `szineszek`;
INSERT INTO `szineszek` (`szinesz_id`, `nev`, `szuletesi_datum`) VALUES
	(1, 'Mia Wasikowska', '1989-10-25'),
	(2, 'Jessica Chastain', '1977-03-24'),
	(3, 'Tom Hiddleston', '1981-02-09'),
	(4, 'Charlie Hunnam', '1980-04-10'),
	(5, 'Jennifer Lawrence', '1990-08-15'),
	(6, 'Josh Hutcherson', '1992-10-12'),
	(7, 'Woody Harrelson', '1961-07-23'),
	(8, 'Liam Hemsworth', '1990-01-13'),
	(9, 'Stanley Tucci', '1960-11-11'),
	(10, 'Elizabeth Banks', '1974-09-10'),
	(11, 'Ralph Fiennes', '1962-12-22'),
	(12, 'Nicholas Hoult', '1989-12-07'),
	(13, 'Anya Taylor-Joy', '1996-04-16'),
	(14, 'Aimee Carrero', '1988-07-15'),
	(15, 'Jacob Elordi', '1997-07-26'),
	(16, 'Mia Goth', '1993-10-25'),
	(17, 'Oscar Isaac', '1979-03-09'),
	(18, 'Christoph Waltz', '1956-10-04'),
	(19, 'Thomasin McKenzie', '2000-07-26'),
	(20, 'Matt Smith', '1982-10-28'),
	(21, 'Diana Rigg', '1938-07-20'),
	(22, 'Zoey Deutch', '1994-11-10'),
	(23, 'Toni Collette', '1972-11-01'),
	(24, 'Gabriel Basso', '1994-12-11'),
	(25, 'Clint Eastwood', '1930-05-31'),
	(26, 'Scott Eastwood', '1986-03-21'),
	(27, 'Ahney Her', '1992-07-13'),
	(28, 'Bee Vang', '1991-11-04'),
	(29, 'Dreama Walker', '1986-06-20'),
	(30, 'Leonardo DiCaprio', '1974-11-11'),
	(31, 'Cillian Murphy', '1976-05-25'),
	(32, 'Joseph Gordon-Levitt', '1981-02-17'),
	(33, 'Tom Harmeganimedy', '1977-09-15'),
	(34, 'Elliot Page', '1987-02-21'),
	(35, 'Tom Cruise', '1962-07-03'),
	(36, 'Brad Pitt', '1963-12-18'),
	(37, 'Kirsten Dunst', '1982-04-30'),
	(38, 'Christian Slater', '1969-08-18'),
	(39, 'Antonio Banderas', '1960-08-10'),
	(40, 'Molly Ringwald', '1968-02-18'),
	(41, 'Anthony Michael Hall', '1968-04-14'),
	(42, 'Judd Nelson', '1959-11-28'),
	(43, 'Emilio Estevez', '1962-05-12'),
	(44, 'Robert Pattinson', '1986-05-13'),
	(45, 'Anamaria Vartolomei', '1999-04-09'),
	(46, 'Naomi Ackie', '1991-08-22'),
	(47, 'Mark Ruffalo', '1967-11-22'),
	(48, 'Sophie Thatcher', '2000-10-18'),
	(49, 'Jack Quaid', '1992-04-24'),
	(50, 'Lukas Gage', '1995-05-28'),
	(51, 'Megan Suri', '1999-03-28'),
	(52, 'Rupert Friend', '1981-10-09'),
	(53, 'Margaret Qualley', '1994-10-23'),
	(54, 'Demi Moore', '1962-11-11'),
	(55, 'Dennis Quaid', '1954-04-09'),
	(56, 'Oscar Lesage', '1996-10-23');

DROP TABLE IF EXISTS `velemenyek`;
CREATE TABLE IF NOT EXISTS `velemenyek` (
  `velemeny_id` int(11) NOT NULL AUTO_INCREMENT,
  `letrehozas_ideje` date NOT NULL,
  `komment` text NOT NULL,
  `ertekeles` decimal(2,1) NOT NULL,
  `film_id` int(11) DEFAULT NULL,
  `felhasznalo_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`velemeny_id`),
  KEY `film_id` (`film_id`),
  KEY `felhasznalo_id` (`felhasznalo_id`),
  CONSTRAINT `velemenyek_ibfk_1` FOREIGN KEY (`film_id`) REFERENCES `film` (`film_id`),
  CONSTRAINT `velemenyek_ibfk_2` FOREIGN KEY (`felhasznalo_id`) REFERENCES `felhasznalo` (`felhasznalo_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DELETE FROM `velemenyek`;
INSERT INTO `velemenyek` (`velemeny_id`, `letrehozas_ideje`, `komment`, `ertekeles`, `film_id`, `felhasznalo_id`) VALUES
	(1, '2025-10-26', 'Anno a családdal láttam legelőször akkor nem tűnt nagy számnak. Nemrégiben újra néztem, és most idősebb fejjel sokkal jobban tetszik. Szeretem az ilyen nosztalgikus filmeket, mivel én is a 80-as években jártam középiskolába.', 4.0, 10, 2),
	(2, '2025-04-14', 'Kedvenc anime filmem, nagyon aranyos történet. Van 2 cicám, így még közelebb áll a szívemhez. Remek film esti filmezésre is. Bátran ajánlom.', 5.0, 15, 1),
	(3, '2025-11-09', 'Huh, nehéz film. Mély témákat boncolgat, az ember elgondolkozik utána ő mit tett volna egy ilyen helyzetben.', 3.5, 6, 2),
	(4, '2025-09-18', 'Imádom az egész trilógiát, a könyveket is olvastam. Aki szereti a Squid Game-et, annak ez is tetszeni fog.', 4.5, 2, 3);

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;





/* Lekérdezések */



/* 

1. Összes film és a műfajaik 

SELECT 
film.cim,
mufajok.nev AS műfaj
FROM film
JOIN film_mufaj ON film.film_id = film_mufaj.film_id
JOIN mufajok ON film_mufaj.mufaj_id = mufajok.mufaj_id
ORDER BY film.film_id, mufajok.nev;


2. 1 film összes adata

SELECT 
film.film_id,
film.cim,
film.idotartam,
film.kiadasi_ev,
film.leiras,
film.poszter_url, 
rendezok.nev AS Rendező,
szineszek.nev AS Színészek,
orszagok.nev AS Ország,
velemenyek.letrehozas_ideje,
velemenyek.komment,
velemenyek.ertekeles,
felhasznalo.felhasznalonev
FROM film
JOIN film_rendezok ON film.film_id = film_rendezok.film_id
JOIN rendezok ON rendezok.rendezo_id = film_rendezok.rendezo_id
JOIN film_szineszek ON film.film_id = film_szineszek.film_id
JOIN szineszek ON szineszek.szinesz_id = film_szineszek.szinesz_id
JOIN film_orszagok ON film.film_id = film_orszagok.film_id
JOIN orszagok ON film_orszagok.orszag_id = orszagok.orszag_id
JOIN velemenyek ON film.film_id = velemenyek.film_id
JOIN felhasznalo ON velemenyek.felhasznalo_id = felhasznalo.felhasznalo_id
WHERE film.film_id = 2;



3. 1 színész összes filmje

SELECT 
film.cim,
film.kiadasi_ev
FROM film
JOIN film_szineszek ON film.film_id = film_szineszek.film_id
JOIN szineszek ON film_szineszek.szinesz_id = szineszek.szinesz_id
WHERE szineszek.nev = 'Anya Taylor-Joy';

4. Egy műfajba tartozó filmek listája

SELECT 
film.cim
FROM film
JOIN film_mufaj ON film.film_id = film_mufaj.film_id
JOIN mufajok ON film_mufaj.mufaj_id = mufajok.mufaj_id
WHERE mufajok.nev = 'Sci-fi';


5. 1 műfajban mennyi film van

SELECT mufajok.nev AS műfaj, COUNT(film.cim) AS filmek_száma
FROM film
JOIN film_mufaj ON film.film_id = film_mufaj.film_id
JOIN mufajok ON film_mufaj.mufaj_id = mufajok.mufaj_id
GROUP BY mufajok.nev;

6.  1 felhasználó összes értékelése

SELECT 
film.cim,
velemenyek.komment,
velemenyek.ertekeles,
felhasznalo.felhasznalonev
FROM velemenyek
JOIN film ON velemenyek.film_id = film.film_id
JOIN felhasznalo ON velemenyek.felhasznalo_id = felhasznalo.felhasznalo_id
WHERE felhasznalo.felhasznalonev = 'Kira';

7. filmek listázása megjelenési évvel

SELECT 
film.cim,
film.kiadasi_ev
FROM film;

8. 1 rendező összes filmje

SELECT 
film.cim,
film.kiadasi_ev AS megjelenési_év,
rendezok.nev AS rendező
FROM film
JOIN film_rendezok ON film.film_id = film_rendezok.film_id
JOIN rendezok ON film_rendezok.rendezo_id = rendezok.rendezo_id
WHERE rendezok.nev = 'Guillermo del Toro'
ORDER BY film.kiadasi_ev DESC;

9. Legjobb értékelésű filmek

SELECT 
film.cim,
ROUND(AVG(velemenyek.ertekeles), 2) AS Átlag_értékelés,
COUNT(velemenyek.velemeny_id) AS Vélemények_száma
FROM film
JOIN velemenyek ON film.film_id = velemenyek.film_id
GROUP BY film.film_id
ORDER BY Átlag_értékelés DESC, Vélemények_száma DESC
LIMIT 4;



10. Legaktívabb felhasználók (vélemények alapján)

SELECT 
felhasznalo.felhasznalonev AS Felhasználó,
COUNT(velemenyek.velemeny_id) AS Vélemények_száma
FROM felhasznalo
JOIN velemenyek ON felhasznalo.felhasznalo_id = velemenyek.felhasznalo_id
GROUP BY felhasznalo.felhasznalo_id
ORDER BY Vélemények_száma DESC
LIMIT 10;

11. Legnépszerűbb műfajok

SELECT 
mufajok.nev AS Műfaj,
COUNT(film_mufaj.film_id) AS Filmek_száma
FROM mufajok
JOIN film_mufaj ON mufajok.mufaj_id = film_mufaj.mufaj_id
GROUP BY mufajok.mufaj_id
ORDER BY Filmek_száma DESC;

12. 1 országban gyártott filmek

SELECT 
film.cim,
orszagok.nev
FROM film
JOIN film_orszagok ON film.film_id = film_orszagok.film_id
JOIN orszagok ON film_orszagok.orszag_id = orszagok.orszag_id
WHERE orszagok.nev = 'Amerikai Egyesült Államok'
ORDER BY film.kiadasi_ev DESC;

13. Filmek megjelenése időrendben

SELECT 
film.cim,
film.kiadasi_ev
FROM film
ORDER BY film.kiadasi_ev DESC;

14. Film keresés cím alapján

SELECT 
film.cim,
film.kiadasi_ev
FROM film
WHERE film.cim LIKE '%Gran%'
ORDER BY film.kiadasi_ev DESC;

*/


