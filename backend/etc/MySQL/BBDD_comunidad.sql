CREATE DATABASE IF NOT EXISTS proyecto_comunidad;
USE proyecto_comunidad;

CREATE TABLE IF NOT EXISTS roles (
    id_roles INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL
);

CREATE TABLE IF NOT EXISTS usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    correo VARCHAR(100) NOT NULL UNIQUE,
    usuario VARCHAR(100) NOT NULL,
    contrasenya VARCHAR(255) NOT NULL,
    id_roles INT NOT NULL,
    CONSTRAINT fk_usuarios_roles FOREIGN KEY(id_roles) REFERENCES roles(id_roles)
);

CREATE TABLE IF NOT EXISTS noticias (
    id_noticias INT AUTO_INCREMENT PRIMARY KEY,
    nombre_noticia VARCHAR(100) NOT NULL,
    titulo VARCHAR(100) NOT NULL,
    contenido TEXT NOT NULL,
    fecha DATE NOT NULL,
    es_destacada TINYINT(1) DEFAULT 0,
    id_usuario INT NOT NULL,
    CONSTRAINT fk_noticias_usuario FOREIGN KEY(id_usuario) REFERENCES usuarios(id_usuario)
);

CREATE TABLE IF NOT EXISTS eventos (
    id_evento INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(100) NOT NULL,
    descripcion VARCHAR(255) NOT NULL,
    fecha DATE NOT NULL,
    id_usuario INT NOT NULL,
    es_destacada TINYINT(1) DEFAULT 0,
    CONSTRAINT fk_eventos_usuarios FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
);

CREATE TABLE IF NOT EXISTS votacion (
    titulo VARCHAR(255) NOT NULL,
    id_votacion INT AUTO_INCREMENT PRIMARY KEY,
    descripcion TEXT,
    fecha_inicio DATETIME NOT NULL,
    fecha_fin DATETIME NOT NULL,
    id_usuario INT NOT NULL,
    CONSTRAINT fk_votacion_usuarios FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
);

CREATE TABLE IF NOT EXISTS opciones_votacion (
    id_opcion INT AUTO_INCREMENT PRIMARY KEY,
    votacion_id INT NOT NULL,
    texto_opcion VARCHAR(255) NOT NULL,
    FOREIGN KEY (votacion_id) REFERENCES votacion(id_votacion) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS voto (
    id_voto INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_votacion INT NOT NULL,
    id_opcion_votada INT NOT NULL,
    fecha_voto DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_voto_usuarios FOREIGN KEY(id_usuario) REFERENCES usuarios(id_usuario),
    CONSTRAINT fk_voto_votacion FOREIGN KEY(id_votacion) REFERENCES votacion(id_votacion),
    CONSTRAINT fk_voto_opcion FOREIGN KEY(id_opcion_votada) REFERENCES opciones_votacion(id_opcion),
    UNIQUE KEY (id_usuario, id_votacion)
);

CREATE TABLE IF NOT EXISTS baneo (
    id_baneo INT AUTO_INCREMENT PRIMARY KEY,
    motivo VARCHAR(255) NOT NULL,
    fecha_inicio DATETIME,
    fecha_fin DATETIME,
    id_usuario INT NOT NULL,
    CONSTRAINT fk_baneo_usuarios FOREIGN KEY(id_usuario) REFERENCES usuarios(id_usuario)
);

CREATE TABLE IF NOT EXISTS hilo (
    id_hilo INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(100) NOT NULL,
    fecha DATE NOT NULL,
    id_foro INT NOT NULL,
    id_usuario INT NOT NULL,
    CONSTRAINT fk_hilo_usuarios FOREIGN KEY(id_usuario) REFERENCES usuarios(id_usuario)
);

CREATE TABLE IF NOT EXISTS respuesta (
    id_respuesta INT AUTO_INCREMENT PRIMARY KEY,
    contenido VARCHAR(255) NOT NULL,
    fecha DATE NOT NULL,
    id_hilo INT NOT NULL,
    id_usuario INT NOT NULL,
    CONSTRAINT fk_respuesta_usuarios FOREIGN KEY(id_usuario) REFERENCES usuarios(id_usuario)
);

CREATE TABLE foro (
    id_foro INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100)
);

ALTER TABLE hilo
    ADD CONSTRAINT fk_hilo_foro
    FOREIGN KEY (id_foro) REFERENCES foro(id_foro);

CREATE TABLE IF NOT EXISTS reserva_zona_comun (
    id_reserva INT AUTO_INCREMENT PRIMARY KEY,
    fecha_reserva DATETIME NOT NULL,
    zona VARCHAR(100),
    id_usuario INT NOT NULL,
    CONSTRAINT fk_reserva_zona_comun_usuarios FOREIGN KEY(id_usuario) REFERENCES usuarios(id_usuario)
);

