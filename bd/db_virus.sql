-- phpMyAdmin SQL Dump
-- version 4.0.10deb1
-- http://www.phpmyadmin.net
--
-- Servidor: localhost
-- Tiempo de generación: 16-04-2015 a las 09:34:17
-- Versión del servidor: 5.5.41-0ubuntu0.14.04.1
-- Versión de PHP: 5.5.9-1ubuntu4.7

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de datos: `db_virus`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `amenaza`
--

CREATE TABLE IF NOT EXISTS `amenaza` (
  `nombre` varchar(50) NOT NULL,
  `autor` varchar(50) NOT NULL,
  `tipo` varchar(100) NOT NULL,
  `fechapvo` varchar(10) NOT NULL,
  `fechaodp` varchar(10) NOT NULL,
  `descripcion` text NOT NULL,
  `malware` text NOT NULL,
  `parche` text NOT NULL,
  `analisisvt` varchar(150) NOT NULL,
  `firma` varchar(64) NOT NULL,
  `referencias` text NOT NULL,
  `observaciones` text NOT NULL,
  `riesgo` int(2) NOT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `archivos`
--

CREATE TABLE IF NOT EXISTS `archivos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `md5` varchar(32) NOT NULL,
  `ruta` varchar(150) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cliente`
--

CREATE TABLE IF NOT EXISTS `cliente` (
  `idcliente` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `empresa` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `telefono` varchar(9) NOT NULL,
  PRIMARY KEY (`idcliente`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `host`
--

CREATE TABLE IF NOT EXISTS `host` (
  `idhost` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `url` varchar(256) NOT NULL,
  `ip` varchar(256) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(256) NOT NULL,
  `idcliente` int(11) NOT NULL,
  PRIMARY KEY (`idhost`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE IF NOT EXISTS `usuario` (
  `iduser` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(256) NOT NULL,
  PRIMARY KEY (`iduser`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`iduser`, `nombre`, `username`, `password`) VALUES
(2, 'Administrador', 'admin', 'fa96dc16accdab3bcd3ef7edff2c75070881fb95');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
