-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Tiempo de generación: 19-08-2026 a las 21:29:58
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
-- Base de datos: `chatbot_v2`
--
-- --------------------------------------------------------
CREATE DATABASE IF NOT EXISTS `chatbot_v2` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `chatbot_v2`;

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `descripcion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id`, `nombre`, `descripcion`) VALUES
(1, 'matematica', 'categoria para intenciones de sumas'),
(2, 'historia', ''),
(3, 'saludo', '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `consultas`
--

CREATE TABLE `consultas` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `intent_id` int(11) DEFAULT NULL,
  `texto_pregunta` varchar(255) DEFAULT NULL,
  `confianza` float DEFAULT NULL,
  `fue_respondida` tinyint(1) DEFAULT 0,
  `fecha_hora` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `consultas`
--

INSERT INTO `consultas` (`id`, `usuario_id`, `intent_id`, `texto_pregunta`, `confianza`, `fue_respondida`, `fecha_hora`) VALUES
(10, NULL, NULL, 'hhola brooo', 0.914244, 1, '2026-08-12 15:10:53'),
(11, NULL, NULL, 'como te llamas', 0.55, 0, '2026-08-12 15:13:14'),
(12, NULL, NULL, 'quien ssos', 0.55, 0, '2026-08-12 15:15:16'),
(13, NULL, NULL, 'sos un gil', 0.653124, 1, '2026-08-12 15:17:39'),
(14, NULL, NULL, 'quien fue juan domingo sarmiento?', 0.55, 0, '2026-08-12 15:25:14'),
(15, NULL, NULL, 'que vocaciones tiene la informatica', 0.55, 0, '2026-08-12 15:27:45'),
(17, NULL, NULL, 'quien fue juan domingo sarmiento?', NULL, 0, '2026-08-12 15:46:20'),
(18, NULL, NULL, 'que es el coseno', NULL, 0, '2026-08-12 15:47:02'),
(19, NULL, NULL, 'Hola', NULL, 0, '2026-08-12 15:47:20'),
(20, NULL, NULL, 'la compu me anda lenta', NULL, 0, '2026-08-12 15:47:39'),
(21, NULL, NULL, 'quien fue juan domingo sarmiento?', NULL, 0, '2026-08-12 15:49:53'),
(22, NULL, NULL, 'quien fue juan domingo sarmiento?', NULL, 0, '2026-08-12 15:51:22'),
(23, NULL, NULL, 'hola bro', NULL, 0, '2026-08-12 15:56:06'),
(24, NULL, NULL, 'Que fue primero el huevo o la gallina?', NULL, 0, '2026-08-12 15:56:22'),
(25, NULL, NULL, 'Quein es el equipo mas grande del footbol Argentino?', NULL, 0, '2026-08-12 15:59:56'),
(26, NULL, NULL, 'quien fue juan domingo sarmiento?', NULL, 0, '2026-08-12 16:14:54'),
(27, NULL, NULL, 'quien fue juan domingo sarmiento', 0.55, 0, '2026-08-12 16:25:51'),
(28, NULL, NULL, 'quienes declararon la independencia de Argentina?', 0.55, 0, '2026-08-12 16:27:35'),
(29, NULL, NULL, 'cuanto es 2+2', 0.987841, 1, '2026-08-12 16:28:49'),
(30, NULL, NULL, 'que es una ecuacion', 0.796233, 1, '2026-08-12 16:28:58'),
(31, NULL, NULL, 'que club es el mas grande de Argentina', 0.55, 0, '2026-08-12 16:29:20'),
(32, NULL, NULL, 'hola', 0.999399, 1, '2026-08-12 16:29:47'),
(33, NULL, NULL, 'que haces ?', 0.645456, 1, '2026-08-12 16:29:51'),
(34, NULL, NULL, 'holiiii', 0.991363, 1, '2026-08-12 16:29:51'),
(35, NULL, NULL, 'Cuanto es 2+2', 0.98805, 1, '2026-08-12 16:30:03'),
(36, NULL, NULL, 'quien declaro la independencia?', 0.55, 0, '2026-08-12 16:30:49'),
(37, NULL, NULL, 'fiasdfjisf', 0.55, 0, '2026-08-12 16:31:53'),
(38, NULL, NULL, 'iafaisfia', 0.55, 0, '2026-08-12 16:32:00'),
(39, NULL, NULL, 'hol', 0.993786, 1, '2026-08-12 16:32:01'),
(40, NULL, NULL, 'hola!', 0.999399, 1, '2026-08-12 16:32:41'),
(41, NULL, NULL, 'que es el pantallazo azul?', 0.999019, 1, '2026-08-12 16:32:48'),
(42, NULL, NULL, 'me cuestan las ecuaciones', 0.935498, 1, '2026-08-12 16:33:14'),
(43, NULL, NULL, 'hay algun test vocacional', 0.959123, 1, '2026-08-12 16:38:45'),
(44, NULL, NULL, 'cual es el horario de clases', 0.989411, 1, '2026-08-12 16:39:04'),
(45, NULL, NULL, 'que programa me recomiendas para editar documentos', 0.983226, 1, '2026-08-12 16:39:34'),
(46, NULL, NULL, 'lo necesito para programar', 0.849299, 1, '2026-08-12 16:39:49'),
(47, NULL, NULL, 'Programacion III', 0.55, 0, '2026-08-12 16:40:14'),
(48, NULL, NULL, 'hola', 0.999399, 1, '2026-08-12 11:40:39'),
(49, NULL, NULL, 'en que sectores me podes ayudar', 0.835898, 1, '2026-08-12 11:40:55'),
(50, NULL, NULL, 'que es la orientacion vocacional', 0.976283, 1, '2026-08-12 11:42:13'),
(51, NULL, NULL, 'gracias', 0.983446, 1, '2026-08-12 11:42:25'),
(52, NULL, NULL, 'adios', 0.925701, 1, '2026-08-12 11:42:30'),
(53, NULL, NULL, 'quien fue juan domingo sarmiento?', 0.55, 0, '2026-08-12 11:43:01'),
(54, NULL, NULL, 'Quien fue sarmiento?', 0.55, 0, '2026-08-12 11:46:54'),
(55, NULL, NULL, 'quien fue sarmiento?', 0.55, 0, '2026-08-12 11:47:19'),
(56, NULL, NULL, 'Hola', 0.999375, 1, '2026-08-12 11:47:46'),
(57, NULL, NULL, 'hola', 0.999399, 1, '2026-08-12 11:47:50'),
(58, NULL, NULL, 'quien fue sarmiento', 0.55, 0, '2026-08-12 11:48:09'),
(59, NULL, NULL, 'hola', NULL, 0, '2026-08-12 17:22:32'),
(60, NULL, NULL, 'que es el pantallazo azul', NULL, 0, '2026-08-12 17:22:45'),
(61, NULL, NULL, 'como cambiar pasta termica', NULL, 0, '2026-08-12 17:25:03'),
(62, NULL, NULL, 'Hola', NULL, 0, '2026-08-12 17:32:59'),
(63, NULL, NULL, 'cuanto es 2+2', NULL, 0, '2026-08-12 17:33:13'),
(64, NULL, NULL, 'como cambiar pasta termica', NULL, 0, '2026-08-12 17:39:24'),
(65, NULL, NULL, 'como cambiar pasta termica', NULL, 0, '2026-08-12 17:41:45'),
(66, NULL, NULL, 'hola', 0.999626, 1, '2026-08-12 17:51:40'),
(67, NULL, NULL, 'hola', 0.999626, 1, '2026-08-12 17:51:47'),
(68, NULL, NULL, 'hola', 0.999626, 1, '2026-08-12 17:55:32'),
(69, NULL, NULL, 'no me anda el internet', 0.999373, 1, '2026-08-12 18:01:21'),
(70, NULL, NULL, 'y la pc esta muy lenta', 0.994648, 1, '2026-08-12 18:01:40'),
(71, NULL, NULL, 'no tengo sonido en mi pc', 0.987222, 1, '2026-08-12 18:02:02'),
(72, NULL, NULL, 'grazias', 0.628813, 1, '2026-08-12 18:02:10'),
(73, NULL, NULL, 'fdsfsd', 0.697767, 1, '2026-08-12 18:02:50'),
(74, NULL, NULL, 'fdsfsdfds', 0.55, 0, '2026-08-12 18:02:57'),
(75, NULL, NULL, 'fgefgf', 0.55087, 1, '2026-08-12 18:03:02'),
(76, NULL, NULL, 'creo que tengo un virus en la pc', 0.997631, 1, '2026-08-12 18:05:13'),
(77, NULL, NULL, 'quien fue sarmiento', 0.837192, 1, '2026-08-12 18:06:48'),
(78, NULL, NULL, 'sarmiento?', 0.570187, 1, '2026-08-12 18:07:33'),
(79, NULL, NULL, 'sarmiento', 0.570187, 1, '2026-08-12 18:07:40'),
(80, NULL, NULL, 'quien fue sarmiento', 0.837192, 1, '2026-08-12 18:07:49'),
(81, NULL, NULL, 'Hola', 0.999623, 1, '2026-08-12 18:13:07'),
(82, NULL, NULL, 'sfdasf', 0.55, 0, '2026-08-12 18:18:25'),
(83, NULL, NULL, 'fsafasfasfasfasf', 0.55, 0, '2026-08-12 18:18:31'),
(84, NULL, NULL, ',nbknbn', 0.726227, 1, '2026-08-12 18:19:50'),
(85, NULL, NULL, 'bkjkj', 0.67697, 1, '2026-08-12 18:19:51'),
(86, NULL, NULL, 'jjkb', 0.55, 0, '2026-08-12 18:19:58'),
(87, NULL, NULL, 'quien fue sarmiento', 0.837192, 1, '2026-08-12 18:20:52'),
(88, NULL, NULL, 'vjbadjbva', 0.55, 0, '2026-08-12 18:22:59'),
(89, NULL, NULL, 'kvandvlkja', 0.55, 0, '2026-08-12 18:23:04'),
(90, NULL, NULL, 'anvjk', 0.822698, 1, '2026-08-12 18:23:05'),
(91, NULL, NULL, 'jvhjdsvhjds', 0.55, 0, '2026-08-12 18:25:02'),
(92, NULL, NULL, 'sdvljbsjdv', 0.55, 0, '2026-08-12 18:25:08'),
(93, NULL, NULL, 'sdvbkjsdv', 0.55, 0, '2026-08-12 18:25:15'),
(94, NULL, NULL, 'diferencia entre router y modem', 0.996147, 1, '2026-08-12 18:25:30'),
(95, NULL, NULL, 'hola', 0.999626, 1, '2026-08-12 18:25:46'),
(96, NULL, NULL, 'hola', 0.999626, 1, '2026-08-12 21:01:04'),
(97, NULL, NULL, 'hellow', 0.977986, 1, '2026-08-12 21:01:08'),
(98, NULL, NULL, 'quien fue Nikola Tesla', 0.55, 0, '2026-08-12 21:01:44'),
(99, NULL, NULL, 'Cuanto es 4+4', 0.996027, 1, '2026-08-12 21:08:20'),
(100, NULL, NULL, 'que decis nada que ver, es 8 el resultado man', 0.920615, 1, '2026-08-12 21:09:31'),
(101, NULL, NULL, 'Entiendes lo  que te digo?', 0.828918, 1, '2026-08-12 21:35:28'),
(102, NULL, NULL, 'hola?', 0.999626, 1, '2026-08-12 21:35:47'),
(103, NULL, NULL, 'cuanto es 2 + 2?', 0.997052, 1, '2026-08-12 21:36:01'),
(104, NULL, NULL, 'cuanto es 2+2', 0.995944, 1, '2026-08-12 21:36:21'),
(105, NULL, NULL, 'quien fue sarmiento', 0.999951, 1, '2026-08-13 14:19:27'),
(106, NULL, NULL, 'hola', 0.863906, 1, '2026-08-13 14:19:29'),
(107, NULL, NULL, 'Hola', 0.798182, 1, '2026-08-19 21:07:17'),
(108, NULL, NULL, 'hola', 0.863906, 1, '2026-08-19 21:07:27'),
(109, NULL, NULL, 'quien fue sarmiento', 0.999951, 1, '2026-08-19 21:08:25');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `frases_entrenamiento`
--

CREATE TABLE `frases_entrenamiento` (
  `id` int(11) NOT NULL,
  `intent_id` int(11) DEFAULT NULL,
  `texto_ejemplo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `frases_entrenamiento`
--

INSERT INTO `frases_entrenamiento` (`id`, `intent_id`, `texto_ejemplo`) VALUES
(1, 3, 'Juan Domingo Sarmiento'),
(2, 3, 'Juan Sarmiento'),
(3, 4, 'Jose de San Martin'),
(4, 4, 'San Martin'),
(5, 5, 'hola!'),
(6, 5, 'hola'),
(7, 5, 'como andas'),
(8, 5, 'que haces');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `intents`
--

CREATE TABLE `intents` (
  `id` int(11) NOT NULL,
  `nombre_intent` varchar(100) DEFAULT NULL,
  `categoria_id` int(11) DEFAULT NULL,
  `descripcion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `intents`
--

INSERT INTO `intents` (`id`, `nombre_intent`, `categoria_id`, `descripcion`) VALUES
(1, 'suma_matematica', 1, 'Intention para sumas matematicas'),
(2, 'resta_matematica', 1, 'Intention para restas matematicas'),
(3, 'proceres_sarmiento', 2, ''),
(4, 'proceres_san_martin', 2, ''),
(5, 'saludar', 3, '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `permisos`
--

CREATE TABLE `permisos` (
  `id_permiso` int(11) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `descripcion` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `permisos`
--

INSERT INTO `permisos` (`id_permiso`, `nombre`, `descripcion`) VALUES
(7, 'Lectura y Escritura', 'Permiso de Leer y Escribir '),
(8, 'Lectura', 'Ya sea Alumno o Personas sin mayor acceso que al chat');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `preguntas_sin_respuesta`
--

CREATE TABLE `preguntas_sin_respuesta` (
  `id` int(11) NOT NULL,
  `consulta_id` int(11) DEFAULT NULL,
  `estado` enum('pendiente','resuelta') DEFAULT 'pendiente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `respuestas`
--

CREATE TABLE `respuestas` (
  `id_respuesta` int(11) NOT NULL,
  `intent_id` int(11) DEFAULT NULL,
  `texto_respuesta` text DEFAULT NULL,
  `activa` tinyint(1) DEFAULT 1,
  `fecha_actualizacion` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `respuestas`
--

INSERT INTO `respuestas` (`id_respuesta`, `intent_id`, `texto_respuesta`, `activa`, `fecha_actualizacion`) VALUES
(1, 3, 'Sarmiento es el encargado de que suframos en la escuela.', 1, NULL),
(2, 4, 'San martin cruzó los Andes', 1, NULL),
(3, 4, 'San Martin es un heroe', 1, NULL),
(4, 5, 'Hola! soy A.U.R.A, el chatbot de la EPET N°3', 1, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `idRol` int(11) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `descripcion` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`idRol`, `nombre`, `descripcion`) VALUES
(9, 'Administrador', 'Acceso total al sistema'),
(11, 'Usuario', 'Ya sea Alumno o Personas sin mayor acceso que al chat');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles_permisos`
--

CREATE TABLE `roles_permisos` (
  `id_rol` int(11) DEFAULT NULL,
  `id_permisos` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(100) DEFAULT NULL,
  `id_rol` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `nombre`, `email`, `password`, `id_rol`) VALUES
(19, 'Administrador', 'admin@epet3.edu.ar', '$2y$10$GAZbm/tlehVRurWZIgO4cuTyBVllybJ3a5oKIt.aHSc069i2n8I6y', NULL),
(20, 'User Beta', 'user@epet3.edu.ar', '$2y$10$94xRUXFIkGYazBFQ9U7cIO6VlJG78p.RKDmtXFrtLBU1j7dhWStLG', NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `consultas`
--
ALTER TABLE `consultas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_Consultas_Usuario_idx` (`usuario_id`),
  ADD KEY `fk_Consultas_Intent_idx` (`intent_id`);

--
-- Indices de la tabla `frases_entrenamiento`
--
ALTER TABLE `frases_entrenamiento`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_Frases_Intent_idx` (`intent_id`);

--
-- Indices de la tabla `intents`
--
ALTER TABLE `intents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_Intents_Categoria_idx` (`categoria_id`);

--
-- Indices de la tabla `permisos`
--
ALTER TABLE `permisos`
  ADD PRIMARY KEY (`id_permiso`);

--
-- Indices de la tabla `preguntas_sin_respuesta`
--
ALTER TABLE `preguntas_sin_respuesta`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_PreguntasSinRespuesta_Consulta` (`consulta_id`),
  ADD KEY `fk_PreguntasSinRespuesta_Consulta_idx` (`consulta_id`);

--
-- Indices de la tabla `respuestas`
--
ALTER TABLE `respuestas`
  ADD PRIMARY KEY (`id_respuesta`),
  ADD KEY `fk_Respuestas_Intent_idx` (`intent_id`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`idRol`);

--
-- Indices de la tabla `roles_permisos`
--
ALTER TABLE `roles_permisos`
  ADD KEY `fk_Rol_Permisos_1_idx` (`id_rol`),
  ADD KEY `fk_Rol_Permisos_2_idx` (`id_permisos`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD KEY `fk_Usuario_1_idx` (`id_rol`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `consultas`
--
ALTER TABLE `consultas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=110;

--
-- AUTO_INCREMENT de la tabla `frases_entrenamiento`
--
ALTER TABLE `frases_entrenamiento`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `intents`
--
ALTER TABLE `intents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `permisos`
--
ALTER TABLE `permisos`
  MODIFY `id_permiso` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `preguntas_sin_respuesta`
--
ALTER TABLE `preguntas_sin_respuesta`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `respuestas`
--
ALTER TABLE `respuestas`
  MODIFY `id_respuesta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `idRol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `consultas`
--
ALTER TABLE `consultas`
  ADD CONSTRAINT `fk_Consultas_Intent` FOREIGN KEY (`intent_id`) REFERENCES `intents` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_Consultas_Usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id_usuario`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `frases_entrenamiento`
--
ALTER TABLE `frases_entrenamiento`
  ADD CONSTRAINT `fk_Frases_Intent` FOREIGN KEY (`intent_id`) REFERENCES `intents` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `intents`
--
ALTER TABLE `intents`
  ADD CONSTRAINT `fk_Intents_Categoria` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `preguntas_sin_respuesta`
--
ALTER TABLE `preguntas_sin_respuesta`
  ADD CONSTRAINT `fk_PreguntasSinRespuesta_Consulta` FOREIGN KEY (`consulta_id`) REFERENCES `consultas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `respuestas`
--
ALTER TABLE `respuestas`
  ADD CONSTRAINT `fk_Respuestas_Intent` FOREIGN KEY (`intent_id`) REFERENCES `intents` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `roles_permisos`
--
ALTER TABLE `roles_permisos`
  ADD CONSTRAINT `fk_Rol_Permisos_1` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`idRol`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_Rol_Permisos_2` FOREIGN KEY (`id_permisos`) REFERENCES `permisos` (`id_permiso`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `fk_Usuario_1` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`idRol`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