CREATE TABLE IF NOT EXISTS incidencia (
    id_incidencia INT AUTO_INCREMENT PRIMARY KEY,
    descripcion VARCHAR(255) NOT NULL,
    id_usuario INT NOT NULL,
    fecha_reporte DATETIME NOT NULL,
    estado ENUM('Pendiente', 'En proceso', 'Resuelta') NOT NULL DEFAULT 'PENDIENTE',
    CONSTRAINT fk_incidencia_usuarios FOREIGN KEY(id_usuario) REFERENCES usuarios(id_usuario)
);

CREATE TABLE IF NOT EXISTS likes_hilo (
    id_like INT AUTO_INCREMENT PRIMARY KEY,
    id_hilo INT NOT NULL,
    id_usuario INT NOT NULL,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_like (id_hilo, id_usuario),
    FOREIGN KEY (id_hilo) REFERENCES hilo(id_hilo),
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
);

CREATE TABLE IF NOT EXISTS dislikes_hilo (
    id_dislike INT AUTO_INCREMENT PRIMARY KEY,
    id_hilo INT NOT NULL,
    id_usuario INT NOT NULL,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_dislike (id_hilo, id_usuario),
    FOREIGN KEY (id_hilo) REFERENCES hilo(id_hilo),
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
);

ALTER TABLE usuarios
ADD COLUMN baneado BOOLEAN DEFAULT FALSE;

ALTER TABLE usuarios
ADD COLUMN fecha_fin_timeout DATETIME NULL;

INSERT INTO roles (id_roles , nombre) VALUES (1, 'Admin'), (2, 'Presidente'), (3, 'Vecino');

ALTER TABLE hilo
ADD COLUMN contenido VARCHAR(255) NOT NULL;

ALTER TABLE usuarios
ADD COLUMN email_verificado TINYINT(1) DEFAULT 0,
ADD COLUMN verification_token VARCHAR(255) DEFAULT NULL;

INSERT INTO foro (id_foro, nombre) VALUES (1, 'General');

INSERT INTO usuarios (id_usuario, nombre, correo, usuario, contrasenya, id_roles) 
VALUES
(1, 'Admin Ejemplo', 'admin@comunidad.com', 'admin1', '$2y$10$w3C.OpqLs.I5ESk1b9CjdeiHB8dMe.ERlg875kR1.nSuNhBY4RUs.', 1),
(2, 'Presidente Ejemplo', 'presidente@comunidad.com', 'presi1', '$2y$10$w3C.OpqLs.I5ESk1b9CjdeiHB8dMe.ERlg875kR1.nSuNhBY4RUs.', 2),
(3, 'Vecino Ejemplo', 'vecino@comunidad.com', 'vecino1', '$2y$10$w3C.OpqLs.I5ESk1b9CjdeiHB8dMe.ERlg875kR1.nSuNhBY4RUs.', 3);

CREATE TABLE likes_respuestas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_respuesta INT NOT NULL,
    id_usuario INT NOT NULL,
    tipo ENUM('like', 'dislike') NOT NULL,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (id_respuesta, id_usuario),
    FOREIGN KEY (id_respuesta) REFERENCES respuesta(id_respuesta) ON DELETE CASCADE,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
);

INSERT INTO eventos (titulo, descripcion, fecha, id_usuario)
VALUES
('Reunión General', 'Reunión general para discutir temas importantes de la comunidad.', '2024-05-10', 3),
('Mantenimiento de Ascensores', 'Mantenimiento programado de los ascensores en todo el edificio.', '2025-11-15', 3),
('Fiesta de Navidad', 'Celebra con nosotros la fiesta de Navidad de la comunidad.', '2026-12-20', 3),
('Junta Extraordinaria', 'Junta extraordinaria para resolver problemas de la comunidad.', '2026-01-22', 3),
('Reparación de Fachada', 'Reparación de la fachada del edificio programada para este mes.', '2025-03-17', 3);

CREATE TABLE IF NOT EXISTS aforo_zona (
    zona VARCHAR(100) PRIMARY KEY,
    aforo_maximo INT NOT NULL
);

INSERT INTO aforo_zona (zona, aforo_maximo) VALUES
('Piscina', 20),
('Pista de tenis', 1),
('Gimnasio', 10),
('Sala de reuniones', 1),
('Barbacoa', 2);

ALTER TABLE eventos
ADD COLUMN valor_destacado TINYINT DEFAULT 5,
ADD CONSTRAINT chk_valor_destacado CHECK (valor_destacado BETWEEN 1 AND 5);

ALTER TABLE noticias
ADD COLUMN valor_destacado TINYINT DEFAULT 5,
ADD CONSTRAINT chk_valor_destacado CHECK (valor_destacado BETWEEN 1 AND 5);

UPDATE eventos 
SET valor_destacado = 5
WHERE valor_destacado IS NULL;

UPDATE noticias
SET valor_destacado = 5
WHERE valor_destacado IS NULL;

ALTER TABLE noticias ADD imagen VARCHAR(255) DEFAULT '/Proyecto-Comunidad/web/etc/assets/img/bloque.jpg';
ALTER TABLE eventos ADD imagen VARCHAR(255) DEFAULT '/Proyecto-Comunidad/web/etc/assets/img/bloque.jpg';