-- phpMyAdmin SQL Dump
-- Sınav Görevleri Takip Sistemi
-- Oluşturma Tarihi: 28 Ocak 2024

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Veritabanı: `osmanak1_takip`
--
CREATE DATABASE IF NOT EXISTS `osmanak1_takip` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci;
USE `osmanak1_takip`;

-- --------------------------------------------------------

--
-- Tablo yapısı: `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- --------------------------------------------------------

--
-- Tablo yapısı: `sinav_sablonlari`
--

CREATE TABLE `sinav_sablonlari` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sinav_adi` varchar(255) NOT NULL,
  `gozetmen_ucret` decimal(10,2) NOT NULL,
  `yedek_ucret` decimal(10,2) NOT NULL,
  `baskan_ucret` decimal(10,2) NOT NULL,
  `basvuru_link` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

--
-- Tablo döküm verisi `sinav_sablonlari`
--

INSERT INTO `sinav_sablonlari` (`sinav_adi`, `gozetmen_ucret`, `yedek_ucret`, `baskan_ucret`, `basvuru_link`) VALUES
('ALES (Akademik Personel ve Lisansüstü Eğitimi Giriş Sınavı)', 750.00, 375.00, 900.00, 'https://ais.osym.gov.tr'),
('YDS (Yabancı Dil Sınavı)', 700.00, 350.00, 850.00, 'https://ais.osym.gov.tr'),
('KPSS (Kamu Personel Seçme Sınavı)', 800.00, 400.00, 950.00, 'https://ais.osym.gov.tr'),
('YKS (Yükseköğretim Kurumları Sınavı)', 850.00, 425.00, 1000.00, 'https://ais.osym.gov.tr');

-- --------------------------------------------------------

--
-- Tablo yapısı: `sinavlar`
--

CREATE TABLE `sinavlar` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sablon_id` int(11) DEFAULT NULL,
  `sinav_tarihi` datetime NOT NULL,
  `son_basvuru_tarihi` datetime NOT NULL,
  `aktif` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `sablon_id` (`sablon_id`),
  CONSTRAINT `sinavlar_ibfk_1` FOREIGN KEY (`sablon_id`) REFERENCES `sinav_sablonlari` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */; 