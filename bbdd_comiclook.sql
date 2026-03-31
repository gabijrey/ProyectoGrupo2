/*
-- Crear usuario
CREATE USER 'admin'@'localhost' IDENTIFIED BY 'admin';
GRANT ALL PRIVILEGES ON *.* TO 'admin'@'localhost';
FLUSH PRIVILEGES;
*/

/*
-----------------------------------------------------------------------------
Cuando estes trabajando en local, por favor descomenta el codigo debajo de los comentarios que ponen "local" entre parentesis. Cuando termines de hacer alguna modificación vuelve a comentarlo.


------------------------------------------------------------------------------
*/

/*
-- Borrar BBDD si ya existe (hosting)
DROP SCHEMA IF EXISTS if0_41430615_comiclook;

-- Crear BBDD (hosting)
CREATE SCHEMA if0_41430615_comiclook;
USE if0_41430615_comiclook;
*/

/*
-- Borrar base de datos si ya existe (local)
DROP SCHEMA IF EXISTS comiclook;

-- CREAR BBDD (local)
CREATE DATABASE comiclook;
USE comiclook;
*/


/*Borrar las tablas una a una*/
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS obra_autor;
DROP TABLE IF EXISTS obra_categoria;
DROP TABLE IF EXISTS categoria;
DROP TABLE IF EXISTS resena;
DROP TABLE IF EXISTS suscripcion;
DROP TABLE IF EXISTS obra;
DROP TABLE IF EXISTS usuario;
DROP TABLE IF EXISTS autor;
DROP TABLE IF EXISTS editorial;

SET FOREIGN_KEY_CHECKS = 1;

/*--------TABLAS------*/
-- Tabla de editoriales
CREATE TABLE editorial(
nombre VARCHAR(100) PRIMARY KEY,
anno_fundacion INT,
ubicacion VARCHAR(255)
);

-- Tabla de autores
CREATE TABLE autor(
id INT PRIMARY KEY auto_increment,
nombre VARCHAR(100),
apellidos VARCHAR(100),
email VARCHAR(255),
num_obras INT
);

-- Tabla usuario
CREATE TABLE usuario (
nombre VARCHAR(255) PRIMARY KEY,
email VARCHAR(255) UNIQUE,
-- FOREIGN KEY (email) REFERENCES autor(email),
contrasena VARCHAR(150),
fecha_registro DATE,
rol INT,
bio TEXT,
img_perfil VARCHAR(255)
);

-- Tabla obra (comic, manga, libro)
CREATE TABLE obra (
id INT PRIMARY KEY auto_increment,
titulo VARCHAR(255),
descripcion TEXT, -- Para poder escribir una sinopsis extensa
genero VARCHAR(80),
anno_lanzamiento INT,
portada VARCHAR(255),
id_autor INT,
tipo INT,
nombre_editorial VARCHAR(100),
FOREIGN KEY (nombre_editorial) REFERENCES editorial(nombre)
);

-- Tabla categoria
CREATE TABLE categoria (
id INT PRIMARY KEY auto_increment,
nombre VARCHAR(50) UNIQUE
);

-- Tabla intermedia entre obra y autor
CREATE TABLE obra_autor (
id_obra INT,
id_autor INT,
-- rol_autor VARCHAR(50), -- Ejemplo: 'Dibujante', 'Guionista'
PRIMARY KEY (id_obra, id_autor),
FOREIGN KEY (id_obra) REFERENCES obra(id),
FOREIGN KEY (id_autor) REFERENCES autor(id)
);

-- Tabla intermedia entre obra y categoria
CREATE TABLE obra_categoria (
id_obra INT,
id_categoria INT,
PRIMARY KEY (id_obra, id_categoria),
FOREIGN KEY (id_obra) REFERENCES obra(id),
FOREIGN KEY (id_categoria) REFERENCES categoria(id)
);

-- Tabla de reseñas
CREATE TABLE resena (
id INT PRIMARY KEY auto_increment,
fecha_public DATE,
puntuacion INT,
nombre_usuario VARCHAR(255),
id_obra INT,
FOREIGN KEY (nombre_usuario) REFERENCES usuario(nombre),
FOREIGN KEY (id_obra) REFERENCES obra(id)
);

-- Tabla de suscripciones
CREATE TABLE suscripcion(
id INT PRIMARY KEY auto_increment,
nombre_usuario VARCHAR(255),
FOREIGN KEY (nombre_usuario) REFERENCES usuario(nombre),
metodo_pago VARCHAR(50),
estado BOOLEAN,
fecha_inicio DATE,
fecha_cancelacion DATE
);