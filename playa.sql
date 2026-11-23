-- Base de datos del proyecto Hoteles de Playa.
-- Ejecuta este archivo una vez desde phpMyAdmin o MySQL.

DROP DATABASE IF EXISTS playa;
CREATE DATABASE playa CHARACTER SET utf8;
USE playa;

-- Categorías que aparecen en el catálogo.
CREATE TABLE categorias (
  nombre VARCHAR(50) NOT NULL,
  descripcion VARCHAR(255),
  PRIMARY KEY (nombre)
);

-- Usuarios registrados y cuentas de prueba.
CREATE TABLE usuarios (
  usuario_id INT NOT NULL AUTO_INCREMENT,
  nombre VARCHAR(100) NOT NULL,
  correo VARCHAR(100) NOT NULL,
  usuario VARCHAR(50) NOT NULL,
  contrasena VARCHAR(255) NOT NULL,
  rol VARCHAR(20) NOT NULL,
  PRIMARY KEY (usuario_id),
  UNIQUE KEY (correo),
  UNIQUE KEY (usuario)
);

-- La columna categoria guarda el nombre que aparece en la tabla categorías.
CREATE TABLE habitaciones (
  id INT NOT NULL AUTO_INCREMENT,
  nombre VARCHAR(100) NOT NULL,
  categoria VARCHAR(50) NOT NULL,
  descripcion VARCHAR(255) NOT NULL,
  precio DECIMAL(10,2) NOT NULL,
  disponibles INT NOT NULL,
  PRIMARY KEY (id),
  FOREIGN KEY (categoria) REFERENCES categorias (nombre)
);

-- Una habitación puede tener varias imágenes. Aquí solo se guarda la ruta.
CREATE TABLE imagenes (
  id_imagen INT NOT NULL AUTO_INCREMENT,
  id_habitacion INT NOT NULL,
  ruta VARCHAR(255) NOT NULL,
  PRIMARY KEY (id_imagen),
  FOREIGN KEY (id_habitacion) REFERENCES habitaciones (id)
    ON DELETE CASCADE
);

-- Datos de la estancia para la reservación.
CREATE TABLE reservaciones (
  id_reservacion INT NOT NULL AUTO_INCREMENT,
  usuario_id INT NOT NULL,
  fecha_entrada DATE NOT NULL,
  fecha_salida DATE NOT NULL,
  total DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (id_reservacion),
  FOREIGN KEY (usuario_id) REFERENCES usuarios (usuario_id)
);

CREATE TABLE detalle_reservacion (
  id_detalle INT NOT NULL AUTO_INCREMENT,
  id_reservacion INT NOT NULL,
  id_habitacion INT NOT NULL,
  cantidad INT NOT NULL,
  -- precio_unitario es por noche; subtotal = precio_unitario * cantidad * noches.
  -- noches se obtiene con la diferencia entre fecha_salida y fecha_entrada.
  precio_unitario DECIMAL(10,2) NOT NULL,
  subtotal DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (id_detalle),
  FOREIGN KEY (id_reservacion) REFERENCES reservaciones (id_reservacion)
    ON DELETE CASCADE,
  FOREIGN KEY (id_habitacion) REFERENCES habitaciones (id)
    ON DELETE RESTRICT
);

-- Categorías de ejemplo.
INSERT INTO categorias (nombre, descripcion) VALUES
  ('Estándar', 'Habitación cómoda para dos personas.'),
  ('Deluxe', 'Habitación amplia con balcón.'),
  ('Suite', 'Habitación amplia para una familia.');

-- Habitaciones de ejemplo. Los precios están expresados en pesos.
INSERT INTO habitaciones (nombre, categoria, descripcion, precio, disponibles) VALUES
  ('Habitación frente al jardín', 'Estándar', 'Habitación para dos personas con vista al jardín.', 1200.00, 8),
  ('Habitación deluxe con balcón', 'Deluxe', 'Habitación con balcón y vista parcial al mar.', 1850.00, 5),
  ('Suite familiar', 'Suite', 'Habitación para una familia de hasta cuatro personas.', 2600.00, 3);

-- Rutas de ejemplo. Las imágenes están en imagenes/habitaciones/.
INSERT INTO imagenes (id_habitacion, ruta) VALUES
  (1, 'imagenes/habitaciones/estandar.jpg'),
  (2, 'imagenes/habitaciones/deluxe.jpg'),
  (3, 'imagenes/habitaciones/suite.jpg');

-- Cuenta de administrador de prueba: usuario admin, contraseña admin123.
-- Cuenta de huésped de prueba: usuario huesped_prueba, contraseña huesped123.
-- Ambas contraseñas están guardadas con hashes compatibles con password_verify().
INSERT INTO usuarios (nombre, correo, usuario, contrasena, rol) VALUES
  ('Administrador', 'admin@hoteles-playa.local', 'admin', '$2y$10$sY6nlihg2SeS78ji3ZjzbuX.oekyRZCrOk43N1HsSPvr7qUZqFh2i', 'admin'),
  ('Huésped de prueba', 'huesped@hoteles-playa.local', 'huesped_prueba', '$2y$10$LTwzB0JzDXgnnORzRZ9feO2QWDe2lMGHP3IxKpk95.9I39m6AUmSy', 'huesped');
