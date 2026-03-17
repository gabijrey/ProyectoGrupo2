-- Borrar base de datos si ya existe
DROP SCHEMA IF EXISTS comiclook;

CREATE USER 'admin'@'localhost' IDENTIFIED BY 'admin';
GRANT ALL PRIVILEGES ON *.* TO 'admin'@'localhost';
FLUSH PRIVILEGES;

-- CREAR BBDD
CREATE DATABASE comiclook;
USE comiclook;

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
num_obras INT
);

-- Tabla usuario
CREATE TABLE usuario (
id INT PRIMARY KEY auto_increment,
nombre VARCHAR(255),
email VARCHAR(255),
contrasena VARCHAR(150),
fecha_registro DATE,
rol INT,
nacionalidad VARCHAR(150)
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
nombre_editorial VARCHAR(100),
FOREIGN KEY (nombre_editorial) REFERENCES editorial(nombre)
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

-- Tabla de reseñas
CREATE TABLE resena (
id INT PRIMARY KEY auto_increment,
fecha_public DATE,
puntuacion INT,
id_usuario INT,
id_obra INT,
FOREIGN KEY (id_usuario) REFERENCES usuario(id),
FOREIGN KEY (id_obra) REFERENCES obra(id)
);

-- Tabla de suscripciones
CREATE TABLE suscripcion(
id INT PRIMARY KEY auto_increment,
id_usuario INT,
FOREIGN KEY (id_usuario) REFERENCES usuario(id),
metodo_pago VARCHAR(50),
estado BOOLEAN,
fecha_inicio DATE,
fecha_cancelacion DATE
);








