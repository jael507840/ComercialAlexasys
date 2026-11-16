-- =====================================================================
-- SISTEMA ALEX@SYS
-- Base de datos: dbAlexasys (19 tablas)
-- Motor: InnoDB / MySQL 8 - MariaDB 10.4+
-- =====================================================================
-- IMPORTANTE:
--   Este script REINICIA las 19 tablas del esquema dbAlexasys.
--   No toca otras bases de datos del servidor.
--   Los usuarios y datos incluidos son DEMOSTRATIVOS.
--   Cambie las contraseñas antes de operar en producción.
-- =====================================================================

SET NAMES utf8mb4;
SET time_zone = '-04:00';
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS dbAlexasys
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
USE dbAlexasys;

DROP TABLE IF EXISTS garantias, pagos_servicio, repuestos_servicio,
    detalle_servicio, ordenes_servicio, equipos_cliente, pagos_venta,
    detalle_venta, ventas, movimientos_inventario, detalle_compra,
    compras, productos, categorias, proveedores, usuario_rol, roles,
    consultas_contacto,
    usuarios, clientes;

SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------
-- 1. clientes
-- ---------------------------------------------------------------------
CREATE TABLE clientes (
    id_cliente      INT UNSIGNED NOT NULL AUTO_INCREMENT,
    ci              VARCHAR(20)  NOT NULL,
    nombres         VARCHAR(100) NOT NULL,
    apellidos       VARCHAR(100) NOT NULL,
    telefono        VARCHAR(20)  DEFAULT NULL,
    direccion       VARCHAR(200) DEFAULT NULL,
    correo          VARCHAR(120) DEFAULT NULL,
    fecha_registro  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    estado          ENUM('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
    PRIMARY KEY (id_cliente),
    UNIQUE KEY uq_clientes_ci (ci),
    KEY idx_clientes_nombre (apellidos, nombres),
    KEY idx_clientes_telefono (telefono)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 2. usuarios
-- ---------------------------------------------------------------------
CREATE TABLE usuarios (
    id_usuario      INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombres         VARCHAR(100) NOT NULL,
    apellidos       VARCHAR(100) NOT NULL,
    usuario         VARCHAR(50)  NOT NULL,
    password_hash   VARCHAR(255) NOT NULL,
    telefono        VARCHAR(20)  DEFAULT NULL,
    estado          ENUM('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
    fecha_registro  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_usuario),
    UNIQUE KEY uq_usuarios_usuario (usuario),
    KEY idx_usuarios_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 3. roles
-- ---------------------------------------------------------------------
CREATE TABLE roles (
    id_rol       INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre       VARCHAR(50)  NOT NULL,
    descripcion  VARCHAR(200) DEFAULT NULL,
    PRIMARY KEY (id_rol),
    UNIQUE KEY uq_roles_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 4. usuario_rol
-- ---------------------------------------------------------------------
CREATE TABLE usuario_rol (
    id_usuario  INT UNSIGNED NOT NULL,
    id_rol      INT UNSIGNED NOT NULL,
    PRIMARY KEY (id_usuario, id_rol),
    KEY idx_usuario_rol_rol (id_rol),
    CONSTRAINT fk_usuario_rol_usuario FOREIGN KEY (id_usuario)
        REFERENCES usuarios (id_usuario) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_usuario_rol_rol FOREIGN KEY (id_rol)
        REFERENCES roles (id_rol) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 5. categorias
-- ---------------------------------------------------------------------
CREATE TABLE categorias (
    id_categoria  INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre        VARCHAR(80)  NOT NULL,
    descripcion   VARCHAR(200) DEFAULT NULL,
    estado        ENUM('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
    PRIMARY KEY (id_categoria),
    UNIQUE KEY uq_categorias_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 6. productos
--    imagen: atributo auxiliar para el catálogo público (ruta local).
-- ---------------------------------------------------------------------
CREATE TABLE productos (
    id_producto   INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_categoria  INT UNSIGNED NOT NULL,
    codigo        VARCHAR(30)  NOT NULL,
    nombre        VARCHAR(120) NOT NULL,
    descripcion   TEXT         DEFAULT NULL,
    marca         VARCHAR(60)  DEFAULT NULL,
    modelo        VARCHAR(60)  DEFAULT NULL,
    imagen        VARCHAR(255) DEFAULT NULL,
    precio_venta  DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    stock_actual  INT UNSIGNED NOT NULL DEFAULT 0,
    stock_minimo  INT UNSIGNED NOT NULL DEFAULT 0,
    estado        ENUM('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
    PRIMARY KEY (id_producto),
    UNIQUE KEY uq_productos_codigo (codigo),
    KEY idx_productos_categoria (id_categoria),
    KEY idx_productos_nombre (nombre),
    KEY idx_productos_estado (estado),
    CONSTRAINT fk_productos_categoria FOREIGN KEY (id_categoria)
        REFERENCES categorias (id_categoria) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT chk_productos_precio CHECK (precio_venta >= 0),
    CONSTRAINT chk_productos_stock CHECK (stock_actual >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 7. proveedores
-- ---------------------------------------------------------------------
CREATE TABLE proveedores (
    id_proveedor  INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre        VARCHAR(120) NOT NULL,
    nit           VARCHAR(20)  DEFAULT NULL,
    telefono      VARCHAR(20)  DEFAULT NULL,
    direccion     VARCHAR(200) DEFAULT NULL,
    correo        VARCHAR(120) DEFAULT NULL,
    estado        ENUM('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
    PRIMARY KEY (id_proveedor),
    KEY idx_proveedores_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 8. compras
-- ---------------------------------------------------------------------
CREATE TABLE compras (
    id_compra           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_proveedor        INT UNSIGNED NOT NULL,
    id_usuario          INT UNSIGNED NOT NULL,
    numero_comprobante  VARCHAR(30)  NOT NULL,
    fecha_compra        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    total_compra        DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    observacion         TEXT         DEFAULT NULL,
    estado              ENUM('Registrada','Anulada') NOT NULL DEFAULT 'Registrada',
    PRIMARY KEY (id_compra),
    UNIQUE KEY uq_compras_comprobante (numero_comprobante),
    KEY idx_compras_proveedor (id_proveedor),
    KEY idx_compras_fecha (fecha_compra),
    KEY idx_compras_usuario (id_usuario),
    CONSTRAINT fk_compras_proveedor FOREIGN KEY (id_proveedor)
        REFERENCES proveedores (id_proveedor) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_compras_usuario FOREIGN KEY (id_usuario)
        REFERENCES usuarios (id_usuario) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT chk_compras_total CHECK (total_compra >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 9. detalle_compra
-- ---------------------------------------------------------------------
CREATE TABLE detalle_compra (
    id_detalle_compra  INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_compra          INT UNSIGNED NOT NULL,
    id_producto        INT UNSIGNED NOT NULL,
    cantidad           INT UNSIGNED NOT NULL,
    costo_unitario     DECIMAL(12,2) NOT NULL,
    subtotal           DECIMAL(12,2) NOT NULL,
    PRIMARY KEY (id_detalle_compra),
    KEY idx_detalle_compra_compra (id_compra),
    KEY idx_detalle_compra_producto (id_producto),
    CONSTRAINT fk_detalle_compra_compra FOREIGN KEY (id_compra)
        REFERENCES compras (id_compra) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_detalle_compra_producto FOREIGN KEY (id_producto)
        REFERENCES productos (id_producto) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT chk_detalle_compra_cantidad CHECK (cantidad > 0),
    CONSTRAINT chk_detalle_compra_costo CHECK (costo_unitario >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 10. movimientos_inventario
--     Referencia polimórfica: referencia_tipo + referencia_id.
--     Índice único para impedir doble registro del mismo documento.
-- ---------------------------------------------------------------------
CREATE TABLE movimientos_inventario (
    id_movimiento     INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_producto       INT UNSIGNED NOT NULL,
    id_usuario        INT UNSIGNED NOT NULL,
    fecha_movimiento  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    tipo_movimiento   ENUM('Entrada','Salida','Ajuste') NOT NULL,
    cantidad          INT UNSIGNED NOT NULL,
    referencia_tipo   ENUM('Compra','Venta','Repuesto','Ajuste','Apertura') NOT NULL,
    referencia_id     INT UNSIGNED DEFAULT NULL,
    observacion       VARCHAR(255) DEFAULT NULL,
    PRIMARY KEY (id_movimiento),
    UNIQUE KEY uq_mov_ref (referencia_tipo, referencia_id, id_producto),
    KEY idx_mov_producto (id_producto),
    KEY idx_mov_fecha (fecha_movimiento),
    KEY idx_mov_usuario (id_usuario),
    CONSTRAINT fk_mov_producto FOREIGN KEY (id_producto)
        REFERENCES productos (id_producto) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_mov_usuario FOREIGN KEY (id_usuario)
        REFERENCES usuarios (id_usuario) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT chk_mov_cantidad CHECK (cantidad > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 11. ventas
-- ---------------------------------------------------------------------
CREATE TABLE ventas (
    id_venta            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_cliente          INT UNSIGNED NOT NULL,
    id_usuario          INT UNSIGNED NOT NULL,
    fecha_venta         DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    numero_comprobante  VARCHAR(30)  NOT NULL,
    subtotal            DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    descuento           DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    total               DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    observacion         TEXT DEFAULT NULL,
    estado              ENUM('Confirmada','Anulada') NOT NULL DEFAULT 'Confirmada',
    PRIMARY KEY (id_venta),
    UNIQUE KEY uq_ventas_comprobante (numero_comprobante),
    KEY idx_ventas_cliente (id_cliente),
    KEY idx_ventas_usuario (id_usuario),
    KEY idx_ventas_fecha (fecha_venta),
    CONSTRAINT fk_ventas_cliente FOREIGN KEY (id_cliente)
        REFERENCES clientes (id_cliente) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_ventas_usuario FOREIGN KEY (id_usuario)
        REFERENCES usuarios (id_usuario) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT chk_ventas_totales CHECK (subtotal >= 0 AND descuento >= 0 AND total >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 12. detalle_venta
-- ---------------------------------------------------------------------
CREATE TABLE detalle_venta (
    id_detalle_venta  INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_venta          INT UNSIGNED NOT NULL,
    id_producto       INT UNSIGNED NOT NULL,
    cantidad          INT UNSIGNED NOT NULL,
    precio_unitario   DECIMAL(12,2) NOT NULL,
    descuento         DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    subtotal          DECIMAL(12,2) NOT NULL,
    PRIMARY KEY (id_detalle_venta),
    KEY idx_detalle_venta_venta (id_venta),
    KEY idx_detalle_venta_producto (id_producto),
    CONSTRAINT fk_detalle_venta_venta FOREIGN KEY (id_venta)
        REFERENCES ventas (id_venta) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_detalle_venta_producto FOREIGN KEY (id_producto)
        REFERENCES productos (id_producto) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT chk_detalle_venta_cantidad CHECK (cantidad > 0),
    CONSTRAINT chk_detalle_venta_precio CHECK (precio_unitario >= 0 AND descuento >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 13. pagos_venta
-- ---------------------------------------------------------------------
CREATE TABLE pagos_venta (
    id_pago_venta  INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_venta       INT UNSIGNED NOT NULL,
    id_usuario     INT UNSIGNED NOT NULL,
    fecha_pago     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    monto          DECIMAL(12,2) NOT NULL,
    metodo_pago    ENUM('Efectivo','Transferencia','QR','Tarjeta') NOT NULL DEFAULT 'Efectivo',
    referencia     VARCHAR(100) DEFAULT NULL,
    observacion    VARCHAR(200) DEFAULT NULL,
    estado         ENUM('Confirmado','Anulado') NOT NULL DEFAULT 'Confirmado',
    PRIMARY KEY (id_pago_venta),
    KEY idx_pagos_venta_venta (id_venta),
    KEY idx_pagos_venta_fecha (fecha_pago),
    KEY idx_pagos_venta_usuario (id_usuario),
    CONSTRAINT fk_pagos_venta_venta FOREIGN KEY (id_venta)
        REFERENCES ventas (id_venta) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_pagos_venta_usuario FOREIGN KEY (id_usuario)
        REFERENCES usuarios (id_usuario) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT chk_pagos_venta_monto CHECK (monto > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 14. equipos_cliente
-- ---------------------------------------------------------------------
CREATE TABLE equipos_cliente (
    id_equipo       INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_cliente      INT UNSIGNED NOT NULL,
    id_producto     INT UNSIGNED DEFAULT NULL,
    tipo_equipo     ENUM('Computadora de escritorio','Laptop','Impresora','Otro') NOT NULL DEFAULT 'Laptop',
    marca           VARCHAR(60)  DEFAULT NULL,
    modelo          VARCHAR(60)  DEFAULT NULL,
    numero_serie    VARCHAR(60)  DEFAULT NULL,
    descripcion     VARCHAR(255) DEFAULT NULL,
    fecha_registro  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_equipo),
    KEY idx_equipos_cliente (id_cliente),
    KEY idx_equipos_serie (numero_serie),
    KEY idx_equipos_producto (id_producto),
    CONSTRAINT fk_equipos_cliente FOREIGN KEY (id_cliente)
        REFERENCES clientes (id_cliente) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_equipos_producto FOREIGN KEY (id_producto)
        REFERENCES productos (id_producto) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 15. ordenes_servicio
-- ---------------------------------------------------------------------
CREATE TABLE ordenes_servicio (
    id_orden               INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_equipo              INT UNSIGNED NOT NULL,
    id_usuario_recepcion   INT UNSIGNED NOT NULL,
    id_usuario_tecnico     INT UNSIGNED DEFAULT NULL,
    numero_orden           VARCHAR(30)  NOT NULL,
    fecha_recepcion        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_entrega          DATETIME     DEFAULT NULL,
    falla_reportada        TEXT         NOT NULL,
    accesorios_recibidos   VARCHAR(255) DEFAULT NULL,
    estado                 ENUM('Recibido','En diagnostico','En reparacion','Esperando repuesto','Terminado','Entregado','Anulado')
                           NOT NULL DEFAULT 'Recibido',
    costo_servicio         DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    observaciones          TEXT DEFAULT NULL,
    PRIMARY KEY (id_orden),
    UNIQUE KEY uq_ordenes_numero (numero_orden),
    KEY idx_ordenes_equipo (id_equipo),
    KEY idx_ordenes_recepcion (id_usuario_recepcion),
    KEY idx_ordenes_tecnico (id_usuario_tecnico),
    KEY idx_ordenes_fecha (fecha_recepcion),
    KEY idx_ordenes_estado (estado),
    CONSTRAINT fk_ordenes_equipo FOREIGN KEY (id_equipo)
        REFERENCES equipos_cliente (id_equipo) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_ordenes_recepcion FOREIGN KEY (id_usuario_recepcion)
        REFERENCES usuarios (id_usuario) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_ordenes_tecnico FOREIGN KEY (id_usuario_tecnico)
        REFERENCES usuarios (id_usuario) ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT chk_ordenes_costo CHECK (costo_servicio >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 16. detalle_servicio
-- ---------------------------------------------------------------------
CREATE TABLE detalle_servicio (
    id_detalle_servicio  INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_orden             INT UNSIGNED NOT NULL,
    id_usuario_tecnico   INT UNSIGNED NOT NULL,
    fecha_intervencion   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    diagnostico          TEXT         NOT NULL,
    trabajo_realizado    TEXT         NOT NULL,
    resultado_prueba     VARCHAR(255) DEFAULT NULL,
    costo                DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    PRIMARY KEY (id_detalle_servicio),
    KEY idx_detalle_servicio_orden (id_orden),
    KEY idx_detalle_servicio_tecnico (id_usuario_tecnico),
    KEY idx_detalle_servicio_fecha (fecha_intervencion),
    CONSTRAINT fk_detalle_servicio_orden FOREIGN KEY (id_orden)
        REFERENCES ordenes_servicio (id_orden) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_detalle_servicio_tecnico FOREIGN KEY (id_usuario_tecnico)
        REFERENCES usuarios (id_usuario) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT chk_detalle_servicio_costo CHECK (costo >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 17. repuestos_servicio
-- ---------------------------------------------------------------------
CREATE TABLE repuestos_servicio (
    id_repuesto_servicio  INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_orden              INT UNSIGNED NOT NULL,
    id_producto           INT UNSIGNED NOT NULL,
    cantidad              INT UNSIGNED NOT NULL,
    costo_unitario        DECIMAL(12,2) NOT NULL,
    subtotal              DECIMAL(12,2) NOT NULL,
    PRIMARY KEY (id_repuesto_servicio),
    KEY idx_repuestos_orden (id_orden),
    KEY idx_repuestos_producto (id_producto),
    CONSTRAINT fk_repuestos_orden FOREIGN KEY (id_orden)
        REFERENCES ordenes_servicio (id_orden) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_repuestos_producto FOREIGN KEY (id_producto)
        REFERENCES productos (id_producto) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT chk_repuestos_cantidad CHECK (cantidad > 0),
    CONSTRAINT chk_repuestos_costo CHECK (costo_unitario >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 18. pagos_servicio
-- ---------------------------------------------------------------------
CREATE TABLE pagos_servicio (
    id_pago_servicio  INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_orden          INT UNSIGNED NOT NULL,
    id_usuario        INT UNSIGNED NOT NULL,
    fecha_pago        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    monto             DECIMAL(12,2) NOT NULL,
    metodo_pago       ENUM('Efectivo','Transferencia','QR','Tarjeta') NOT NULL DEFAULT 'Efectivo',
    referencia        VARCHAR(100) DEFAULT NULL,
    observacion       VARCHAR(200) DEFAULT NULL,
    estado            ENUM('Confirmado','Anulado') NOT NULL DEFAULT 'Confirmado',
    PRIMARY KEY (id_pago_servicio),
    KEY idx_pagos_servicio_orden (id_orden),
    KEY idx_pagos_servicio_fecha (fecha_pago),
    KEY idx_pagos_servicio_usuario (id_usuario),
    CONSTRAINT fk_pagos_servicio_orden FOREIGN KEY (id_orden)
        REFERENCES ordenes_servicio (id_orden) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_pagos_servicio_usuario FOREIGN KEY (id_usuario)
        REFERENCES usuarios (id_usuario) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT chk_pagos_servicio_monto CHECK (monto > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 19. garantias
--     Restricción lógica: exactamente una fuente (venta o servicio).
-- ---------------------------------------------------------------------
CREATE TABLE garantias (
    id_garantia        INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_detalle_venta   INT UNSIGNED DEFAULT NULL,
    id_orden           INT UNSIGNED DEFAULT NULL,
    fecha_inicio       DATE NOT NULL,
    fecha_fin          DATE NOT NULL,
    tipo_garantia      ENUM('Producto','Servicio') NOT NULL,
    descripcion        VARCHAR(255) NOT NULL,
    condiciones        TEXT DEFAULT NULL,
    estado             ENUM('Vigente','Vencida','Anulada') NOT NULL DEFAULT 'Vigente',
    PRIMARY KEY (id_garantia),
    KEY idx_garantias_detalle (id_detalle_venta),
    KEY idx_garantias_orden (id_orden),
    KEY idx_garantias_fechas (fecha_inicio, fecha_fin),
    KEY idx_garantias_estado (estado),
    CONSTRAINT fk_garantias_detalle FOREIGN KEY (id_detalle_venta)
        REFERENCES detalle_venta (id_detalle_venta) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_garantias_orden FOREIGN KEY (id_orden)
        REFERENCES ordenes_servicio (id_orden) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT chk_garantias_fuente CHECK (
        (id_detalle_venta IS NOT NULL AND id_orden IS NULL)
        OR (id_detalle_venta IS NULL AND id_orden IS NOT NULL)
    ),
    CONSTRAINT chk_garantias_fechas CHECK (fecha_fin >= fecha_inicio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 20. consultas_contacto
-- ---------------------------------------------------------------------
CREATE TABLE consultas_contacto (
    id_consulta          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre               VARCHAR(100) NOT NULL,
    correo               VARCHAR(254) NOT NULL,
    mensaje              TEXT NOT NULL,
    fecha_registro       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    notificacion_enviada TINYINT(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (id_consulta),
    KEY idx_consultas_contacto_fecha (fecha_registro)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- DATOS INICIALES (DEMOSTRACIÓN)
-- =====================================================================

-- Roles
INSERT INTO roles (id_rol, nombre, descripcion) VALUES
(1, 'Gerente',  'Acceso total al sistema, usuarios, compras, precios y reportes'),
(2, 'Vendedor', 'Clientes, ventas, pagos de ventas, equipos y consultas'),
(3, 'Tecnico',  'Recepción de equipos, órdenes, intervenciones, repuestos y pagos de servicio');

-- Usuarios DEMO (contraseñas indicadas en README.md)
INSERT INTO usuarios (id_usuario, nombres, apellidos, usuario, password_hash, telefono, estado) VALUES
(1, 'Usuario', 'Demo', 'gerente',
 '$2y$10$Ulmry/BzCB1l1363ADT0reAHFWPt5HLXrOPb2/ItwqudS.Q7HQ.hW', NULL, 'Activo'),
(2, 'Vendedor', 'Demo', 'vendedor',
 '$2y$10$s5ae.C3zJHJLuW1waXQD6eCKCqiC9kSLTgzepd27CI.J1U10L5l3S', NULL, 'Activo'),
(3, 'Tecnico', 'Demo', 'tecnico',
 '$2y$10$6kXpL7ZaEV1z.aP/906ZHOzjOLJsE1hIe7VoXMCXchlb0uMqZhxoW', NULL, 'Activo');

INSERT INTO usuario_rol (id_usuario, id_rol) VALUES (1,1), (2,2), (3,3);

-- Categorías
INSERT INTO categorias (id_categoria, nombre, descripcion) VALUES
(1, 'Computadoras de escritorio', 'Equipos de escritorio armados y de marca'),
(2, 'Laptops', 'Computadoras portátiles'),
(3, 'Accesorios', 'Periféricos y accesorios informáticos'),
(4, 'Componentes y repuestos', 'Memorias, discos, fuentes y repuestos'),
(5, 'Impresoras', 'Impresoras y multifuncionales');

-- Productos
-- Las imágenes están organizadas por uso en imagen/productos:
--   escritorio/  computadoras de escritorio
--   portatiles/  laptops
--   accesorios/  periféricos, componentes y repuestos
INSERT INTO productos
(id_producto, id_categoria, codigo, nombre, descripcion, marca, modelo, imagen, precio_venta, stock_actual, stock_minimo, estado) VALUES
(1, 1, 'PC-001', 'Computadora de escritorio Core i5', 'Equipo de escritorio para oficina, 8 GB RAM, SSD 480 GB', 'HP', 'ProDesk 400 G7', 'imagen/productos/escritorio/desktop.jpg', 4200.00, 3, 1, 'Activo'),
(2, 1, 'PC-002', 'Computadora de escritorio Ryzen 5', 'Equipo armado con Ryzen 5 5600G, 16 GB RAM, SSD 480 GB', 'Armada', 'Ryzen 5 5600G', 'imagen/productos/escritorio/pc1.jfif', 3800.00, 2, 1, 'Activo'),
(3, 2, 'LAP-001', 'Laptop Lenovo IdeaPad 3', 'Laptop 15.6 pulgadas, Ryzen 5, 8 GB RAM, SSD 256 GB', 'Lenovo', '15ALC6', 'imagen/productos/portatiles/lenovoi3.jfif', 4600.00, 4, 2, 'Activo'),
(4, 2, 'LAP-002', 'Laptop HP Pavilion 15', 'Laptop 15.6 pulgadas, Core i5, 8 GB RAM, SSD 512 GB', 'HP', '15-eg0', 'imagen/productos/portatiles/hppavilion.jfif', 5200.00, 2, 1, 'Activo'),
(5, 3, 'ACC-001', 'Teclado USB', 'Teclado estándar USB español', 'Logitech', 'K120', 'imagen/productos/accesorios/teclado.jpg', 120.00, 15, 5, 'Activo'),
(6, 3, 'ACC-002', 'Mouse óptico USB', 'Mouse óptico USB 1000 dpi', 'Logitech', 'M90', 'imagen/productos/accesorios/mouse2.jfif', 65.00, 20, 5, 'Activo'),
(7, 3, 'ACC-003', 'Monitor LED 24 pulgadas', 'Monitor Full HD 24 pulgadas HDMI', 'Samsung', 'LS24R350', 'imagen/productos/accesorios/monitor.jpg', 950.00, 6, 2, 'Activo'),
(8, 4, 'REP-001', 'Memoria RAM DDR4 8GB', 'Memoria RAM DDR4 2666 MHz', 'Kingston', 'Fury', 'imagen/productos/accesorios/memoria-ram.jpg', 320.00, 10, 4, 'Activo'),
(9, 4, 'REP-002', 'SSD SATA 480GB', 'Unidad de estado sólido SATA III 480 GB', 'Kingston', 'A400', 'imagen/productos/accesorios/ssd-adata.jpg', 380.00, 8, 3, 'Activo'),
(10, 5, 'IMP-001', 'Impresora multifuncional', 'Impresora multifuncional WiFi sistema continuo', 'Epson', 'L3250', 'imagen/productos/impresora.jpg', 1450.00, 3, 1, 'Activo'),
(11, 4, 'REP-003', 'Fuente de poder 500W', 'Fuente de poder ATX 500W', 'Thermaltake', 'TR2-500', 'imagen/productos/componentes.jpg', 280.00, 5, 2, 'Activo'),
(12, 3, 'ACC-004', 'Adaptador HDMI a VGA', 'Adaptador HDMI a VGA con cable de 20 cm', 'Genérico', 'HDMI-VGA', 'imagen/productos/accesorios/VGA-HDMI.jfif', 150.00, 7, 3, 'Activo'),
(13, 1, 'PC-003', 'Computadora All in One 23.8 pulgadas', 'All in One con Core i5, 8 GB RAM y SSD 512 GB', 'Lenovo', 'AIO 3 24', 'imagen/productos/escritorio/allinone1.jfif', 5100.00, 2, 1, 'Activo'),
(14, 1, 'PC-004', 'Computadora gamer Ryzen 7', 'Equipo gamer con Ryzen 7, 16 GB RAM, RTX 3060 y SSD 1 TB', 'Armada', 'Ryzen 7 5700G', 'imagen/productos/escritorio/computadora-gammer.jpeg', 6900.00, 2, 1, 'Activo'),
(15, 1, 'PC-005', 'Computadora de escritorio Lenovo', 'Equipo de oficina Lenovo con Core i3 y 8 GB RAM', 'Lenovo', 'ThinkCentre M70q', 'imagen/productos/escritorio/lenovo.jpg', 4500.00, 2, 1, 'Activo'),
(16, 1, 'PC-006', 'Computadora de escritorio Magic', 'Equipo armado para hogar y oficina, 8 GB RAM, SSD 240 GB', 'Armada', 'Magic', 'imagen/productos/escritorio/pc-magic_2.jpg', 3300.00, 3, 1, 'Activo'),
(17, 1, 'PC-007', 'Computadora de escritorio ASRock', 'Kit completo con Core i5 de 12ª generación, 16 GB RAM y SSD 1 TB', 'ASRock', 'Core i5 12th Gen', 'imagen/productos/escritorio/asrock.jpg', 5600.00, 2, 1, 'Activo'),
(18, 1, 'PC-008', 'Computadora de escritorio Dell OptiPlex', 'Equipo compacto Dell con Core i5, 8 GB RAM y SSD 256 GB', 'Dell', 'OptiPlex 3070', 'imagen/productos/escritorio/computadora-de-escritorio.jpg', 4300.00, 2, 1, 'Activo'),
(19, 2, 'LAP-003', 'Laptop ASUS Vivobook Core i5', 'Laptop 15.6 pulgadas, Core i5, 8 GB RAM, SSD 512 GB', 'ASUS', 'Vivobook 15', 'imagen/productos/portatiles/asusi5.jfif', 4900.00, 3, 2, 'Activo'),
(20, 2, 'LAP-004', 'Laptop ASUS ROG Strix Ryzen 9', 'Laptop gamer 15.6 pulgadas, Ryzen 9, 16 GB RAM, RTX 3060', 'ASUS', 'ROG Strix G15', 'imagen/productos/portatiles/asusr9.jfif', 9800.00, 2, 1, 'Activo'),
(21, 2, 'LAP-005', 'Laptop HP Core i7', 'Laptop 14 pulgadas, Core i7, 16 GB RAM, SSD 512 GB', 'HP', 'Pavilion 14', 'imagen/productos/portatiles/hpi7.jfif', 7200.00, 2, 1, 'Activo'),
(22, 2, 'LAP-006', 'Laptop Lenovo Legion Core i7', 'Laptop gamer 15.6 pulgadas, Core i7, 16 GB RAM, RTX 3050', 'Lenovo', 'Legion 5', 'imagen/productos/portatiles/lenovolegioni7.jfif', 8600.00, 2, 1, 'Activo'),
(23, 2, 'LAP-007', 'Laptop MSI Core i7', 'Laptop gamer 15.6 pulgadas, Core i7, 16 GB RAM, RTX 3050 Ti', 'MSI', 'Katana GF66', 'imagen/productos/portatiles/msii7.jfif', 9100.00, 2, 1, 'Activo'),
(24, 2, 'LAP-008', 'Laptop HP Core i3', 'Laptop 15.6 pulgadas, Core i3, 8 GB RAM, SSD 256 GB', 'HP', '250 G8', 'imagen/productos/portatiles/hpi3.jfif', 3900.00, 3, 2, 'Activo'),
(25, 2, 'LAP-009', 'Laptop Lenovo V15', 'Laptop 15.6 pulgadas, Celeron, 8 GB RAM, SSD 256 GB', 'Lenovo', 'V15 G2', 'imagen/productos/portatiles/lenovo.jfif', 4100.00, 3, 2, 'Activo'),
(26, 3, 'ACC-005', 'Auriculares gamer RGB', 'Auriculares con micrófono y sonido envolvente', 'Genérico', 'G-2000', 'imagen/productos/accesorios/auricular1.jfif', 180.00, 8, 3, 'Activo'),
(27, 3, 'ACC-006', 'Mouse gamer RGB', 'Mouse gamer con 6 botones y 3200 DPI', 'Genérico', 'MG-3200', 'imagen/productos/accesorios/mausegamer.jfif', 150.00, 10, 4, 'Activo'),
(28, 3, 'ACC-007', 'Teclado mecánico RGB', 'Teclado mecánico retroiluminado con reposamuñecas', 'Kanup', 'K-500', 'imagen/productos/accesorios/tecladokanup.jfif', 260.00, 6, 2, 'Activo'),
(29, 3, 'ACC-008', 'Combo teclado y mouse inalámbrico', 'Kit inalámbrico de teclado y mouse con receptor USB', 'Genérico', 'KM-100', 'imagen/productos/accesorios/teclado-mouse.jfif', 220.00, 9, 3, 'Activo'),
(30, 3, 'ACC-009', 'Teclado gamer de una mano', 'Teclado de membrana para juegos con teclas programables', 'Redragon', 'Keypad', 'imagen/productos/accesorios/tecladohernomico.jfif', 300.00, 4, 2, 'Activo'),
(31, 3, 'ACC-010', 'Adaptador WiFi USB AC600', 'Adaptador inalámbrico USB dual band AC600', 'TP-Link', 'Archer T2U Nano', 'imagen/productos/accesorios/wifia600.jfif', 130.00, 7, 3, 'Activo'),
(32, 3, 'ACC-011', 'Antena WiFi USB de alta potencia', 'Antena inalámbrica USB con 2 antenas externas', 'TP-Link', 'TL-WN822N', 'imagen/productos/accesorios/wifitplink.jfif', 160.00, 5, 2, 'Activo'),
(33, 3, 'ACC-012', 'Lector de tarjetas externo', 'Lector USB multiformato para tarjetas de memoria', 'Genérico', 'CR-300', 'imagen/productos/accesorios/LECTOR-EXTERNO.jfif', 95.00, 5, 2, 'Activo'),
(34, 3, 'ACC-013', 'Disco duro externo 1TB', 'Disco duro externo USB 3.0 de 1 TB', 'Toshiba', 'Canvio Basics', 'imagen/productos/accesorios/dexterno1.jfif', 480.00, 4, 2, 'Activo'),
(35, 4, 'REP-004', 'SSD M.2 NVMe 1TB', 'Unidad de estado sólido M.2 NVMe de 1 TB', 'TeamGroup', 'MP33', 'imagen/productos/accesorios/SOLIDO-M.2.jfif', 780.00, 6, 2, 'Activo'),
(36, 4, 'REP-005', 'Disipador CPU RGB', 'Disipador con ventilador RGB para procesadores Intel y AMD', 'Redragon', 'DP-200', 'imagen/productos/accesorios/discipador.jfif', 190.00, 5, 2, 'Activo'),
(37, 4, 'REP-006', 'Tarjeta WiFi PCIe', 'Tarjeta de red inalámbrica PCI Express', 'TP-Link', 'Archer T4E', 'imagen/productos/accesorios/wifiinterno.jfif', 160.00, 4, 2, 'Activo');

-- Proveedores
INSERT INTO proveedores (id_proveedor, nombre, nit, telefono, direccion, correo, estado) VALUES
(1, 'Proveedor Demo Uno', NULL, NULL, 'Direccion de ejemplo', 'ventas@example.com', 'Activo'),
(2, 'Proveedor Demo Dos', NULL, NULL, 'Direccion de ejemplo', 'compras@example.com', 'Activo');

-- Clientes
INSERT INTO clientes (id_cliente, ci, nombres, apellidos, telefono, direccion, correo, fecha_registro, estado) VALUES
(1, 'DEMO-0001', 'Cliente', 'Demo Uno', NULL, 'Direccion de ejemplo', 'cliente1@example.com', '2026-10-20 09:15:00', 'Activo'),
(2, 'DEMO-0002', 'Cliente', 'Demo Dos', NULL, 'Direccion de ejemplo', 'cliente2@example.com', '2026-10-25 16:40:00', 'Activo');

-- Compra de apertura (genera el stock inicial y sus movimientos)
INSERT INTO compras (id_compra, id_proveedor, id_usuario, numero_comprobante, fecha_compra, total_compra, observacion, estado) VALUES
(1, 1, 1, 'C-000001', '2026-11-01 09:00:00', 50080.00, 'Compra de apertura para demostración', 'Registrada');

INSERT INTO detalle_compra (id_compra, id_producto, cantidad, costo_unitario, subtotal) VALUES
(1, 1, 3, 3000.00, 9000.00),
(1, 2, 2, 2700.00, 5400.00),
(1, 3, 4, 3400.00, 13600.00),
(1, 4, 2, 3900.00, 7800.00),
(1, 5, 15, 70.00, 1050.00),
(1, 6, 20, 35.00, 700.00),
(1, 7, 6, 650.00, 3900.00),
(1, 8, 10, 210.00, 2100.00),
(1, 9, 8, 250.00, 2000.00),
(1, 10, 3, 1000.00, 3000.00),
(1, 11, 5, 180.00, 900.00),
(1, 12, 7, 90.00, 630.00);

INSERT INTO movimientos_inventario
(id_producto, id_usuario, fecha_movimiento, tipo_movimiento, cantidad, referencia_tipo, referencia_id, observacion) VALUES
(1, 1, '2026-11-01 09:00:00', 'Entrada', 3, 'Compra', 1, 'Ingreso por compra C-000001'),
(2, 1, '2026-11-01 09:00:00', 'Entrada', 2, 'Compra', 1, 'Ingreso por compra C-000001'),
(3, 1, '2026-11-01 09:00:00', 'Entrada', 4, 'Compra', 1, 'Ingreso por compra C-000001'),
(4, 1, '2026-11-01 09:00:00', 'Entrada', 2, 'Compra', 1, 'Ingreso por compra C-000001'),
(5, 1, '2026-11-01 09:00:00', 'Entrada', 15, 'Compra', 1, 'Ingreso por compra C-000001'),
(6, 1, '2026-11-01 09:00:00', 'Entrada', 20, 'Compra', 1, 'Ingreso por compra C-000001'),
(7, 1, '2026-11-01 09:00:00', 'Entrada', 6, 'Compra', 1, 'Ingreso por compra C-000001'),
(8, 1, '2026-11-01 09:00:00', 'Entrada', 10, 'Compra', 1, 'Ingreso por compra C-000001'),
(9, 1, '2026-11-01 09:00:00', 'Entrada', 8, 'Compra', 1, 'Ingreso por compra C-000001'),
(10, 1, '2026-11-01 09:00:00', 'Entrada', 3, 'Compra', 1, 'Ingreso por compra C-000001'),
(11, 1, '2026-11-01 09:00:00', 'Entrada', 5, 'Compra', 1, 'Ingreso por compra C-000001'),
(12, 1, '2026-11-01 09:00:00', 'Entrada', 7, 'Compra', 1, 'Ingreso por compra C-000001');

-- Compra de ampliación del catálogo: incorpora los equipos y accesorios
-- cuyas fotografías se organizaron en escritorio, portatiles y accesorios.
INSERT INTO compras (id_compra, id_proveedor, id_usuario, numero_comprobante, fecha_compra, total_compra, observacion, estado) VALUES
(2, 2, 1, 'C-000002', '2026-11-02 09:30:00', 139030.00, 'Compra de ampliación del catálogo para demostración', 'Registrada');

INSERT INTO detalle_compra (id_compra, id_producto, cantidad, costo_unitario, subtotal) VALUES
(2, 13, 2, 3800.00, 7600.00),
(2, 14, 2, 5200.00, 10400.00),
(2, 15, 2, 3300.00, 6600.00),
(2, 16, 3, 2400.00, 7200.00),
(2, 17, 2, 4200.00, 8400.00),
(2, 18, 2, 3100.00, 6200.00),
(2, 19, 3, 3600.00, 10800.00),
(2, 20, 2, 7500.00, 15000.00),
(2, 21, 2, 5500.00, 11000.00),
(2, 22, 2, 6500.00, 13000.00),
(2, 23, 2, 6900.00, 13800.00),
(2, 24, 3, 2800.00, 8400.00),
(2, 25, 3, 3000.00, 9000.00),
(2, 26, 8, 110.00, 880.00),
(2, 27, 10, 90.00, 900.00),
(2, 28, 6, 160.00, 960.00),
(2, 29, 9, 130.00, 1170.00),
(2, 30, 4, 180.00, 720.00),
(2, 31, 7, 80.00, 560.00),
(2, 32, 5, 95.00, 475.00),
(2, 33, 5, 55.00, 275.00),
(2, 34, 4, 350.00, 1400.00),
(2, 35, 6, 560.00, 3360.00),
(2, 36, 5, 110.00, 550.00),
(2, 37, 4, 95.00, 380.00);

INSERT INTO movimientos_inventario
(id_producto, id_usuario, fecha_movimiento, tipo_movimiento, cantidad, referencia_tipo, referencia_id, observacion) VALUES
(13, 1, '2026-11-02 09:30:00', 'Entrada', 2, 'Compra', 2, 'Ingreso por compra C-000002'),
(14, 1, '2026-11-02 09:30:00', 'Entrada', 2, 'Compra', 2, 'Ingreso por compra C-000002'),
(15, 1, '2026-11-02 09:30:00', 'Entrada', 2, 'Compra', 2, 'Ingreso por compra C-000002'),
(16, 1, '2026-11-02 09:30:00', 'Entrada', 3, 'Compra', 2, 'Ingreso por compra C-000002'),
(17, 1, '2026-11-02 09:30:00', 'Entrada', 2, 'Compra', 2, 'Ingreso por compra C-000002'),
(18, 1, '2026-11-02 09:30:00', 'Entrada', 2, 'Compra', 2, 'Ingreso por compra C-000002'),
(19, 1, '2026-11-02 09:30:00', 'Entrada', 3, 'Compra', 2, 'Ingreso por compra C-000002'),
(20, 1, '2026-11-02 09:30:00', 'Entrada', 2, 'Compra', 2, 'Ingreso por compra C-000002'),
(21, 1, '2026-11-02 09:30:00', 'Entrada', 2, 'Compra', 2, 'Ingreso por compra C-000002'),
(22, 1, '2026-11-02 09:30:00', 'Entrada', 2, 'Compra', 2, 'Ingreso por compra C-000002'),
(23, 1, '2026-11-02 09:30:00', 'Entrada', 2, 'Compra', 2, 'Ingreso por compra C-000002'),
(24, 1, '2026-11-02 09:30:00', 'Entrada', 3, 'Compra', 2, 'Ingreso por compra C-000002'),
(25, 1, '2026-11-02 09:30:00', 'Entrada', 3, 'Compra', 2, 'Ingreso por compra C-000002'),
(26, 1, '2026-11-02 09:30:00', 'Entrada', 8, 'Compra', 2, 'Ingreso por compra C-000002'),
(27, 1, '2026-11-02 09:30:00', 'Entrada', 10, 'Compra', 2, 'Ingreso por compra C-000002'),
(28, 1, '2026-11-02 09:30:00', 'Entrada', 6, 'Compra', 2, 'Ingreso por compra C-000002'),
(29, 1, '2026-11-02 09:30:00', 'Entrada', 9, 'Compra', 2, 'Ingreso por compra C-000002'),
(30, 1, '2026-11-02 09:30:00', 'Entrada', 4, 'Compra', 2, 'Ingreso por compra C-000002'),
(31, 1, '2026-11-02 09:30:00', 'Entrada', 7, 'Compra', 2, 'Ingreso por compra C-000002'),
(32, 1, '2026-11-02 09:30:00', 'Entrada', 5, 'Compra', 2, 'Ingreso por compra C-000002'),
(33, 1, '2026-11-02 09:30:00', 'Entrada', 5, 'Compra', 2, 'Ingreso por compra C-000002'),
(34, 1, '2026-11-02 09:30:00', 'Entrada', 4, 'Compra', 2, 'Ingreso por compra C-000002'),
(35, 1, '2026-11-02 09:30:00', 'Entrada', 6, 'Compra', 2, 'Ingreso por compra C-000002'),
(36, 1, '2026-11-02 09:30:00', 'Entrada', 5, 'Compra', 2, 'Ingreso por compra C-000002'),
(37, 1, '2026-11-02 09:30:00', 'Entrada', 4, 'Compra', 2, 'Ingreso por compra C-000002');

-- Venta de demostración
INSERT INTO ventas (id_venta, id_cliente, id_usuario, fecha_venta, numero_comprobante, subtotal, descuento, total, observacion, estado) VALUES
(1, 1, 2, '2026-11-05 11:30:00', 'V-000001', 4730.00, 130.00, 4600.00, 'Venta de demostración con garantía de un año', 'Confirmada');

INSERT INTO detalle_venta (id_detalle_venta, id_venta, id_producto, cantidad, precio_unitario, descuento, subtotal) VALUES
(1, 1, 3, 1, 4600.00, 0.00, 4600.00),
(2, 1, 6, 2, 65.00, 0.00, 130.00);

INSERT INTO pagos_venta (id_venta, id_usuario, fecha_pago, monto, metodo_pago, referencia, observacion, estado) VALUES
(1, 2, '2026-11-05 11:35:00', 2000.00, 'Efectivo', NULL, 'Adelanto', 'Confirmado'),
(1, 2, '2026-11-06 10:00:00', 2600.00, 'Transferencia', 'TRF-0001', 'Cancelación del saldo', 'Confirmado');

INSERT INTO movimientos_inventario
(id_producto, id_usuario, fecha_movimiento, tipo_movimiento, cantidad, referencia_tipo, referencia_id, observacion) VALUES
(3, 2, '2026-11-05 11:30:00', 'Salida', 1, 'Venta', 1, 'Salida por venta V-000001'),
(6, 2, '2026-11-05 11:30:00', 'Salida', 2, 'Venta', 1, 'Salida por venta V-000001');

-- Garantía de producto
INSERT INTO garantias (id_detalle_venta, id_orden, fecha_inicio, fecha_fin, tipo_garantia, descripcion, condiciones, estado) VALUES
(1, NULL, '2026-11-05', '2027-11-05', 'Producto',
 'Garantía de un año por la laptop Lenovo IdeaPad 3',
 'Cubre fallas de fábrica. No cubre daños por golpes, líquidos ni manipulación no autorizada.', 'Vigente');

-- Equipo y orden de servicio de demostración
INSERT INTO equipos_cliente (id_equipo, id_cliente, id_producto, tipo_equipo, marca, modelo, numero_serie, descripcion, fecha_registro) VALUES
(1, 1, 3, 'Laptop', 'Lenovo', 'IdeaPad 3 15ALC6', 'LNV-2026-001', 'Laptop adquirida en Alex@sys, presenta falla en la salida de video', '2026-11-08 10:00:00');

INSERT INTO ordenes_servicio
(id_orden, id_equipo, id_usuario_recepcion, id_usuario_tecnico, numero_orden, fecha_recepcion, fecha_entrega,
 falla_reportada, accesorios_recibidos, estado, costo_servicio, observaciones) VALUES
(1, 1, 3, 3, 'OS-000001', '2026-11-08 10:30:00', NULL,
 'El equipo no da imagen por el puerto HDMI', 'Cargador original y funda',
 'En reparacion', 120.00, 'Cliente autorizó la revisión de la salida de video');

INSERT INTO detalle_servicio
(id_orden, id_usuario_tecnico, fecha_intervencion, diagnostico, trabajo_realizado, resultado_prueba, costo) VALUES
(1, 3, '2026-11-09 15:00:00',
 'Puerto de video sin salida y adaptador dañado',
 'Reemplazo del adaptador de video y limpieza interna',
 'Equipo muestra imagen correctamente por HDMI', 120.00);

INSERT INTO repuestos_servicio (id_orden, id_producto, cantidad, costo_unitario, subtotal) VALUES
(1, 12, 1, 90.00, 90.00);

INSERT INTO movimientos_inventario
(id_producto, id_usuario, fecha_movimiento, tipo_movimiento, cantidad, referencia_tipo, referencia_id, observacion) VALUES
(12, 3, '2026-11-09 15:00:00', 'Salida', 1, 'Repuesto', 1, 'Repuesto utilizado en la orden OS-000001');

INSERT INTO pagos_servicio (id_orden, id_usuario, fecha_pago, monto, metodo_pago, referencia, observacion, estado) VALUES
(1, 3, '2026-11-09 15:10:00', 100.00, 'Efectivo', NULL, 'Adelanto del servicio técnico', 'Confirmado');

-- Garantía de servicio técnico (2 meses)
INSERT INTO garantias (id_detalle_venta, id_orden, fecha_inicio, fecha_fin, tipo_garantia, descripcion, condiciones, estado) VALUES
(NULL, 1, '2026-11-10', '2027-01-09', 'Servicio',
 'Garantía de dos meses por la reparación de la salida de video',
 'Cubre el trabajo realizado y el repuesto colocado. No cubre daños por mal uso.', 'Vigente');

-- Ajuste final de stock coherente con los movimientos registrados
-- Regla: el stock siempre equivale a SUM(Entradas) - SUM(Salidas).
-- Los ajustes manuales se registran como Entrada o Salida con
-- referencia_tipo = 'Ajuste' para conservar esa equivalencia.
UPDATE productos p SET p.stock_actual = (
    SELECT COALESCE(SUM(CASE
        WHEN m.tipo_movimiento = 'Entrada' THEN CAST(m.cantidad AS SIGNED)
        WHEN m.tipo_movimiento = 'Salida' THEN -CAST(m.cantidad AS SIGNED)
        ELSE 0 END), 0)
    FROM movimientos_inventario m WHERE m.id_producto = p.id_producto
);
