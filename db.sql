-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Anamakine: 127.0.0.1
-- Üretim Zamanı: 30 Oca 2024, 11:54:54
-- Sunucu sürümü: 10.4.32-MariaDB
-- PHP Sürümü: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Veritabanı: `ali`
--

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `carihareket`
--

CREATE TABLE `carihareket` (
  `id` varchar(36) NOT NULL DEFAULT uuid(),
  `customerid` varchar(36) NOT NULL,
  `HareketTarih` datetime NOT NULL,
  `HareketAciklama` varchar(255) NOT NULL,
  `HareketTur` varchar(50) NOT NULL,
  `HareketBakiye` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `customer`
--

CREATE TABLE `customer` (
  `id` varchar(36) NOT NULL DEFAULT uuid(),
  `KisaAd` varchar(50) DEFAULT NULL,
  `Firma` varchar(250) DEFAULT NULL,
  `FirmaTel` varchar(11) DEFAULT NULL,
  `FirmaEmail` varchar(150) DEFAULT NULL,
  `FirmaTur` varchar(50) DEFAULT NULL,
  `FirmaVergiNo` varchar(50) DEFAULT NULL,
  `FirmaVD` varchar(250) DEFAULT NULL,
  `FirmaAdres` text DEFAULT NULL,
  `EklenmeTarihi` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `kullanicilar`
--

CREATE TABLE `kullanicilar` (
  `id` varchar(36) NOT NULL DEFAULT uuid(),
  `Eposta` varchar(50) NOT NULL,
  `Isim` varchar(50) NOT NULL,
  `KullaniciAdi` varchar(50) NOT NULL,
  `Sifre` varchar(150) NOT NULL,
  `SonGiris` datetime DEFAULT NULL,
  `2FA` int(11) DEFAULT 1,
  `Telefon` varchar(10) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `kullanicilar`
--

INSERT INTO `kullanicilar` (`id`, `Eposta`, `Isim`, `KullaniciAdi`, `Sifre`, `SonGiris`, `2FA`, `Telefon`) VALUES
('7e747f5d-bf51-11ee-a231-50a13236bf9c', 'fatihyuzuguldu@icloud.com', 'Fatih Yüzügüldü', '123', '262c90b2c7d9a25950f181caf133965ec1333a74bd75d483fa5ea2ef8f11f740', '2024-01-30 12:28:28', 3, '5467422716');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `sms`
--

CREATE TABLE `sms` (
  `id` int(11) NOT NULL,
  `response` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dökümü yapılmış tablolar için indeksler
--

--
-- Tablo için indeksler `carihareket`
--
ALTER TABLE `carihareket`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `customerid` (`customerid`) USING BTREE;

--
-- Tablo için indeksler `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- Tablo için indeksler `kullanicilar`
--
ALTER TABLE `kullanicilar`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `sms`
--
ALTER TABLE `sms`
  ADD PRIMARY KEY (`id`);

--
-- Dökümü yapılmış tablolar için AUTO_INCREMENT değeri
--

--
-- Tablo için AUTO_INCREMENT değeri `sms`
--
ALTER TABLE `sms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- Dökümü yapılmış tablolar için kısıtlamalar
--

--
-- Tablo kısıtlamaları `carihareket`
--
ALTER TABLE `carihareket`
  ADD CONSTRAINT `carihareket_ibfk_1` FOREIGN KEY (`customerid`) REFERENCES `customer` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
