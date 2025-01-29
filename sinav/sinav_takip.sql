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

--
-- Tablo döküm verisi `admin`
--

INSERT INTO `admin` (`username`, `password`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

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

-- --------------------------------------------------------

--
-- Tablo yapısı: `ucretler`
--

CREATE TABLE `ucretler` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sinav_turu` varchar(255) NOT NULL,
  `gozetmen_ucret` decimal(10,2) NOT NULL,
  `yedek_ucret` decimal(10,2) NOT NULL,
  `baskan_ucret` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

--
-- Tablo döküm verisi `ucretler`
--

INSERT INTO `ucretler` (`sinav_turu`, `gozetmen_ucret`, `yedek_ucret`, `baskan_ucret`) VALUES
('KPSS', 800.00, 400.00, 950.00),
('YKS', 850.00, 425.00, 1000.00),
('ALES', 750.00, 375.00, 900.00),
('YDS', 700.00, 350.00, 850.00);

--
-- Tablo yapısı: `sinav_takvimi`
--

CREATE TABLE `sinav_takvimi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sinav_turu` varchar(255) NOT NULL,
  `tarih` date NOT NULL,
  `aciklama` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

--
-- Tablo döküm verisi `sinav_takvimi`
--

INSERT INTO `sinav_takvimi` (`sinav_turu`, `tarih`, `aciklama`) VALUES
('ALES', '2024-05-05', '2024 ALES İlkbahar Dönemi'),
('YDS', '2024-04-07', '2024 YDS İlkbahar Dönemi'),
('KPSS', '2024-07-14', '2024 KPSS Lisans'),
('YKS', '2024-06-15', '2024 YKS 1. Oturum (TYT)');

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */; 