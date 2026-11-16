-- Agrega almacenamiento de consultas del formulario de contacto sin
-- modificar ni eliminar los datos existentes.
USE dbalexasys;

CREATE TABLE IF NOT EXISTS consultas_contacto (
    id_consulta          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre               VARCHAR(100) NOT NULL,
    correo               VARCHAR(254) NOT NULL,
    mensaje              TEXT NOT NULL,
    fecha_registro       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    notificacion_enviada TINYINT(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (id_consulta),
    KEY idx_consultas_contacto_fecha (fecha_registro)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
