/* ==========================================================================
   Alex@sys - JavaScript de interfaz
   - Menú lateral móvil
   - Confirmación de acciones sensibles
   - Cálculo de totales en compras y ventas
   - Búsqueda con envío por Enter
   ========================================================================== */

(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        initSidebar();
        initConfirmaciones();
        initTotalesVenta();
        initTotalesCompra();
        initAutoCierre();
        initVistaPreviaImagen();
    });

    /* Menú lateral en móviles */
    function initSidebar() {
        const boton = document.getElementById('btnMenu');
        const sidebar = document.getElementById('sidebar');
        const telon = document.getElementById('telonSidebar');
        if (!sidebar) return;

        function alternar() {
            sidebar.classList.toggle('abierto');
            if (telon) telon.classList.toggle('visible', sidebar.classList.contains('abierto'));
        }
        if (boton) boton.addEventListener('click', alternar);
        if (telon) telon.addEventListener('click', alternar);
    }

    /* Confirmación para formularios con data-confirmar */
    function initConfirmaciones() {
        document.querySelectorAll('form[data-confirmar]').forEach(function (form) {
            form.addEventListener('submit', function (evento) {
                if (!window.confirm(form.getAttribute('data-confirmar'))) {
                    evento.preventDefault();
                }
            });
        });
    }

    /* Ventas: recalcula subtotales, descuento global y total */
    function initTotalesVenta() {
        const contenedor = document.getElementById('lineasVenta');
        if (!contenedor) return;

        const descuentoGlobal = document.getElementById('descuentoGlobal');
        const moneda = contenedor.dataset.moneda || 'Bs';

        function recalcular() {
            let subtotal = 0;
            contenedor.querySelectorAll('.linea-venta').forEach(function (fila) {
                const select = fila.querySelector('.producto-select');
                const cantidad = fila.querySelector('.cantidad-input');
                const precio = fila.querySelector('.precio-input');
                const descLinea = fila.querySelector('.descuento-linea');
                const celda = fila.querySelector('.subtotal-linea');

                const c = Math.max(0, parseInt(cantidad.value || '0', 10));
                const p = Math.max(0, parseFloat(precio.value || '0'));
                const d = Math.max(0, parseFloat(descLinea ? descLinea.value || '0' : '0'));
                const linea = Math.max(0, c * p - d);

                if (celda) celda.textContent = moneda + ' ' + linea.toFixed(2);
                subtotal += linea;

                // El precio se propone desde el catálogo al elegir producto.
                if (select && select.selectedOptions.length && !precio.dataset.manual) {
                    const sugerido = select.selectedOptions[0].dataset.precio;
                    if (sugerido !== undefined && precio.value === '') {
                        precio.value = parseFloat(sugerido).toFixed(2);
                    }
                }
            });

            const desc = Math.max(0, parseFloat(descuentoGlobal ? descuentoGlobal.value || '0' : '0'));
            const total = Math.max(0, subtotal - desc);
            const elSub = document.getElementById('subtotalVenta');
            const elTot = document.getElementById('totalVenta');
            if (elSub) elSub.textContent = moneda + ' ' + subtotal.toFixed(2);
            if (elTot) elTot.textContent = moneda + ' ' + total.toFixed(2);
        }

        contenedor.addEventListener('input', recalcular);
        contenedor.addEventListener('change', recalcular);
        if (descuentoGlobal) descuentoGlobal.addEventListener('input', recalcular);

        // Al cambiar de producto se propone su precio de venta.
        contenedor.addEventListener('change', function (evento) {
            if (!evento.target.classList.contains('producto-select')) return;
            const fila = evento.target.closest('.linea-venta');
            const precio = fila.querySelector('.precio-input');
            if (!precio) return;
            const sugerido = evento.target.selectedOptions[0]?.dataset.precio;
            if (sugerido !== undefined) {
                precio.value = parseFloat(sugerido).toFixed(2);
                delete precio.dataset.manual;
            }
            recalcular();
        });

        document.querySelectorAll('.precio-input').forEach(function (input) {
            input.addEventListener('input', function () { input.dataset.manual = '1'; });
        });

        const btnAgregar = document.getElementById('btnAgregarLinea');
        if (btnAgregar) {
            btnAgregar.addEventListener('click', function () {
                const plantilla = document.getElementById('plantillaLineaVenta');
                if (!plantilla) return;
                const numero = contenedor.querySelectorAll('.linea-venta').length;
                const html = plantilla.innerHTML.replace(/__indice__/g, String(numero));
                const temp = document.createElement('tbody');
                temp.innerHTML = html.trim();
                contenedor.appendChild(temp.firstElementChild);
                recalcular();
            });
        }

        contenedor.addEventListener('click', function (evento) {
            const boton = evento.target.closest('.btn-quitar-linea');
            if (!boton) return;
            const filas = contenedor.querySelectorAll('.linea-venta');
            if (filas.length <= 1) {
                window.alert('La venta debe conservar al menos una línea.');
                return;
            }
            boton.closest('.linea-venta').remove();
            recalcular();
        });

        recalcular();
    }

    /* Compras: recalcula subtotales y total */
    function initTotalesCompra() {
        const contenedor = document.getElementById('lineasCompra');
        if (!contenedor) return;

        const moneda = contenedor.dataset.moneda || 'Bs';

        function recalcular() {
            let total = 0;
            contenedor.querySelectorAll('.linea-compra').forEach(function (fila) {
                const cantidad = fila.querySelector('.cantidad-input');
                const costo = fila.querySelector('.costo-input');
                const celda = fila.querySelector('.subtotal-linea');
                const c = Math.max(0, parseInt(cantidad.value || '0', 10));
                const p = Math.max(0, parseFloat(costo.value || '0'));
                const linea = c * p;
                if (celda) celda.textContent = moneda + ' ' + linea.toFixed(2);
                total += linea;
            });
            const elTot = document.getElementById('totalCompra');
            if (elTot) elTot.textContent = moneda + ' ' + total.toFixed(2);
        }

        contenedor.addEventListener('input', recalcular);

        const btnAgregar = document.getElementById('btnAgregarLineaCompra');
        if (btnAgregar) {
            btnAgregar.addEventListener('click', function () {
                const plantilla = document.getElementById('plantillaLineaCompra');
                if (!plantilla) return;
                const numero = contenedor.querySelectorAll('.linea-compra').length;
                const html = plantilla.innerHTML.replace(/__indice__/g, String(numero));
                const temp = document.createElement('tbody');
                temp.innerHTML = html.trim();
                contenedor.appendChild(temp.firstElementChild);
                recalcular();
            });
        }

        contenedor.addEventListener('click', function (evento) {
            const boton = evento.target.closest('.btn-quitar-linea');
            if (!boton) return;
            const filas = contenedor.querySelectorAll('.linea-compra');
            if (filas.length <= 1) {
                window.alert('La compra debe conservar al menos una línea.');
                return;
            }
            boton.closest('.linea-compra').remove();
            recalcular();
        });

        recalcular();
    }

    /* Cierre automático de alertas informativas */
    function initAutoCierre() {
        document.querySelectorAll('.alerta-auto').forEach(function (alerta) {
            window.setTimeout(function () {
                alerta.classList.add('fade');
                window.setTimeout(function () { alerta.remove(); }, 400);
            }, 6000);
        });
    }

    /* Vista previa de imagen en formularios de producto */
    function initVistaPreviaImagen() {
        const input = document.getElementById('imagenProducto');
        const vista = document.getElementById('vistaImagenProducto');
        if (!input || !vista) return;
        input.addEventListener('change', function () {
            const archivo = input.files && input.files[0];
            if (!archivo) return;
            vista.src = URL.createObjectURL(archivo);
            vista.classList.remove('d-none');
        });
    }
})();
