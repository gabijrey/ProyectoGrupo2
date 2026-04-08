/*

----------------------------------------------------------------------------------
ESTOS INSERTS DEBEN HACERSE DESPUES DE HABER EJECUTADO INSERT_BUENO.SQL

Basicamente el proposito de este archivo es que se vean las portadas que ya tenemos en la BBDD, cuando hayan mas portadas de comics dará igual el orden de ejecucion
---------------------------------------------------------------------------------
*/



-- 3. INSERT DE 4 CÓMICS (Tipo 0)
-- Usamos 'Norma Editorial' para la mayoría de cómics/manga en España
INSERT INTO obra (titulo, descripcion, genero, anno_lanzamiento, portada, tipo, nombre_editorial) VALUES 
('Soy un ángel perdido', 'Una historia tierna sobre los lazos familiares y el destino.', 'Drama', 2026, 'imagen/angel_perdido.jpg', 0, 'Norma Editorial'),
('Carta Blanca', 'Un romance contado de forma inversa, desde el final hasta el principio.', 'Romance', 2021, 'imagen/carta_blanca.jpg', 0, 'Norma Editorial'),
('Batman: Hush', 'Uno de los misterios más grandes del Caballero Oscuro.', 'Superhéroes', 2002, 'batman_hush.jpg', 0, 'Norma Editorial'),
('Justice League: Origin', 'El renacimiento de la Liga de la Justicia contra Darkseid.', 'Superhéroes', 2011, 'imagen/jl_origin.jpg', 0, 'Norma Editorial');


-- 4. INSERT DE 4 LIBROS (Tipo 2)
-- Usamos 'B de Bolsillo' y 'Debolsillo' para narrativa
INSERT INTO obra (titulo, descripcion, genero, anno_lanzamiento, portada, tipo, nombre_editorial) VALUES 
('Project Hail Mary', 'Un hombre despierta en una nave espacial sin recordar quién es.', 'Ciencia Ficción', 2021, 'imagen/hail_mary.jpg', 2, 'B de Bolsillo'),
('The Martian', 'Un astronauta queda atrapado en Marte y debe sobrevivir con su ingenio.', 'Ciencia Ficción', 2011, 'imagen/martian.jpg', 2, 'B de Bolsillo'),
('El Imperio Final', 'Un mundo donde el héroe profetizado falló y el mal gobierna.', 'Fantasía', 2006, 'mistborn.jpg', 2, 'Debolsillo'),
('El Camino de los Reyes', 'El inicio de la épica saga del Archivo de las Tormentas.', 'Fantasía', 2010, 'imagen/way_of_kings.jpg', 2, 'B de Bolsillo');


-- 5. INSERT DE 4 MANGAS (Tipo 1)
INSERT INTO obra (titulo, descripcion, genero, anno_lanzamiento, portada, tipo, nombre_editorial) VALUES 
('Jujutsu Kaisen Vol. 1', 'Un joven se traga un dedo maldito y entra en el mundo de los hechiceros.', 'Shonen', 2018, 'imagen/jjk_1.jpg', 1, 'Norma Editorial'),
('Jujutsu Kaisen 0', 'La precuela que explica el origen de las maldiciones más fuertes.', 'Shonen', 2017, 'imagen/jjk_0.jpg', 1, 'Norma Editorial'),
('Jujutsu Kaisen Vol. 26', 'La batalla culminante en Shinjuku llega a su punto crítico.', 'Shonen', 2024, 'imagen/jjk_26.jpg', 1, 'Norma Editorial'),
('All You Need is Kill', 'Un soldado atrapado en un bucle temporal luchando contra aliens.', 'Seinen', 2014, 'imagen/all_kill.jpg', 1, 'Norma Editorial');