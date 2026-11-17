-- Base de datos del proyecto Hoteles de Playa.
-- Ejecuta este archivo una vez desde phpMyAdmin o MySQL.

CREATE DATABASE hoteles_de_playa CHARACTER SET utf8;
USE hoteles_de_playa;

-- Categorias que aparecen en el catalogo.
CREATE TABLE categorias (
  nombre VARCHAR(50) NOT NULL,
  descripcion VARCHAR(255),
  PRIMARY KEY (nombre)
);

-- Usuarios registrados y cuenta de administrador.
CREATE TABLE usuarios (
  usuario_id INT NOT NULL AUTO_INCREMENT,
  nombre VARCHAR(100) NOT NULL,
  correo VARCHAR(100) NOT NULL,
  usuario VARCHAR(50) NOT NULL,
  contrasena VARCHAR(50) NOT NULL,
  rol VARCHAR(20) NOT NULL,
  PRIMARY KEY (usuario_id)
);

-- La columna categoria guarda el nombre que aparece en la tabla categorias.
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

-- Una habitacion puede tener varias imagenes. Aqui solo se guarda la ruta.
CREATE TABLE imagenes (
  id_imagen INT NOT NULL AUTO_INCREMENT,
  id_habitacion INT NOT NULL,
  ruta VARCHAR(255) NOT NULL,
  PRIMARY KEY (id_imagen),
  FOREIGN KEY (id_habitacion) REFERENCES habitaciones (id)
    ON DELETE CASCADE
);

-- Propuesta sencilla para CU-15. Jarib debe confirmar estos campos.
CREATE TABLE reservaciones (
  id_reservacion INT NOT NULL AUTO_INCREMENT,
  usuario_id INT NOT NULL,
  total DECIMAL(10,2) NOT NULL,
  estado VARCHAR(20) NOT NULL,
  PRIMARY KEY (id_reservacion),
  FOREIGN KEY (usuario_id) REFERENCES usuarios (usuario_id)
);

CREATE TABLE detalle_reservacion (
  id_detalle INT NOT NULL AUTO_INCREMENT,
  id_reservacion INT NOT NULL,
  id_habitacion INT NOT NULL,
  cantidad INT NOT NULL,
  precio_unitario DECIMAL(10,2) NOT NULL,
  subtotal DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (id_detalle),
  FOREIGN KEY (id_reservacion) REFERENCES reservaciones (id_reservacion)
    ON DELETE CASCADE,
  FOREIGN KEY (id_habitacion) REFERENCES habitaciones (id)
    ON DELETE RESTRICT
);

-- Categorias de ejemplo.
INSERT INTO categorias (nombre, descripcion) VALUES
  ('Estandar', 'Habitacion comoda para dos personas.'),
  ('Deluxe', 'Habitacion amplia con balcon.'),
  ('Suite', 'Habitacion amplia para una familia.');

-- Habitaciones de ejemplo. Los precios estan expresados en pesos.
INSERT INTO habitaciones (nombre, categoria, descripcion, precio, disponibles) VALUES
  ('Habitacion frente al jardin', 'Estandar', 'Habitacion para dos personas con vista al jardin.', 1200.00, 8),
  ('Habitacion deluxe con balcon', 'Deluxe', 'Habitacion con balcon y vista parcial al mar.', 1850.00, 5),
  ('Suite familiar', 'Suite', 'Habitacion para una familia de hasta cuatro personas.', 2600.00, 3);

-- Rutas de ejemplo. Agrega las imagenes con estos nombres dentro de esta carpeta.
INSERT INTO imagenes (id_habitacion, ruta) VALUES
  (1, 'imagenes/habitaciones/estandar.jpg'),
  (2, 'imagenes/habitaciones/deluxe.jpg'),
  (3, 'imagenes/habitaciones/suite.jpg');

-- Cuenta de administrador de ejemplo: usuario admin, contrasena admin123.
INSERT INTO usuarios (nombre, correo, usuario, contrasena, rol) VALUES
  ('Administrador', 'admin@hoteles-playa.local', 'admin', 'admin123', 'admin');
