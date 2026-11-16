-- ============================================================================
-- Alex@sys · Actualización del catálogo de productos
-- ----------------------------------------------------------------------------
-- Reorganiza las fotografías en imagen/productos/{escritorio,portatiles,accesorios}
-- y amplía el catálogo con los equipos y accesorios de la tienda.
--
-- Uso:
--   mysql -u root dbalexasys < database/actualizacion-catalogo.sql
--
-- Es idempotente: puede ejecutarse varias veces sin duplicar información.
-- ============================================================================

START TRANSACTION;

-- ----------------------------------------------------------------------------
-- 1. Productos existentes (1-12): nuevas rutas de imagen
-- ----------------------------------------------------------------------------
UPDATE productos SET imagen = 'imagen/productos/escritorio/desktop.jpg' WHERE id_producto = 1;
UPDATE productos SET imagen = 'imagen/productos/escritorio/pc1.jfif' WHERE id_producto = 2;
UPDATE productos SET imagen = 'imagen/productos/portatiles/lenovoi3.jfif' WHERE id_producto = 3;
UPDATE productos SET imagen = 'imagen/productos/portatiles/hppavilion.jfif' WHERE id_producto = 4;
UPDATE productos SET imagen = 'imagen/productos/accesorios/teclado.jpg' WHERE id_producto = 5;
UPDATE productos SET imagen = 'imagen/productos/accesorios/mouse2.jfif' WHERE id_producto = 6;
UPDATE productos SET imagen = 'imagen/productos/accesorios/monitor.jpg' WHERE id_producto = 7;
UPDATE productos SET imagen = 'imagen/productos/accesorios/memoria-ram.jpg' WHERE id_producto = 8;
UPDATE productos SET imagen = 'imagen/productos/accesorios/ssd-adata.jpg' WHERE id_producto = 9;
UPDATE productos SET imagen = 'imagen/productos/impresora.jpg' WHERE id_producto = 10;
UPDATE productos SET imagen = 'imagen/productos/componentes.jpg' WHERE id_producto = 11;
UPDATE productos
   SET nombre = 'Adaptador HDMI a VGA',
       descripcion = 'Adaptador HDMI a VGA con cable de 20 cm',
       marca = 'Genérico',
       modelo = 'HDMI-VGA',
       imagen = 'imagen/productos/accesorios/VGA-HDMI.jfif'
 WHERE id_producto = 12;

-- ----------------------------------------------------------------------------
-- 2. Nuevos productos (13-37)
-- ----------------------------------------------------------------------------
INSERT INTO productos
(id_producto, id_categoria, codigo, nombre, descripcion, marca, modelo, imagen, precio_venta, stock_actual, stock_minimo, estado) VALUES
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
(37, 4, 'REP-006', 'Tarjeta WiFi PCIe', 'Tarjeta de red inalámbrica PCI Express', 'TP-Link', 'Archer T4E', 'imagen/productos/accesorios/wifiinterno.jfif', 160.00, 4, 2, 'Activo')
ON DUPLICATE KEY UPDATE
    id_categoria = VALUES(id_categoria),
    codigo = VALUES(codigo),
    nombre = VALUES(nombre),
    descripcion = VALUES(descripcion),
    marca = VALUES(marca),
    modelo = VALUES(modelo),
    imagen = VALUES(imagen),
    precio_venta = VALUES(precio_venta),
    stock_minimo = VALUES(stock_minimo),
    estado = VALUES(estado);

-- ----------------------------------------------------------------------------
-- 3. Compra de ampliación del catálogo (stock y costo de los productos 13-37)
-- ----------------------------------------------------------------------------
DELETE FROM movimientos_inventario WHERE referencia_tipo = 'Compra' AND referencia_id = 2;
DELETE FROM detalle_compra WHERE id_compra = 2;
DELETE FROM compras WHERE id_compra = 2;

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

-- ----------------------------------------------------------------------------
-- 4. Coherencia del stock con los movimientos de inventario
-- ----------------------------------------------------------------------------
UPDATE productos p SET p.stock_actual = (
    SELECT COALESCE(SUM(CASE
        WHEN m.tipo_movimiento = 'Entrada' THEN CAST(m.cantidad AS SIGNED)
        WHEN m.tipo_movimiento = 'Salida' THEN -CAST(m.cantidad AS SIGNED)
        ELSE 0 END), 0)
    FROM movimientos_inventario m WHERE m.id_producto = p.id_producto
);

-- ----------------------------------------------------------------------------
-- 5. Datos de demostración del servicio técnico coherentes con el producto 12
--    (adaptador de video)
-- ----------------------------------------------------------------------------
UPDATE equipos_cliente
   SET descripcion = 'Laptop adquirida en Alex@sys, presenta falla en la salida de video'
 WHERE id_equipo = 1;

UPDATE ordenes_servicio
   SET falla_reportada = 'El equipo no da imagen por el puerto HDMI',
       observaciones = 'Cliente autorizó la revisión de la salida de video'
 WHERE id_orden = 1;

UPDATE detalle_servicio
   SET diagnostico = 'Puerto de video sin salida y adaptador dañado',
       trabajo_realizado = 'Reemplazo del adaptador de video y limpieza interna',
       resultado_prueba = 'Equipo muestra imagen correctamente por HDMI'
 WHERE id_orden = 1;

UPDATE garantias
   SET descripcion = 'Garantía de dos meses por la reparación de la salida de video'
 WHERE id_orden = 1 AND tipo_garantia = 'Servicio';

COMMIT;
