-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 18-03-2025 a las 14:32:51
-- Versión del servidor: 10.11.10-MariaDB
-- Versión de PHP: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `u369746653_ibymeturnos`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` time NOT NULL,
  `status` enum('pending','confirmed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `credits_refunded` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `appointments`
--

INSERT INTO `appointments` (`id`, `user_id`, `service_id`, `appointment_date`, `appointment_time`, `status`, `created_at`, `credits_refunded`) VALUES
(8, 6, 1, '2025-03-16', '09:00:00', 'cancelled', '2025-03-16 06:35:51', 0),
(9, 6, 1, '2025-03-21', '00:00:00', 'cancelled', '2025-03-16 07:41:22', 0),
(10, 6, 1, '2025-03-22', '00:00:00', 'cancelled', '2025-03-16 08:03:22', 0),
(11, 6, 1, '2025-03-30', '00:00:00', 'cancelled', '2025-03-16 08:04:43', 0),
(12, 6, 1, '2025-03-16', '09:00:00', 'cancelled', '2025-03-16 08:05:56', 0),
(13, 6, 1, '2025-03-16', '09:00:00', 'cancelled', '2025-03-16 08:07:14', 0),
(14, 6, 1, '2025-03-16', '09:30:00', 'cancelled', '2025-03-16 08:27:49', 0),
(15, 6, 1, '2025-03-16', '10:00:00', 'cancelled', '2025-03-16 08:27:55', 0),
(16, 6, 1, '2025-03-16', '10:30:00', 'cancelled', '2025-03-16 08:28:00', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `coupons`
--

CREATE TABLE `coupons` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `amount` int(11) NOT NULL,
  `is_used` tinyint(1) DEFAULT 0,
  `used_by` int(11) DEFAULT NULL,
  `used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `created_by` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `coupons`
--

INSERT INTO `coupons` (`id`, `code`, `amount`, `is_used`, `used_by`, `used_at`, `created_at`, `created_by`) VALUES
(1, '85AA25C8', 100, 1, 2, '2025-03-17 23:09:46', '2025-03-17 23:09:30', 2),
(3, 'ACE59A83', 100, 1, 6, '2025-03-18 00:29:00', '2025-03-17 23:12:09', 2),
(4, 'E2B03469', 100, 0, NULL, NULL, '2025-03-17 23:12:09', 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `credits`
--

CREATE TABLE `credits` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `credits`
--

INSERT INTO `credits` (`id`, `user_id`, `amount`, `created_at`, `updated_at`) VALUES
(1, 2, 100, '2025-03-17 23:09:46', '2025-03-17 23:09:46'),
(2, 6, 100, '2025-03-18 00:29:00', '2025-03-18 00:29:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `credit_transactions`
--

CREATE TABLE `credit_transactions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` int(11) NOT NULL,
  `type` enum('coupon','appointment','refund') NOT NULL,
  `reference_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `credit_transactions`
--

INSERT INTO `credit_transactions` (`id`, `user_id`, `amount`, `type`, `reference_id`, `created_at`) VALUES
(1, 2, 100, 'coupon', 1, '2025-03-17 23:09:46'),
(2, 6, 100, 'coupon', 3, '2025-03-18 00:29:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `duration` int(11) NOT NULL COMMENT 'Duración en minutos',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `credits_cost` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `services`
--

INSERT INTO `services` (`id`, `name`, `description`, `price`, `duration`, `created_at`, `credits_cost`) VALUES
(1, 'Corte de Pelo', 'Corte y peinado profesional', 2500.00, 60, '2025-03-13 16:51:22', 0),
(2, 'Tintura', 'Coloración completa con productos de primera calidad', 4500.00, 120, '2025-03-13 16:51:22', 0),
(3, 'Manicura', 'Tratamiento completo de manos y uñas', 2000.00, 45, '2025-03-13 16:51:22', 0),
(4, 'Pedicura', 'Tratamiento completo de pies y uñas', 2500.00, 60, '2025-03-13 16:51:22', 0),
(5, 'Depilación', 'Depilación con cera', 1800.00, 30, '2025-03-13 16:51:22', 0),
(6, 'Maquillaje', 'Maquillaje profesional', 3500.00, 60, '2025-03-13 16:51:22', 0),
(7, 'ssss', 'dddd', 10.00, 20, '2025-03-17 23:07:05', 0),
(8, 'Nutrimascotas', '2222', 0.00, 30, '2025-03-17 23:09:04', 20);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_admin` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `phone`, `created_at`, `is_admin`) VALUES
(2, 'Admin', 'admin@admin.com', '$2y$10$rKJMvwX6DjjoBJ7vCE2w7Og8xHYApTy/TnDyyyZQzo4mUdQXewkvK', '2614198986', '2025-03-13 17:03:23', 1),
(5, 'juanita', 'dddd@dd.com', '$2y$10$K4VgrLwSUl/LNeWCiJ6BE.XQocNc570TwPdy7u9BeiBIv5ufw06DG', '23111133', '2025-03-13 17:27:54', 0),
(6, 'carlos', 'usuario@usuario.com', '$2y$10$3JObLV7pFFk1jJHOVlsdcern/z4EhC7wT0oOPXUW6UYL6N6B9Vsj2', '2254444', '2025-03-13 17:28:14', 0);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Indices de la tabla `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `used_by` (`used_by`),
  ADD KEY `created_by` (`created_by`);

--
-- Indices de la tabla `credits`
--
ALTER TABLE `credits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indices de la tabla `credit_transactions`
--
ALTER TABLE `credit_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indices de la tabla `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `coupons`
--
ALTER TABLE `coupons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `credits`
--
ALTER TABLE `credits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `credit_transactions`
--
ALTER TABLE `credit_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `coupons`
--
ALTER TABLE `coupons`
  ADD CONSTRAINT `coupons_ibfk_1` FOREIGN KEY (`used_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `coupons_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Filtros para la tabla `credits`
--
ALTER TABLE `credits`
  ADD CONSTRAINT `credits_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Filtros para la tabla `credit_transactions`
--
ALTER TABLE `credit_transactions`
  ADD CONSTRAINT `credit_transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
