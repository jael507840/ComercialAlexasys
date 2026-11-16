# Manual de usuario

Alex@sys organiza el trabajo en tres perfiles. El menú lateral solo muestra los
módulos permitidos y el dashboard cambia según el rol.

## 1. Ingreso al sistema

1. Abra `index.php?ruta=login`.
2. Ingrese usuario y contraseña.
3. Tras cinco intentos fallidos, la cuenta se bloquea temporalmente durante
   15 minutos.
4. Use **Salir** para cerrar la sesión.

## 2. Dashboard

| Rol | Información principal |
|---|---|
| Gerente | Ventas y cobros del día/mes, órdenes activas, clientes, productos, stock bajo, garantías, gráfico de ventas, ventas y órdenes con saldo, garantías por vencer |
| Vendedor | Ventas y cobros, clientes activos y nuevos, stock bajo, ventas pendientes de cobro, últimas ventas y órdenes |
| Técnico | Órdenes activas, órdenes asignadas, recibidas hoy, cobros de servicios, estado del taller, sus órdenes con saldo, repuestos con stock bajo |

Los **accesos rápidos** llevan directamente a los módulos del perfil.

## 3. Flujo comercial (Vendedor y Gerente)

### 3.1 Registrar un cliente

1. **Clientes → Nuevo cliente**.
2. Complete CI, nombres, apellidos y teléfono. La CI es única.
3. Guarde. El cliente queda disponible para ventas y equipos.

### 3.2 Registrar una venta

1. **Ventas → Nueva venta**.
2. Seleccione cliente y agregue productos con cantidad.
3. El sistema valida stock y calcula subtotal, descuento y total.
4. Al confirmar, se descuenta el stock y se registra el movimiento de
   inventario.
5. Si la venta se anula, el stock se reintegra y se registra el movimiento.

### 3.3 Cobrar una venta

1. **Pagos de ventas** o el botón de cobro en el detalle de la venta.
2. Seleccione la venta con saldo.
3. Ingrese monto, método, fecha y referencia.
4. El sistema rechaza montos cero, negativos o mayores al saldo.
5. El saldo se actualiza como `TOTAL − PAGOS CONFIRMADOS`.

### 3.4 Garantías

1. **Garantías → Nueva garantía**.
2. Seleccione una venta o una orden de servicio.
3. Indique tipo, fechas y descripción.
4. El listado permite filtrar y consultar la vigencia.

## 4. Flujo de servicio técnico (Técnico y Gerente)

### 4.1 Recepción de equipo

1. **Equipos de clientes → Nuevo equipo**.
2. Registre cliente, tipo, marca, modelo y número de serie.
3. Guarde el equipo.

### 4.2 Orden de servicio

1. **Órdenes de servicio → Nueva orden**.
2. Seleccione el equipo y el técnico asignado.
3. Registre el problema reportado y el costo de mano de obra.
4. Guarde la orden; queda en estado **Recibido**.

### 4.3 Intervenciones y repuestos

1. Abra la orden y entre a **Detalle / Intervenciones**.
2. Registre diagnóstico, trabajo realizado y cambio de estado
   (En diagnóstico, En reparación, Terminado).
3. En **Repuestos usados**, agregue productos; el sistema descuenta stock y
   registra el movimiento.
4. No se puede usar más cantidad que el stock disponible.

### 4.4 Cobro del servicio

1. En la orden o en **Pagos de servicios**, elija **Registrar pago**.
2. El máximo permitido es el saldo (mano de obra + repuestos − pagos).
3. Al pagar el total, la orden queda sin saldo.
4. No se aceptan pagos de órdenes anuladas.

### 4.5 Entrega y garantía

1. Con la orden **Terminada** y sin saldo pendiente, marque **Entregado**.
2. Genere la garantía del servicio desde **Garantías → Nueva garantía**.

## 5. Catálogo e inventario (Gerente)

### 5.1 Categorías y productos

1. **Categorías**: cree y edite la clasificación del catálogo.
2. **Productos**: registre código, nombre, precio, stock mínimo e imagen
   (JPG/PNG/WEBP de hasta 2 MB).

### 5.2 Compras

1. **Compras → Nueva compra**.
2. Seleccione proveedor y agregue productos con cantidad y costo.
3. Al registrar la compra se incrementa el stock y se crea el movimiento de
   entrada.

### 5.3 Inventario

1. **Inventario** muestra stock actual, mínimos y movimientos.
2. Use **Ajustar** para corregir el stock; es obligatorio indicar el motivo.
3. Todo ajuste genera un movimiento de entrada o salida.
4. El sistema impide dejar stock negativo.

## 6. Reportes

**Reportes** permite consultar ventas, compras, servicios, pagos e inventario,
con filtros por fecha. Los resultados se basan en datos reales de MySQL.

## 7. Usuarios y roles (Gerente)

1. **Usuarios y roles → Nuevo usuario**.
2. Complete datos, usuario, contraseña y rol.
3. Para cambiar contraseña o estado, abra **Actualizar usuario**.
4. Cada rol conserva los permisos descritos al inicio de este manual.

## 8. Reglas que el sistema siempre aplica

- No hay stock negativo.
- Toda variación de stock tiene un movimiento asociado.
- No se cobra más que el saldo pendiente.
- No se registran pagos en documentos anulados.
- Las acciones sensibles requieren sesión y token CSRF.
- Las contraseñas se almacenan cifradas.
