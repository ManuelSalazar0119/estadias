-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3307
-- Tiempo de generación: 20-02-2026 a las 19:12:52
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `cefppenay`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `actividades`
--

CREATE TABLE `actividades` (
  `id_actividad` int(11) NOT NULL,
  `nombre_actividad` varchar(100) NOT NULL,
  `descripcion_actividad` text DEFAULT NULL,
  `activo_actividad` tinyint(1) DEFAULT 1,
  `id_area` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `actividades`
--

INSERT INTO `actividades` (`id_actividad`, `nombre_actividad`, `descripcion_actividad`, `activo_actividad`, `id_area`) VALUES
(8, 'PCC', 'RealizaciÃ³n de Pruebas Cervicales Comparativas', 1, 1),
(9, 'Buffer EPN1', 'RealizaciÃ³n de Prueba Anual en Zona de Amortiguamiento (EPN1)', 1, 1),
(10, 'Buffer Control', 'RealizaciÃ³n de prueba anual en zona de amortiguamiento (CONTROL)', 1, 1),
(11, 'toma de muestras', 'toma de muestras ', 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `areas`
--

CREATE TABLE `areas` (
  `id_area` int(11) NOT NULL,
  `nombre_area` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `areas`
--

INSERT INTO `areas` (`id_area`, `nombre_area`) VALUES
(1, 'Tuberculosis'),
(2, 'Brucelosis');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `campos_actividad`
--

CREATE TABLE `campos_actividad` (
  `id_camposA` int(11) NOT NULL,
  `id_actividad` int(11) NOT NULL,
  `nombre_campo_actividad` varchar(100) NOT NULL,
  `tipo_campo_actividad` enum('texto','numero','fecha','lista') NOT NULL,
  `obligatorio_actividad` tinyint(1) DEFAULT 0,
  `opciones_lista_actividad` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `campos_actividad`
--

INSERT INTO `campos_actividad` (`id_camposA`, `id_actividad`, `nombre_campo_actividad`, `tipo_campo_actividad`, `obligatorio_actividad`, `opciones_lista_actividad`) VALUES
(19, 8, 'Recurso', 'lista', 0, 'FED,EST'),
(20, 8, 'CLAVE', 'texto', 1, NULL),
(21, 8, 'PREDIO', 'texto', 1, NULL),
(22, 8, 'UPP', 'texto', 0, NULL),
(23, 8, 'NOMBRE DEL BENEFICIARIO', 'texto', 1, NULL),
(24, 8, 'MUNICIPIO', 'texto', 0, NULL),
(25, 8, 'LOCALIDAD', 'texto', 0, NULL),
(26, 8, 'POBLACION', 'numero', 0, NULL),
(27, 8, 'SEMENTALES', 'numero', 0, NULL),
(28, 8, 'VACAS', 'numero', 0, NULL),
(29, 8, 'VAQUILLAS', 'numero', 0, NULL),
(30, 8, 'BECERROS', 'numero', 0, NULL),
(31, 8, 'BECERRAS', 'numero', 0, NULL),
(32, 8, 'FIN ZOOTECNICO', 'lista', 0, 'CARNE,LECHE,MIXTO'),
(33, 8, 'FECHA', 'fecha', 0, NULL),
(34, 8, 'PROBADOS', 'numero', 0, NULL),
(35, 8, 'NEGATIVOS PCC', 'numero', 0, NULL),
(36, 8, 'SOSPECHOSOS PCC', 'numero', 0, NULL),
(37, 8, 'REACTORES PCC', 'numero', 0, NULL),
(38, 8, 'FECHA INYECCION', 'fecha', 0, NULL),
(39, 8, 'FECHA LECTURA', 'fecha', 0, NULL),
(40, 8, 'LATITUD', 'texto', 0, NULL),
(41, 8, 'LONGITUD', 'texto', 0, NULL),
(42, 8, 'ALTITUD', 'texto', 0, NULL),
(43, 8, 'OBSERVACIONES', 'texto', 0, NULL),
(44, 9, 'Recurso', 'lista', 0, 'FED,EST'),
(45, 9, 'CLAVE', 'texto', 1, NULL),
(46, 9, 'PREDIO', 'texto', 1, NULL),
(47, 9, 'UPP', 'texto', 0, NULL),
(48, 9, 'NOMBRE DEL BENEFICIARIO', 'texto', 1, NULL),
(49, 9, 'MUNICIPIO', 'texto', 0, NULL),
(50, 9, 'LOCALIDAD', 'texto', 0, NULL),
(51, 9, 'POBLACION', 'numero', 0, NULL),
(52, 9, 'SEMENTALES', 'numero', 0, NULL),
(53, 9, 'VACAS', 'numero', 0, NULL),
(54, 9, 'VAQUILLAS', 'numero', 0, NULL),
(55, 9, 'BECERROS', 'numero', 0, NULL),
(56, 9, 'BECERRAS', 'numero', 0, NULL),
(57, 9, 'FIN ZOOTECNICO', 'lista', 0, 'CARNE,LECHE,MIXTO'),
(58, 9, 'FECHA', 'fecha', 0, NULL),
(59, 9, 'PROBADOS', 'numero', 0, NULL),
(60, 9, 'NEGATIVOS', 'numero', 0, NULL),
(61, 9, 'SOSPECHOSOS', 'numero', 0, NULL),
(62, 9, 'REACTORES', 'numero', 0, NULL),
(63, 9, 'FECHA INYECCION', 'fecha', 0, NULL),
(64, 9, 'FECHA LECTURA', 'fecha', 0, NULL),
(65, 9, 'LATITUD', 'texto', 0, NULL),
(66, 9, 'LONGITUD', 'texto', 0, NULL),
(67, 9, 'ALTITUD', 'texto', 0, NULL),
(68, 9, 'OBSERVACIONES', 'texto', 0, NULL),
(69, 10, 'Recurso', 'lista', 0, 'FED,EST'),
(70, 10, 'CLAVE', 'texto', 1, NULL),
(71, 10, 'PREDIO', 'texto', 1, NULL),
(72, 10, 'UPP', 'texto', 0, NULL),
(73, 10, 'NOMBRE DEL BENEFICIARIO', 'texto', 1, NULL),
(74, 10, 'MUNICIPIO', 'texto', 0, NULL),
(75, 10, 'LOCALIDAD', 'texto', 0, NULL),
(76, 10, 'POBLACION', 'numero', 0, NULL),
(77, 10, 'SEMENTALES', 'numero', 0, NULL),
(78, 10, 'VACAS', 'numero', 0, NULL),
(79, 10, 'VAQUILLAS', 'numero', 0, NULL),
(80, 10, 'BECERROS', 'numero', 0, NULL),
(81, 10, 'BECERRAS', 'numero', 0, NULL),
(82, 10, 'FIN ZOOTECNICO', 'lista', 0, 'CARNE,LECHE,MIXTO'),
(83, 10, 'FECHA', 'fecha', 0, NULL),
(84, 10, 'PROBADOS', 'numero', 0, NULL),
(85, 10, 'NEGATIVOS', 'numero', 0, NULL),
(86, 10, 'SOSPECHOSOS', 'numero', 0, NULL),
(87, 10, 'REACTORES', 'numero', 0, NULL),
(88, 10, 'FECHA INYECCION', 'fecha', 0, NULL),
(89, 10, 'FECHA LECTURA', 'fecha', 0, NULL),
(90, 10, 'LATITUD', 'texto', 0, NULL),
(91, 10, 'LONGITUD', 'texto', 0, NULL),
(92, 10, 'ALTITUD', 'texto', 0, NULL),
(93, 10, 'OBSERVACIONES', 'texto', 0, NULL),
(94, 11, 'toro, 1, nada de observacion', 'texto', 1, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `grupos_actividades`
--

CREATE TABLE `grupos_actividades` (
  `id` int(11) NOT NULL,
  `nombre_grupo` varchar(255) NOT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `grupos_actividades`
--

INSERT INTO `grupos_actividades` (`id`, `nombre_grupo`, `creado_en`) VALUES
(1, 'Grupo Cervicales', '2025-09-03 16:20:00'),
(2, 'Grupo Envios', '2025-09-03 16:20:23'),
(3, 'Grupo Buffer Control', '2025-09-03 21:41:56'),
(4, 'Grupo EPN1', '2025-09-05 16:52:27');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `grupo_actividad_detalle`
--

CREATE TABLE `grupo_actividad_detalle` (
  `id` int(11) NOT NULL,
  `id_grupo` int(11) NOT NULL,
  `id_actividad` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `grupo_actividad_detalle`
--

INSERT INTO `grupo_actividad_detalle` (`id`, `id_grupo`, `id_actividad`) VALUES
(1, 1, 8),
(2, 3, 10),
(3, 4, 9);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `justificaciones_mensuales`
--

CREATE TABLE `justificaciones_mensuales` (
  `id` int(11) NOT NULL,
  `id_grupo` int(11) NOT NULL,
  `unidad` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `anio` int(11) NOT NULL,
  `mes` int(11) NOT NULL,
  `justificacion` text NOT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `actualizado_en` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `justificaciones_mensuales`
--

INSERT INTO `justificaciones_mensuales` (`id`, `id_grupo`, `unidad`, `anio`, `mes`, `justificacion`, `creado_en`, `actualizado_en`) VALUES
(1, 1, '', 2025, 7, 'Tb cervicales', '2025-09-03 16:51:25', '2025-09-03 21:47:38'),
(13, 3, '', 2025, 7, 'Nueva', '2025-09-03 21:42:52', '2025-09-03 21:47:38'),
(57, 4, 'cabezas', 2025, 8, 'uumm modificadop2', '2025-09-05 22:59:53', '2025-09-05 23:00:23'),
(58, 4, 'unidades de producción', 2025, 8, 'ummm2', '2025-09-05 22:59:53', '2025-09-05 23:00:23'),
(61, 3, 'cabezas', 2025, 7, 'avers1', '2025-09-05 23:00:49', '2025-09-05 23:00:49'),
(62, 3, 'unidades de producción', 2025, 7, 'avers2', '2025-09-05 23:00:49', '2025-09-05 23:00:49'),
(63, 1, 'cabezas', 2025, 7, 'avers3', '2025-09-05 23:00:49', '2025-09-05 23:00:49'),
(64, 1, 'unidades de producción', 2025, 7, 'avers4', '2025-09-05 23:00:49', '2025-09-05 23:00:49');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `municipios`
--

CREATE TABLE `municipios` (
  `id_municipio` int(11) NOT NULL,
  `id_zona` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `municipios`
--

INSERT INTO `municipios` (`id_municipio`, `id_zona`, `nombre`) VALUES
(2, 2, 'Amatlán de Cañas'),
(3, 2, 'Compostela'),
(4, 1, 'Huajicori'),
(5, 2, 'Ixtlán del Río'),
(6, 2, 'Jala'),
(7, 2, 'Xalisco'),
(8, 1, 'Del Nayar'),
(9, 1, 'Rosamorada'),
(10, 1, 'Ruiz'),
(11, 1, 'San Blas'),
(12, 2, 'San Pedro Lagunillas'),
(13, 2, 'Santa María del Oro'),
(14, 1, 'Santiago Ixcuintla'),
(15, 1, 'Tecuala'),
(16, 2, 'Tepic'),
(17, 1, 'Tuxpan'),
(18, 1, 'La Yesca'),
(19, 2, 'Bahía de Banderas'),
(20, 1, 'Acaponeta'),
(21, 2, 'Ahuacatlán');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `programacion_anual`
--

CREATE TABLE `programacion_anual` (
  `id` int(11) NOT NULL,
  `id_actividad` int(11) NOT NULL,
  `anio` int(11) NOT NULL,
  `programado` int(11) NOT NULL,
  `unidad` varchar(50) DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `actualizado_en` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `programacion_anual`
--

INSERT INTO `programacion_anual` (`id`, `id_actividad`, `anio`, `programado`, `unidad`, `creado_en`, `actualizado_en`) VALUES
(1, 8, 2025, 150, 'Cabezas', '2025-09-02 20:24:06', '2025-09-02 20:24:06'),
(3, 10, 2025, 177, 'Cabezas', '2025-09-15 15:34:09', '2025-09-15 15:34:09'),
(4, 10, 2025, 56, 'Unidades de Producción', '2025-09-15 15:34:26', '2025-09-15 15:34:26');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `programacion_mensual`
--

CREATE TABLE `programacion_mensual` (
  `id` int(11) NOT NULL,
  `id_actividad` int(11) NOT NULL,
  `anio` int(11) NOT NULL,
  `mes` int(11) NOT NULL,
  `programado` int(11) NOT NULL,
  `unidad` varchar(50) DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `actualizado_en` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `programacion_mensual`
--

INSERT INTO `programacion_mensual` (`id`, `id_actividad`, `anio`, `mes`, `programado`, `unidad`, `creado_en`, `actualizado_en`) VALUES
(3, 8, 2025, 7, 777, 'Cabezas', '2025-09-02 16:12:51', '2025-09-03 15:22:07'),
(16, 8, 2025, 7, 52, 'Unidades de Producción', '2025-09-02 17:56:22', '2025-09-02 20:42:42'),
(23, 10, 2025, 7, 12, 'Unidades de Producción', '2025-09-02 22:06:05', '2025-09-02 22:06:05'),
(24, 10, 2025, 7, 77, 'Cabezas', '2025-09-02 22:06:28', '2025-09-02 22:06:28'),
(31, 9, 2025, 8, 120, 'Cabezas', '2025-09-05 16:50:06', '2025-09-05 16:51:00'),
(32, 9, 2025, 8, 3, 'Unidades de Producción', '2025-09-05 16:51:21', '2025-09-05 16:51:21'),
(33, 10, 2025, 9, 2, 'Unidades de Producción', '2025-09-08 16:28:26', '2025-09-09 21:02:47'),
(35, 8, 2025, 9, 2323, 'Unidades de Producción', '2025-09-08 16:29:14', '2025-09-08 16:29:14'),
(37, 8, 2025, 9, 170, 'Cabezas', '2025-09-08 19:15:00', '2025-09-08 19:15:00'),
(42, 9, 2025, 7, 14, 'Unidades de Producción', '2025-09-09 22:30:20', '2025-09-09 22:30:20'),
(45, 9, 2025, 7, 78, 'Cabezas', '2025-09-09 22:30:36', '2025-09-09 22:30:36'),
(48, 10, 2025, 9, 50, 'Cabezas', '2026-01-14 18:47:26', '2026-01-14 18:47:26');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `registros_actividad`
--

CREATE TABLE `registros_actividad` (
  `id_registro` int(11) NOT NULL,
  `id_actividad` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `fecha_registro` date NOT NULL,
  `id_zona` int(11) DEFAULT NULL,
  `id_municipio` int(11) DEFAULT NULL,
  `observaciones_registro` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `registros_actividad`
--

INSERT INTO `registros_actividad` (`id_registro`, `id_actividad`, `id_usuario`, `fecha_registro`, `id_zona`, `id_municipio`, `observaciones_registro`) VALUES
(7, 8, 1, '2025-08-19', NULL, NULL, NULL),
(8, 8, 1, '2025-07-16', 1, 9, ''),
(9, 8, 1, '2025-08-20', 1, 9, ''),
(10, 8, 1, '2025-08-20', 1, 8, ''),
(11, 9, 1, '2025-08-20', 1, 2, ''),
(12, 9, 1, '2025-08-20', 1, 5, ''),
(13, 9, 1, '2025-08-22', 1, 3, ''),
(14, 8, 1, '2025-08-22', 2, 12, ''),
(15, 8, 1, '2025-09-08', 1, 5, ''),
(16, 10, 1, '2025-09-09', 1, 2, ''),
(17, 10, 1, '2025-09-09', 2, 5, ''),
(18, 9, 1, '2025-07-09', 1, 2, ''),
(19, 10, 1, '2025-07-12', 1, 2, '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `registro_pdfs`
--

CREATE TABLE `registro_pdfs` (
  `id_pdf` int(11) NOT NULL,
  `id_registro` int(11) NOT NULL,
  `ruta_pdf` varchar(255) NOT NULL,
  `fecha_subida` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `registro_pdfs`
--

INSERT INTO `registro_pdfs` (`id_pdf`, `id_registro`, `ruta_pdf`, `fecha_subida`) VALUES
(1, 13, 'registro_13_1755899445.pdf', '2025-08-22 21:50:45'),
(2, 14, 'registro_14_1755902514.pdf', '2025-08-22 22:41:54'),
(3, 15, 'registro_15_1757358824.pdf', '2025-09-08 19:13:44'),
(4, 16, 'registro_16_1757451402.pdf', '2025-09-09 20:56:42'),
(5, 17, 'registro_17_1757452100.pdf', '2025-09-09 21:08:20'),
(6, 18, 'registro_18_1757456987.pdf', '2025-09-09 22:29:47'),
(7, 19, 'registro_19_1757717094.pdf', '2025-09-12 22:44:54');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `nombre_usuario` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `rol_usuario` enum('Administrador','Médico','Coordinador','Consulta') NOT NULL,
  `activo_usuario` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `nombre_usuario`, `email`, `password_hash`, `rol_usuario`, `activo_usuario`) VALUES
(1, 'Adalberto', 'gerencia@cefppenay.com', '$2y$10$4GYc1RBS0eC1QGjP8MYBBOEmsxIL4xhRzo/1f5.TOINImtQuLOs1K', 'Administrador', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `valores_actividad`
--

CREATE TABLE `valores_actividad` (
  `id_valores` int(11) NOT NULL,
  `id_registro` int(11) NOT NULL,
  `id_camposA` int(11) NOT NULL,
  `valor` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `valores_actividad`
--

INSERT INTO `valores_actividad` (`id_valores`, `id_registro`, `id_camposA`, `valor`) VALUES
(14, 7, 19, ''),
(15, 7, 20, ''),
(16, 7, 21, ''),
(17, 7, 22, ''),
(18, 7, 23, ''),
(19, 7, 24, ''),
(20, 7, 25, ''),
(21, 7, 26, ''),
(22, 7, 27, ''),
(23, 7, 28, ''),
(24, 7, 29, ''),
(25, 7, 30, ''),
(26, 7, 31, ''),
(27, 7, 32, ''),
(28, 7, 33, ''),
(29, 7, 34, ''),
(30, 7, 35, ''),
(31, 7, 36, ''),
(32, 7, 37, ''),
(33, 7, 38, ''),
(34, 7, 39, ''),
(35, 7, 40, ''),
(36, 7, 41, ''),
(37, 7, 42, ''),
(38, 7, 43, ''),
(39, 8, 19, 'FED'),
(40, 8, 20, '1'),
(41, 8, 21, 'Los Colomos'),
(42, 8, 22, '12334503500'),
(43, 8, 23, 'Jholaus Enrique'),
(44, 8, 24, 'Compostela'),
(45, 8, 25, 'Los Colomos'),
(46, 8, 26, '50'),
(47, 8, 27, '5'),
(48, 8, 28, '40'),
(49, 8, 29, '3'),
(50, 8, 30, '2'),
(51, 8, 31, '0'),
(52, 8, 32, 'CARNE'),
(53, 8, 33, '2025-08-20'),
(54, 8, 34, '10'),
(55, 8, 35, '8'),
(56, 8, 36, '1'),
(57, 8, 37, '1'),
(58, 8, 38, '2025-08-20'),
(59, 8, 39, '2025-08-23'),
(60, 8, 40, '24.02329424'),
(61, 8, 41, '-104.03403492340'),
(62, 8, 42, ''),
(63, 8, 43, ''),
(64, 9, 19, 'FED'),
(65, 9, 20, '1'),
(66, 9, 21, 'Las Marianas'),
(67, 9, 22, '3424284320'),
(68, 9, 23, 'Juan Lopez'),
(69, 9, 24, 'Rosamorada'),
(70, 9, 25, 'Los Colomos'),
(71, 9, 26, '9'),
(72, 9, 27, '1'),
(73, 9, 28, '8'),
(74, 9, 29, '0'),
(75, 9, 30, '0'),
(76, 9, 31, '0'),
(77, 9, 32, 'CARNE'),
(78, 9, 33, '2025-08-20'),
(79, 9, 34, '5'),
(80, 9, 35, '5'),
(81, 9, 36, '0'),
(82, 9, 37, '0'),
(83, 9, 38, '2025-08-20'),
(84, 9, 39, '2025-08-20'),
(85, 9, 40, '24.02329424'),
(86, 9, 41, '-104.03403492340'),
(87, 9, 42, ''),
(88, 9, 43, ''),
(89, 10, 19, 'FED'),
(90, 10, 20, '2'),
(91, 10, 21, 'Sacualpan'),
(92, 10, 22, '23492349234'),
(93, 10, 23, 'Alfonso'),
(94, 10, 24, 'Del Nayar'),
(95, 10, 25, 'El chilillo'),
(96, 10, 26, '15'),
(97, 10, 27, '1'),
(98, 10, 28, '10'),
(99, 10, 29, '2'),
(100, 10, 30, '1'),
(101, 10, 31, '1'),
(102, 10, 32, 'CARNE'),
(103, 10, 33, '2025-08-20'),
(104, 10, 34, '5'),
(105, 10, 35, '5'),
(106, 10, 36, '0'),
(107, 10, 37, '0'),
(108, 10, 38, '2025-08-20'),
(109, 10, 39, '2025-08-23'),
(110, 10, 40, '24.0450540450'),
(111, 10, 41, '-104.2382382323'),
(112, 10, 42, ''),
(113, 10, 43, ''),
(114, 11, 44, 'FED'),
(115, 11, 45, '1'),
(116, 11, 46, 'Culiacancito'),
(117, 11, 47, '384237424'),
(118, 11, 48, 'Maria Antonia'),
(119, 11, 49, 'Amatlán de Cañas'),
(120, 11, 50, 'Culiacancillo'),
(121, 11, 51, '100'),
(122, 11, 52, '5'),
(123, 11, 53, '80'),
(124, 11, 54, '5'),
(125, 11, 55, '5'),
(126, 11, 56, '5'),
(127, 11, 57, 'CARNE'),
(128, 11, 58, '2025-08-20'),
(129, 11, 59, '50'),
(130, 11, 60, '50'),
(131, 11, 61, '0'),
(132, 11, 62, '0'),
(133, 11, 63, '2025-08-20'),
(134, 11, 64, '2025-08-23'),
(135, 11, 65, ''),
(136, 11, 66, ''),
(137, 11, 67, ''),
(138, 11, 68, ''),
(139, 12, 44, 'FED'),
(140, 12, 45, '1'),
(141, 12, 46, 'dfgdfg'),
(142, 12, 47, '3434534534534'),
(143, 12, 48, 'dgdfgdfgdfg'),
(144, 12, 49, 'Amatlán de Cañas'),
(145, 12, 50, 'dfgdfgd'),
(146, 12, 51, '100'),
(147, 12, 52, '5'),
(148, 12, 53, '80'),
(149, 12, 54, '5'),
(150, 12, 55, '5'),
(151, 12, 56, '5'),
(152, 12, 57, 'CARNE'),
(153, 12, 58, '2025-08-20'),
(154, 12, 59, '25'),
(155, 12, 60, '25'),
(156, 12, 61, '0'),
(157, 12, 62, '0'),
(158, 12, 63, '2025-08-20'),
(159, 12, 64, '2025-08-23'),
(160, 12, 65, ''),
(161, 12, 66, ''),
(162, 12, 67, ''),
(163, 12, 68, ''),
(164, 13, 44, 'FED'),
(165, 13, 45, '2'),
(166, 13, 46, 'sjdsjdsjd'),
(167, 13, 47, '23423423423'),
(168, 13, 48, 'Enrique Salazar'),
(169, 13, 49, 'Tepic'),
(170, 13, 50, 'Mora'),
(171, 13, 51, '100'),
(172, 13, 52, '4'),
(173, 13, 53, '50'),
(174, 13, 54, '30'),
(175, 13, 55, '6'),
(176, 13, 56, '10'),
(177, 13, 57, 'CARNE'),
(178, 13, 58, '2025-08-22'),
(179, 13, 59, '30'),
(180, 13, 60, '25'),
(181, 13, 61, '4'),
(182, 13, 62, '1'),
(183, 13, 63, '2025-08-22'),
(184, 13, 64, '2025-08-22'),
(185, 13, 65, '24.03403043'),
(186, 13, 66, '-104.493434834'),
(187, 13, 67, ''),
(188, 13, 68, ''),
(189, 14, 19, 'FED'),
(190, 14, 20, '3'),
(191, 14, 21, 'Culiacancito'),
(192, 14, 22, '42342342342'),
(193, 14, 23, 'Mariano Lopez'),
(194, 14, 24, 'Tepic'),
(195, 14, 25, 'Tepic'),
(196, 14, 26, '30'),
(197, 14, 27, '10'),
(198, 14, 28, '10'),
(199, 14, 29, '10'),
(200, 14, 30, '0'),
(201, 14, 31, '0'),
(202, 14, 32, 'CARNE'),
(203, 14, 33, '2025-08-22'),
(204, 14, 34, '20'),
(205, 14, 35, '20'),
(206, 14, 36, '0'),
(207, 14, 37, '0'),
(208, 14, 38, '2025-08-22'),
(209, 14, 39, '2025-08-25'),
(210, 14, 40, '24.834838384384'),
(211, 14, 41, '-104.34838438434'),
(212, 14, 42, ''),
(213, 14, 43, ''),
(214, 15, 19, 'FED'),
(215, 15, 20, '1'),
(216, 15, 21, 'aves'),
(217, 15, 22, '23823848234'),
(218, 15, 23, 'macarias'),
(219, 15, 24, 'Tecuala'),
(220, 15, 25, 'la loma'),
(221, 15, 26, '100'),
(222, 15, 27, '20'),
(223, 15, 28, '80'),
(224, 15, 29, '0'),
(225, 15, 30, '0'),
(226, 15, 31, '0'),
(227, 15, 32, 'CARNE'),
(228, 15, 33, '2025-09-08'),
(229, 15, 34, '170'),
(230, 15, 35, '48'),
(231, 15, 36, '2'),
(232, 15, 37, '0'),
(233, 15, 38, '2025-09-08'),
(234, 15, 39, '2025-09-11'),
(235, 15, 40, '24.023294343'),
(236, 15, 41, '-104.239238382'),
(237, 15, 42, ''),
(238, 15, 43, 'Ninguna'),
(239, 16, 69, 'FED'),
(240, 16, 70, '1'),
(241, 16, 71, 'sdfsdfsd'),
(242, 16, 72, '428428342834'),
(243, 16, 73, 'jsfddhffhfhfhf'),
(244, 16, 74, 'San blas'),
(245, 16, 75, 'san blas'),
(246, 16, 76, '100'),
(247, 16, 77, '90'),
(248, 16, 78, '1'),
(249, 16, 79, '8'),
(250, 16, 80, '1'),
(251, 16, 81, '0'),
(252, 16, 82, 'CARNE'),
(253, 16, 83, '2025-09-09'),
(254, 16, 84, '10'),
(255, 16, 85, '9'),
(256, 16, 86, '1'),
(257, 16, 87, '0'),
(258, 16, 88, '2025-09-09'),
(259, 16, 89, '2025-09-12'),
(260, 16, 90, '24.3493843843'),
(261, 16, 91, '-104.348387437434'),
(262, 16, 92, ''),
(263, 16, 93, ''),
(264, 17, 69, 'FED'),
(265, 17, 70, '2'),
(266, 17, 71, 'jsdfjdhfs'),
(267, 17, 72, '2349294282'),
(268, 17, 73, 'Ijilo'),
(269, 17, 74, 'mdfjdfjqmf'),
(270, 17, 75, 'msjjdfj'),
(271, 17, 76, '20'),
(272, 17, 77, '1'),
(273, 17, 78, '18'),
(274, 17, 79, '1'),
(275, 17, 80, '0'),
(276, 17, 81, '0'),
(277, 17, 82, 'CARNE'),
(278, 17, 83, '2025-09-09'),
(279, 17, 84, '12'),
(280, 17, 85, '11'),
(281, 17, 86, '0'),
(282, 17, 87, '1'),
(283, 17, 88, '2025-09-09'),
(284, 17, 89, '2025-09-12'),
(285, 17, 90, '24.3493843843'),
(286, 17, 91, '-104.348387437434'),
(287, 17, 92, ''),
(288, 17, 93, ''),
(289, 18, 44, 'FED'),
(290, 18, 45, '7'),
(291, 18, 46, 'sjfdshfhfshdffhs'),
(292, 18, 47, '273723472'),
(293, 18, 48, 'jfhsafydfh'),
(294, 18, 49, 'sdufsdufusd'),
(295, 18, 50, 'jdfhsdfhsd'),
(296, 18, 51, '30'),
(297, 18, 52, '1'),
(298, 18, 53, '26'),
(299, 18, 54, '3'),
(300, 18, 55, '0'),
(301, 18, 56, '0'),
(302, 18, 57, 'CARNE'),
(303, 18, 58, '2025-07-09'),
(304, 18, 59, '30'),
(305, 18, 60, '30'),
(306, 18, 61, '0'),
(307, 18, 62, '0'),
(308, 18, 63, '2025-07-09'),
(309, 18, 64, '2025-07-12'),
(310, 18, 65, '293238238'),
(311, 18, 66, '-104.283283283'),
(312, 18, 67, ''),
(313, 18, 68, ''),
(314, 19, 69, 'FED'),
(315, 19, 70, '3'),
(316, 19, 71, 'probandocambios'),
(317, 19, 72, '23742742374'),
(318, 19, 73, 'para excel'),
(319, 19, 74, 'sjsdjsdjd'),
(320, 19, 75, 'sdjsdjd'),
(321, 19, 76, '30'),
(322, 19, 77, '0'),
(323, 19, 78, '0'),
(324, 19, 79, '0'),
(325, 19, 80, '0'),
(326, 19, 81, '30'),
(327, 19, 82, 'CARNE'),
(328, 19, 83, '2025-07-31'),
(329, 19, 84, '15'),
(330, 19, 85, '0'),
(331, 19, 86, '0'),
(332, 19, 87, '0'),
(333, 19, 88, '2025-07-31'),
(334, 19, 89, '2025-07-03'),
(335, 19, 90, '24.3493843843'),
(336, 19, 91, '-104.348387437434'),
(337, 19, 92, ''),
(338, 19, 93, '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `zonas`
--

CREATE TABLE `zonas` (
  `id_zona` int(11) NOT NULL,
  `nombre_zona` varchar(100) NOT NULL,
  `codigo_zona` varchar(15) NOT NULL,
  `entidad_zona` varchar(30) NOT NULL,
  `descripcion_zona` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `zonas`
--

INSERT INTO `zonas` (`id_zona`, `nombre_zona`, `codigo_zona`, `entidad_zona`, `descripcion_zona`) VALUES
(1, 'Buffer Escasa Prevalencia Nivel 1', 'EPN1', 'Federal', 'Zona Buffer EPN1 Federal'),
(2, 'Buffer Control', 'Control', 'Estatal', 'Zona Buffer Control Estatal');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `actividades`
--
ALTER TABLE `actividades`
  ADD PRIMARY KEY (`id_actividad`);

--
-- Indices de la tabla `areas`
--
ALTER TABLE `areas`
  ADD PRIMARY KEY (`id_area`);

--
-- Indices de la tabla `campos_actividad`
--
ALTER TABLE `campos_actividad`
  ADD PRIMARY KEY (`id_camposA`),
  ADD KEY `id_actividad` (`id_actividad`);

--
-- Indices de la tabla `grupos_actividades`
--
ALTER TABLE `grupos_actividades`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `grupo_actividad_detalle`
--
ALTER TABLE `grupo_actividad_detalle`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_grupo` (`id_grupo`),
  ADD KEY `id_actividad` (`id_actividad`);

--
-- Indices de la tabla `justificaciones_mensuales`
--
ALTER TABLE `justificaciones_mensuales`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_justificacion` (`id_grupo`,`anio`,`mes`,`unidad`);

--
-- Indices de la tabla `municipios`
--
ALTER TABLE `municipios`
  ADD PRIMARY KEY (`id_municipio`),
  ADD KEY `id_zona` (`id_zona`);

--
-- Indices de la tabla `programacion_anual`
--
ALTER TABLE `programacion_anual`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_actividad` (`id_actividad`,`anio`,`unidad`);

--
-- Indices de la tabla `programacion_mensual`
--
ALTER TABLE `programacion_mensual`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_actividad` (`id_actividad`,`anio`,`mes`,`unidad`);

--
-- Indices de la tabla `registros_actividad`
--
ALTER TABLE `registros_actividad`
  ADD PRIMARY KEY (`id_registro`),
  ADD KEY `id_actividad` (`id_actividad`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_zona` (`id_zona`),
  ADD KEY `id_municipio` (`id_municipio`);

--
-- Indices de la tabla `registro_pdfs`
--
ALTER TABLE `registro_pdfs`
  ADD PRIMARY KEY (`id_pdf`),
  ADD KEY `id_registro` (`id_registro`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `email_usuario` (`email`);

--
-- Indices de la tabla `valores_actividad`
--
ALTER TABLE `valores_actividad`
  ADD PRIMARY KEY (`id_valores`),
  ADD KEY `id_registro` (`id_registro`),
  ADD KEY `id_camposA` (`id_camposA`);

--
-- Indices de la tabla `zonas`
--
ALTER TABLE `zonas`
  ADD PRIMARY KEY (`id_zona`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `actividades`
--
ALTER TABLE `actividades`
  MODIFY `id_actividad` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `areas`
--
ALTER TABLE `areas`
  MODIFY `id_area` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `campos_actividad`
--
ALTER TABLE `campos_actividad`
  MODIFY `id_camposA` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=95;

--
-- AUTO_INCREMENT de la tabla `grupos_actividades`
--
ALTER TABLE `grupos_actividades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `grupo_actividad_detalle`
--
ALTER TABLE `grupo_actividad_detalle`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `justificaciones_mensuales`
--
ALTER TABLE `justificaciones_mensuales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT de la tabla `municipios`
--
ALTER TABLE `municipios`
  MODIFY `id_municipio` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de la tabla `programacion_anual`
--
ALTER TABLE `programacion_anual`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `programacion_mensual`
--
ALTER TABLE `programacion_mensual`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT de la tabla `registros_actividad`
--
ALTER TABLE `registros_actividad`
  MODIFY `id_registro` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de la tabla `registro_pdfs`
--
ALTER TABLE `registro_pdfs`
  MODIFY `id_pdf` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `valores_actividad`
--
ALTER TABLE `valores_actividad`
  MODIFY `id_valores` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=339;

--
-- AUTO_INCREMENT de la tabla `zonas`
--
ALTER TABLE `zonas`
  MODIFY `id_zona` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `campos_actividad`
--
ALTER TABLE `campos_actividad`
  ADD CONSTRAINT `campos_actividad_ibfk_1` FOREIGN KEY (`id_actividad`) REFERENCES `actividades` (`id_actividad`) ON DELETE CASCADE;

--
-- Filtros para la tabla `grupo_actividad_detalle`
--
ALTER TABLE `grupo_actividad_detalle`
  ADD CONSTRAINT `grupo_actividad_detalle_ibfk_1` FOREIGN KEY (`id_grupo`) REFERENCES `grupos_actividades` (`id`),
  ADD CONSTRAINT `grupo_actividad_detalle_ibfk_2` FOREIGN KEY (`id_actividad`) REFERENCES `actividades` (`id_actividad`);

--
-- Filtros para la tabla `justificaciones_mensuales`
--
ALTER TABLE `justificaciones_mensuales`
  ADD CONSTRAINT `justificaciones_mensuales_ibfk_1` FOREIGN KEY (`id_grupo`) REFERENCES `grupos_actividades` (`id`);

--
-- Filtros para la tabla `municipios`
--
ALTER TABLE `municipios`
  ADD CONSTRAINT `municipios_ibfk_1` FOREIGN KEY (`id_zona`) REFERENCES `zonas` (`id_zona`) ON DELETE CASCADE;

--
-- Filtros para la tabla `programacion_mensual`
--
ALTER TABLE `programacion_mensual`
  ADD CONSTRAINT `programacion_mensual_ibfk_1` FOREIGN KEY (`id_actividad`) REFERENCES `actividades` (`id_actividad`);

--
-- Filtros para la tabla `registros_actividad`
--
ALTER TABLE `registros_actividad`
  ADD CONSTRAINT `registros_actividad_ibfk_1` FOREIGN KEY (`id_actividad`) REFERENCES `actividades` (`id_actividad`) ON DELETE CASCADE,
  ADD CONSTRAINT `registros_actividad_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE,
  ADD CONSTRAINT `registros_actividad_ibfk_3` FOREIGN KEY (`id_zona`) REFERENCES `zonas` (`id_zona`) ON DELETE CASCADE,
  ADD CONSTRAINT `registros_actividad_ibfk_4` FOREIGN KEY (`id_municipio`) REFERENCES `municipios` (`id_municipio`) ON DELETE CASCADE;

--
-- Filtros para la tabla `registro_pdfs`
--
ALTER TABLE `registro_pdfs`
  ADD CONSTRAINT `registro_pdfs_ibfk_1` FOREIGN KEY (`id_registro`) REFERENCES `registros_actividad` (`id_registro`);

--
-- Filtros para la tabla `valores_actividad`
--
ALTER TABLE `valores_actividad`
  ADD CONSTRAINT `valores_actividad_ibfk_1` FOREIGN KEY (`id_registro`) REFERENCES `registros_actividad` (`id_registro`) ON DELETE CASCADE,
  ADD CONSTRAINT `valores_actividad_ibfk_2` FOREIGN KEY (`id_camposA`) REFERENCES `campos_actividad` (`id_camposA`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
