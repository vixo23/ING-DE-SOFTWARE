-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 09-10-2025 a las 22:17:34
-- Versión del servidor: 11.8.2-MariaDB
-- Versión de PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `biometrico1`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `centro_costo`
--

CREATE TABLE `centro_costo` (
  `id_centro` int(10) NOT NULL,
  `fechaCreacion` date NOT NULL,
  `descripcion` varchar(250) NOT NULL,
  `status` int(10) NOT NULL DEFAULT 1,
  `id_empresa` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Volcado de datos para la tabla `centro_costo`
--

INSERT INTO `centro_costo` (`id_centro`, `fechaCreacion`, `descripcion`, `status`, `id_empresa`) VALUES
(1, '2025-09-10', 'CO222', 1, 1),
(2, '2025-09-18', 'PRUEBAAA', 1, 1),
(3, '2025-09-13', 'prueba2', 1, 1),
(4, '2025-09-13', 'prueba1', 1, 1),
(5, '2025-10-08', 'centro de costo demo', 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `departamentos`
--

CREATE TABLE `departamentos` (
  `id_departamento` int(10) NOT NULL,
  `fechaCreacion` date DEFAULT NULL,
  `descripcion` varchar(250) NOT NULL,
  `status` tinyint(10) NOT NULL DEFAULT 1,
  `id_empresas` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Volcado de datos para la tabla `departamentos`
--

INSERT INTO `departamentos` (`id_departamento`, `fechaCreacion`, `descripcion`, `status`, `id_empresas`) VALUES
(1, '2025-09-11', 'contabilidad', 1, 1),
(2, '2025-09-10', 'recursos humanos', 1, 1),
(3, '2025-09-10', 'cobranza', 1, 1),
(4, '2025-09-11', 'centro de finanzas', 1, 1),
(20, '2025-10-08', 'prueba111', 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `dispositivo`
--

CREATE TABLE `dispositivo` (
  `id_biometrico` int(11) NOT NULL,
  `marca` varchar(50) NOT NULL,
  `modelo` varchar(250) NOT NULL,
  `ubicacion` varchar(250) NOT NULL,
  `status` tinyint(5) NOT NULL DEFAULT 1,
  `tipo` varchar(50) NOT NULL,
  `id_sucursal` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empresas`
--

CREATE TABLE `empresas` (
  `id_empresas` int(11) NOT NULL,
  `RazonSocial` varchar(250) NOT NULL,
  `nombreFantasia` varchar(250) DEFAULT NULL,
  `rut` varchar(50) NOT NULL,
  `representanteLegal` varchar(250) DEFAULT NULL,
  `actividadEconomica` varchar(250) DEFAULT NULL,
  `giro` varchar(250) DEFAULT NULL,
  `fechaCreacion` date DEFAULT NULL,
  `web` varchar(250) DEFAULT NULL,
  `email` varchar(250) DEFAULT NULL,
  `logo` varchar(250) DEFAULT NULL,
  `pais` varchar(250) DEFAULT NULL,
  `status` int(11) NOT NULL DEFAULT 1,
  `id_holding` int(11) NOT NULL,
  `FechaTermino` date DEFAULT NULL,
  `clave` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Volcado de datos para la tabla `empresas`
--

INSERT INTO `empresas` (`id_empresas`, `RazonSocial`, `nombreFantasia`, `rut`, `representanteLegal`, `actividadEconomica`, `giro`, `fechaCreacion`, `web`, `email`, `logo`, `pais`, `status`, `id_holding`, `FechaTermino`, `clave`) VALUES
(1, 'INFORMATICA EUGCOM LTDA.', 'EUGCOM SPA', '77597940-2', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, '1025'),
(2, 'CAPACITACION VALBEL LTDA.', 'CAPACITACION VALBEL LTDA.', '76145906-6', 'John Vilches', 'CAPACITACION', 'CAPACITACION', '2025-09-10', 'WWW.VALBEL.CL', 'dsalinas@eugcom.cl', NULL, NULL, 1, 1, '2025-09-26', '1010');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `holding`
--

CREATE TABLE `holding` (
  `id_holding` int(11) NOT NULL,
  `nombre` varchar(250) NOT NULL,
  `representante` varchar(250) NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `fechaCreacion` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Volcado de datos para la tabla `holding`
--

INSERT INTO `holding` (`id_holding`, `nombre`, `representante`, `status`, `fechaCreacion`) VALUES
(1, 'EUGCOM', 'JOHN VILCHES', 1, '2025-08-14'),
(2, 'ALFONSO ELIAS J E HIJOS S.A.', 'MARCO ELIAS MIRTY ', 1, '2025-09-12'),
(3, 'TAPEMAN', 'NULL', 0, '2025-09-12');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `marcas`
--

CREATE TABLE `marcas` (
  `id_marcas` int(250) NOT NULL,
  `fecha` date NOT NULL,
  `hora` time NOT NULL,
  `tipoMarca` varchar(50) DEFAULT NULL,
  `status` tinyint(5) DEFAULT NULL,
  `hash` varchar(450) DEFAULT NULL,
  `id_usuario` int(10) NOT NULL,
  `id_biometrico` int(10) DEFAULT NULL,
  `correoEnviado` int(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Volcado de datos para la tabla `marcas`
--

INSERT INTO `marcas` (`id_marcas`, `fecha`, `hora`, `tipoMarca`, `status`, `hash`, `id_usuario`, `id_biometrico`, `correoEnviado`) VALUES
(1, '2025-09-11', '22:01:37', NULL, 1, NULL, 1, NULL, NULL),
(2, '2025-09-12', '00:03:05', '1', 1, NULL, 1, NULL, 1),
(3, '2025-09-03', '22:02:10', NULL, 1, NULL, 22, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `motivos_permisos`
--

CREATE TABLE `motivos_permisos` (
  `id_motivo` int(10) NOT NULL,
  `descripcion` varchar(250) NOT NULL,
  `status` tinyint(5) NOT NULL,
  `fecha_Creacion` date DEFAULT NULL,
  `id_empresa` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Volcado de datos para la tabla `motivos_permisos`
--

INSERT INTO `motivos_permisos` (`id_motivo`, `descripcion`, `status`, `fecha_Creacion`, `id_empresa`) VALUES
(1, 'ENFERMEDAD EN EL DIA 	', 1, '2025-09-11', 1),
(2, 'LACTANCIA', 1, '2025-09-12', 1),
(3, 'MUDANZA', 1, '2025-09-12', 1),
(16, 'LICENCIA', 1, '2025-09-20', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `permisos`
--

CREATE TABLE `permisos` (
  `id_permisos` int(10) NOT NULL,
  `id_usuario` int(10) NOT NULL,
  `id_motivo` int(10) NOT NULL,
  `fecha_ini` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `hora_ini` varchar(50) DEFAULT NULL,
  `hora_fin` varchar(50) DEFAULT NULL,
  `total_horas` varchar(50) NOT NULL,
  `observaciones` varchar(500) NOT NULL,
  `goce` int(10) NOT NULL,
  `status` tinyint(5) NOT NULL,
  `adjunto` varchar(250) DEFAULT NULL,
  `creado` varchar(50) NOT NULL,
  `id_empresa` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Volcado de datos para la tabla `permisos`
--

INSERT INTO `permisos` (`id_permisos`, `id_usuario`, `id_motivo`, `fecha_ini`, `fecha_fin`, `hora_ini`, `hora_fin`, `total_horas`, `observaciones`, `goce`, `status`, `adjunto`, `creado`, `id_empresa`) VALUES
(1, 1, 2, '2025-09-03', '2025-09-11', '09:00', '18:30', '63', 'Vacaciones', 1, 2, 'https://biometricocloud.cl/sitioreloj/uploads/', '2018-09-10', 1),
(3, 1, 1, '2025-09-24', '2025-09-25', NULL, NULL, '2', 'MEDICO', 1, 1, NULL, '2025-09-23 21:28:37', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sucursal`
--

CREATE TABLE `sucursal` (
  `id_sucursal` int(11) NOT NULL,
  `tipo` varchar(45) NOT NULL,
  `nombre` varchar(250) DEFAULT NULL,
  `direccion` varchar(450) NOT NULL,
  `comuna` varchar(250) NOT NULL,
  `telefono` varchar(45) DEFAULT NULL,
  `celular` varchar(45) DEFAULT NULL,
  `fax` varchar(45) DEFAULT NULL,
  `representante` varchar(250) DEFAULT NULL,
  `rutRepresentante` varchar(45) DEFAULT NULL,
  `ciudad` varchar(45) DEFAULT NULL,
  `pais` varchar(45) DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `empresas_id_empresas` int(11) NOT NULL,
  `fechaCreacion` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Volcado de datos para la tabla `sucursal`
--

INSERT INTO `sucursal` (`id_sucursal`, `tipo`, `nombre`, `direccion`, `comuna`, `telefono`, `celular`, `fax`, `representante`, `rutRepresentante`, `ciudad`, `pais`, `status`, `empresas_id_empresas`, `fechaCreacion`) VALUES
(1, 'matriz', ' 	INFORMATICA EUGCOM LTDA', 'EDUARDO MATTE NRO. 1726', 'SANTIAGO', NULL, ' jvilches@eugcom.cl', NULL, NULL, NULL, NULL, NULL, 1, 1, '2025-09-10'),
(2, '', 'MARTINEZ DE ROSAS 54B2-G', 'MARTINEZ DE ROSAS 54B2-G', 'colina', NULL, '11111111', NULL, NULL, NULL, NULL, NULL, 1, 1, '2025-09-11'),
(3, 'Sucursal', 'PRUEBA1', 'SANTIAGO 2020', 'MAIPU', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL),
(4, 'Matriz', 'PRUEB', 'SANTIAGO', 'Cerrillos', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_contrato`
--

CREATE TABLE `tipo_contrato` (
  `id_tipocontrato` int(10) NOT NULL,
  `descripcion` varchar(250) NOT NULL,
  `descripcion_turno` varchar(250) NOT NULL,
  `status` tinyint(5) NOT NULL,
  `id_empresa` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Volcado de datos para la tabla `tipo_contrato`
--

INSERT INTO `tipo_contrato` (`id_tipocontrato`, `descripcion`, `descripcion_turno`, `status`, `id_empresa`) VALUES
(1, 'PLAZO FiJO', '', 1, 1),
(2, 'HONORARIOS', 'edicion1', 1, 1),
(3, 'PRUEBA22', 'prueba edicion', 1, 1),
(6, 'PRUEBA1', 'PRUEBA DESCRIPCION', 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_vacaciones`
--

CREATE TABLE `tipo_vacaciones` (
  `id_tipovacacion` int(10) NOT NULL,
  `descripcion` varchar(250) NOT NULL,
  `status` tinyint(5) NOT NULL DEFAULT 1,
  `id_empresas` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Volcado de datos para la tabla `tipo_vacaciones`
--

INSERT INTO `tipo_vacaciones` (`id_tipovacacion`, `descripcion`, `status`, `id_empresas`) VALUES
(1, 'Legales', 1, 1),
(2, 'Progresivas', 1, 1),
(3, 'FALLECIMIENTO', 1, 1),
(4, 'PERDIDA', 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `turnos`
--

CREATE TABLE `turnos` (
  `id_turnos` int(10) NOT NULL,
  `nombreTurno` varchar(20) DEFAULT NULL,
  `numeroDia` int(10) DEFAULT NULL,
  `horaEntrada` varchar(45) DEFAULT NULL,
  `horaSalida` varchar(45) DEFAULT NULL,
  `diaEntrada` varchar(45) DEFAULT NULL,
  `diaSalida` varchar(45) DEFAULT NULL,
  `entradaColacion` varchar(45) DEFAULT NULL,
  `tolerancia` int(11) DEFAULT NULL,
  `salidaColacion` varchar(45) DEFAULT NULL,
  `totalHorasDia` varchar(45) DEFAULT NULL,
  `status` tinyint(5) DEFAULT 1,
  `id_empresa` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Volcado de datos para la tabla `turnos`
--

INSERT INTO `turnos` (`id_turnos`, `nombreTurno`, `numeroDia`, `horaEntrada`, `horaSalida`, `diaEntrada`, `diaSalida`, `entradaColacion`, `tolerancia`, `salidaColacion`, `totalHorasDia`, `status`, `id_empresa`) VALUES
(1, 'jornada3', 1, '09:00', '18:30', 'Lunes', 'Viernes', '13:30', 10, '14:00', '9:00', 1, 1),
(4, 'JORNADA1', NULL, '08:30', '18:15', 'Lunes', 'Viernes', '14:30', 10, '15:30', NULL, 1, 1),
(5, 'jornada2', NULL, '09:00', '19:00', 'Lunes', 'Viernes', '14:30', 10, '15:00', NULL, 1, 1),
(6, '5', NULL, '09:20', '18:30', 'Lunes', 'Viernes', '14:00', 15, '14:30', NULL, 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuarios` int(10) NOT NULL,
  `rut` varchar(50) NOT NULL,
  `digitoRut` varchar(1) NOT NULL,
  `codigoDedo` varchar(45) DEFAULT NULL,
  `nombres` varchar(50) NOT NULL,
  `apellido1` varchar(45) NOT NULL,
  `apellido2` varchar(45) NOT NULL,
  `id_tipocontrato` int(10) NOT NULL,
  `direccion` varchar(100) NOT NULL,
  `comuna` varchar(100) NOT NULL,
  `ciudad` varchar(100) DEFAULT NULL,
  `pais` varchar(100) DEFAULT NULL,
  `telefono` varchar(45) DEFAULT NULL,
  `celular` varchar(100) NOT NULL,
  `email` varchar(250) NOT NULL,
  `turnos_id_turnos` int(10) DEFAULT NULL,
  `username` varchar(250) NOT NULL,
  `password` int(250) NOT NULL,
  `cargo` varchar(100) NOT NULL,
  `salt` varchar(450) DEFAULT NULL,
  `status` tinyint(5) NOT NULL,
  `id_sucursal` int(10) DEFAULT NULL,
  `id_departamento` int(10) DEFAULT NULL,
  `id_centrocosto` int(10) DEFAULT NULL,
  `fechaCreacion` date DEFAULT NULL,
  `id_empresa` int(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuarios`, `rut`, `digitoRut`, `codigoDedo`, `nombres`, `apellido1`, `apellido2`, `id_tipocontrato`, `direccion`, `comuna`, `ciudad`, `pais`, `telefono`, `celular`, `email`, `turnos_id_turnos`, `username`, `password`, `cargo`, `salt`, `status`, `id_sucursal`, `id_departamento`, `id_centrocosto`, `fechaCreacion`, `id_empresa`) VALUES
(1, '13926052', '2', NULL, 'DANIEL', 'RODRIGUEZ', 'VALDES', 1, 'NULL', 'NULL', 'NULL', NULL, NULL, '99999999', 'CORREO@PRUEBA.COM', 1, 'USERNAME', 121212, '0', '60f1b72bf315b991f862d2a7345cbb14998c818b048ff196c217cad30e5f3cfd182da791cd2e9062c53d57587412dad61a9bc3b3713b9c5a6b5ae2e180be6ad6', 1, 1, 1, 1, '2025-09-11', 1),
(2, '533523523', '3', NULL, 'diego', 'jere', 'klintom', 1, 'bella 21', 'maipu', NULL, NULL, NULL, '', 'perez@prueba.com', 4, 'asdasd', 1111, '1', NULL, 1, 1, 1, 1, '2025-09-25', 1),
(20, '11111111', '2', NULL, 'pedro', 'perez', 'perez', 1, 'valle 1212', 'Cerro Navia', NULL, NULL, '222222', '99999933', 'perez@pedro.com', 1, '11111111', 1234, '1', NULL, 1, 1, 1, 1, '2025-09-23', 1),
(21, '12345678', 'k', NULL, 'addan ignacio', 'saez', 'rodriguez', 1, 'SANTIAGO 1500', 'Maipú', NULL, NULL, '', '+569 47478989', 'prueba@correo.com', NULL, '12345678', 1234, '1', NULL, 1, 1, 2, 1, '2025-09-25', 2),
(22, '22211380', '2', NULL, 'felipe', 'soto', 'soto', 1, 'los pintos 22', 'Cerrillos', NULL, NULL, '', '92848218542', 'prueba@correo.com', 6, '22211380', 1234, '1', NULL, 1, 1, 4, 1, '2025-09-24', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vacaciones`
--

CREATE TABLE `vacaciones` (
  `id_vacaciones` int(10) NOT NULL,
  `id_tipovacacion` int(10) NOT NULL,
  `anio` int(10) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_termino` date NOT NULL,
  `dias` int(10) NOT NULL,
  `periodo` int(10) NOT NULL,
  `dia_usado` int(10) NOT NULL,
  `dia_restante` int(10) NOT NULL,
  `id_usuario` int(10) NOT NULL,
  `aprobacion` int(10) NOT NULL,
  `status` tinyint(5) NOT NULL,
  `id_autoriza` int(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Volcado de datos para la tabla `vacaciones`
--

INSERT INTO `vacaciones` (`id_vacaciones`, `id_tipovacacion`, `anio`, `fecha_inicio`, `fecha_termino`, `dias`, `periodo`, `dia_usado`, `dia_restante`, `id_usuario`, `aprobacion`, `status`, `id_autoriza`) VALUES
(2, 1, 2004, '2025-09-02', '2025-09-10', 20, 2, 10, 10, 1, 2, 1, 1);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `centro_costo`
--
ALTER TABLE `centro_costo`
  ADD PRIMARY KEY (`id_centro`),
  ADD UNIQUE KEY `id_centro` (`id_centro`),
  ADD KEY `id_empresas` (`id_empresa`) USING BTREE;

--
-- Indices de la tabla `departamentos`
--
ALTER TABLE `departamentos`
  ADD PRIMARY KEY (`id_departamento`),
  ADD UNIQUE KEY `id_departamento` (`id_departamento`),
  ADD KEY `id_empresas` (`id_empresas`) USING BTREE;

--
-- Indices de la tabla `dispositivo`
--
ALTER TABLE `dispositivo`
  ADD PRIMARY KEY (`id_biometrico`),
  ADD UNIQUE KEY `id_biometrico` (`id_biometrico`),
  ADD KEY `id_sucursal` (`id_sucursal`);

--
-- Indices de la tabla `empresas`
--
ALTER TABLE `empresas`
  ADD PRIMARY KEY (`id_empresas`),
  ADD UNIQUE KEY `id_empresa` (`id_empresas`),
  ADD KEY `id_holding` (`id_holding`);

--
-- Indices de la tabla `holding`
--
ALTER TABLE `holding`
  ADD PRIMARY KEY (`id_holding`),
  ADD UNIQUE KEY `id_holding` (`id_holding`);

--
-- Indices de la tabla `marcas`
--
ALTER TABLE `marcas`
  ADD PRIMARY KEY (`id_marcas`) USING BTREE,
  ADD UNIQUE KEY `id_marca` (`id_marcas`) USING BTREE,
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_biometrico` (`id_biometrico`);

--
-- Indices de la tabla `motivos_permisos`
--
ALTER TABLE `motivos_permisos`
  ADD PRIMARY KEY (`id_motivo`),
  ADD UNIQUE KEY `id_motivo` (`id_motivo`),
  ADD KEY `id_empresa` (`id_empresa`);

--
-- Indices de la tabla `permisos`
--
ALTER TABLE `permisos`
  ADD PRIMARY KEY (`id_permisos`) USING BTREE,
  ADD UNIQUE KEY `id_permiso` (`id_permisos`) USING BTREE,
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_motivo` (`id_motivo`),
  ADD KEY `id_empresa` (`id_empresa`);

--
-- Indices de la tabla `sucursal`
--
ALTER TABLE `sucursal`
  ADD PRIMARY KEY (`id_sucursal`),
  ADD UNIQUE KEY `id_sucursal` (`id_sucursal`),
  ADD KEY `id_empresa` (`empresas_id_empresas`);

--
-- Indices de la tabla `tipo_contrato`
--
ALTER TABLE `tipo_contrato`
  ADD PRIMARY KEY (`id_tipocontrato`),
  ADD UNIQUE KEY `id_tipocontrato` (`id_tipocontrato`),
  ADD KEY `id_empresa` (`id_empresa`);

--
-- Indices de la tabla `tipo_vacaciones`
--
ALTER TABLE `tipo_vacaciones`
  ADD PRIMARY KEY (`id_tipovacacion`),
  ADD UNIQUE KEY `id_tipovacacion` (`id_tipovacacion`),
  ADD KEY `id_empresa` (`id_empresas`);

--
-- Indices de la tabla `turnos`
--
ALTER TABLE `turnos`
  ADD PRIMARY KEY (`id_turnos`),
  ADD UNIQUE KEY `id_turno` (`id_turnos`),
  ADD KEY `id_empresa` (`id_empresa`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuarios`),
  ADD UNIQUE KEY `id_usuario` (`id_usuarios`),
  ADD KEY `id_empresa` (`id_empresa`),
  ADD KEY `id_centrocosto` (`id_centrocosto`),
  ADD KEY `id_departamento` (`id_departamento`),
  ADD KEY `id_sucursal` (`id_sucursal`),
  ADD KEY `id_turno` (`turnos_id_turnos`),
  ADD KEY `id_tipocontrato` (`id_tipocontrato`);

--
-- Indices de la tabla `vacaciones`
--
ALTER TABLE `vacaciones`
  ADD PRIMARY KEY (`id_vacaciones`),
  ADD UNIQUE KEY `id_vacacion` (`id_vacaciones`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_tipovacacion` (`id_tipovacacion`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `centro_costo`
--
ALTER TABLE `centro_costo`
  MODIFY `id_centro` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `departamentos`
--
ALTER TABLE `departamentos`
  MODIFY `id_departamento` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `dispositivo`
--
ALTER TABLE `dispositivo`
  MODIFY `id_biometrico` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `empresas`
--
ALTER TABLE `empresas`
  MODIFY `id_empresas` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `holding`
--
ALTER TABLE `holding`
  MODIFY `id_holding` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `marcas`
--
ALTER TABLE `marcas`
  MODIFY `id_marcas` int(250) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `motivos_permisos`
--
ALTER TABLE `motivos_permisos`
  MODIFY `id_motivo` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `permisos`
--
ALTER TABLE `permisos`
  MODIFY `id_permisos` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `sucursal`
--
ALTER TABLE `sucursal`
  MODIFY `id_sucursal` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `tipo_contrato`
--
ALTER TABLE `tipo_contrato`
  MODIFY `id_tipocontrato` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `tipo_vacaciones`
--
ALTER TABLE `tipo_vacaciones`
  MODIFY `id_tipovacacion` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `turnos`
--
ALTER TABLE `turnos`
  MODIFY `id_turnos` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuarios` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de la tabla `vacaciones`
--
ALTER TABLE `vacaciones`
  MODIFY `id_vacaciones` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `centro_costo`
--
ALTER TABLE `centro_costo`
  ADD CONSTRAINT `centro_costo_ibfk_1` FOREIGN KEY (`id_empresa`) REFERENCES `empresas` (`id_empresas`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `departamentos`
--
ALTER TABLE `departamentos`
  ADD CONSTRAINT `departamentos_ibfk_1` FOREIGN KEY (`id_empresas`) REFERENCES `empresas` (`id_empresas`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `dispositivo`
--
ALTER TABLE `dispositivo`
  ADD CONSTRAINT `dispositivo_ibfk_1` FOREIGN KEY (`id_sucursal`) REFERENCES `sucursal` (`id_sucursal`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `empresas`
--
ALTER TABLE `empresas`
  ADD CONSTRAINT `empresas_ibfk_1` FOREIGN KEY (`id_holding`) REFERENCES `holding` (`id_holding`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `marcas`
--
ALTER TABLE `marcas`
  ADD CONSTRAINT `marcas_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuarios`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `marcas_ibfk_2` FOREIGN KEY (`id_biometrico`) REFERENCES `dispositivo` (`id_biometrico`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `motivos_permisos`
--
ALTER TABLE `motivos_permisos`
  ADD CONSTRAINT `motivos_permisos_ibfk_1` FOREIGN KEY (`id_empresa`) REFERENCES `empresas` (`id_empresas`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `permisos`
--
ALTER TABLE `permisos`
  ADD CONSTRAINT `permisos_ibfk_1` FOREIGN KEY (`id_empresa`) REFERENCES `empresas` (`id_empresas`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `permisos_ibfk_2` FOREIGN KEY (`id_motivo`) REFERENCES `motivos_permisos` (`id_motivo`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `permisos_ibfk_3` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuarios`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `sucursal`
--
ALTER TABLE `sucursal`
  ADD CONSTRAINT `sucursal_ibfk_1` FOREIGN KEY (`empresas_id_empresas`) REFERENCES `empresas` (`id_empresas`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `tipo_contrato`
--
ALTER TABLE `tipo_contrato`
  ADD CONSTRAINT `tipo_contrato_ibfk_1` FOREIGN KEY (`id_empresa`) REFERENCES `empresas` (`id_empresas`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `tipo_vacaciones`
--
ALTER TABLE `tipo_vacaciones`
  ADD CONSTRAINT `tipo_vacaciones_ibfk_1` FOREIGN KEY (`id_empresas`) REFERENCES `empresas` (`id_empresas`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `turnos`
--
ALTER TABLE `turnos`
  ADD CONSTRAINT `turnos_ibfk_1` FOREIGN KEY (`id_empresa`) REFERENCES `empresas` (`id_empresas`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`id_empresa`) REFERENCES `empresas` (`id_empresas`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `usuarios_ibfk_2` FOREIGN KEY (`id_sucursal`) REFERENCES `sucursal` (`id_sucursal`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `usuarios_ibfk_3` FOREIGN KEY (`id_centrocosto`) REFERENCES `centro_costo` (`id_centro`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `usuarios_ibfk_4` FOREIGN KEY (`id_tipocontrato`) REFERENCES `tipo_contrato` (`id_tipocontrato`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `usuarios_ibfk_5` FOREIGN KEY (`id_departamento`) REFERENCES `departamentos` (`id_departamento`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `usuarios_ibfk_6` FOREIGN KEY (`turnos_id_turnos`) REFERENCES `turnos` (`id_turnos`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `vacaciones`
--
ALTER TABLE `vacaciones`
  ADD CONSTRAINT `vacaciones_ibfk_1` FOREIGN KEY (`id_tipovacacion`) REFERENCES `tipo_vacaciones` (`id_tipovacacion`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `vacaciones_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuarios`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
