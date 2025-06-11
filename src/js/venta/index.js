import DataTable from "datatables.net-bs5";
import Swal from "sweetalert2";
import { lenguaje } from "../lenguaje";

// Función de validación simplificada
const validarFormulario = (form, excluir = []) => {
    const elementos = form.querySelectorAll('input[required], select[required], textarea[required]');
    let valido = true;
    
    elementos.forEach(elemento => {
        if (!excluir.includes(elemento.name) && !elemento.value.trim()) {
            elemento.classList.add('is-invalid');
            elemento.classList.remove('is-valid');
            valido = false;
        } else if (!excluir.includes(elemento.name)) {
            elemento.classList.add('is-valid');
            elemento.classList.remove('is-invalid');
        }
    });
    
    return valido;
};

// Elementos del DOM
const FormularioVenta = document.getElementById('FormularioVenta');
const BtnGuardar = document.getElementById('BtnGuardar');
const BtnLimpiar = document.getElementById('BtnLimpiar');
const TablaDetalle = document.getElementById('TablaDetalle');

// Variables globales
let detalleVenta = [];
let ventaEditando = null;
let usuarioActual = 1; // Aquí deberías obtener el ID del usuario logueado

// Cargar clientes para el select
const cargarClientes = async () => {
    try {
        const respuesta = await fetch('/empresa_celulares/cliente/buscarAPI');
        const data = await respuesta.json();
        
        const selectCliente = document.getElementById('cliente_id');
        selectCliente.innerHTML = '<option value="">Seleccione un cliente</option>';
        
        if (data.codigo === 1 && data.data) {
            data.data.forEach(cliente => {
                const nombreCompleto = `${cliente.cliente_nom1} ${cliente.cliente_nom2 || ''} ${cliente.cliente_ape1} ${cliente.cliente_ape2 || ''}`.trim();
                const option = document.createElement('option');
                option.value = cliente.cliente_id;
                option.textContent = `${nombreCompleto} - ${cliente.cliente_tel}`;
                selectCliente.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Error cargando clientes:', error);
    }
};

// Cargar productos del inventario
const cargarProductos = async () => {
    try {
        const respuesta = await fetch('/empresa_celulares/venta/obtenerProductosAPI');
        const data = await respuesta.json();
        
        const selectProducto = document.getElementById('producto_id');
        selectProducto.innerHTML = '<option value="">Seleccione un producto</option>';
        
        if (data.codigo === 1 && data.data) {
            data.data.forEach(producto => {
                const option = document.createElement('option');
                option.value = producto.inventario_id;
                option.textContent = `${producto.inventario_descripcion} - Q.${parseFloat(producto.inventario_precio_venta).toFixed(2)} (Stock: ${producto.inventario_stock})`;
                option.dataset.precio = producto.inventario_precio_venta;
                option.dataset.stock = producto.inventario_stock;
                option.dataset.descripcion = producto.inventario_descripcion;
                selectProducto.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Error cargando productos:', error);
    }
};

// Cargar servicios disponibles
const cargarServicios = async () => {
    try {
        const respuesta = await fetch('/empresa_celulares/venta/obtenerServiciosAPI');
        const data = await respuesta.json();
        
        console.log('Respuesta servicios:', data); // Debug
        
        const selectServicio = document.getElementById('servicio_id');
        selectServicio.innerHTML = '<option value="">Seleccione un servicio</option>';
        
        if (data.codigo === 1 && data.data) {
            console.log('Servicios encontrados:', data.data.length); // Debug
            
            data.data.forEach(servicio => {
                const dispositivo = `${servicio.recepcion_marca || ''} ${servicio.recepcion_modelo || ''}`.trim();
                const option = document.createElement('option');
                option.value = servicio.orden_id;
                option.textContent = `${servicio.tipo_servicio_nombre} - ${dispositivo} - Q.${parseFloat(servicio.orden_costo_total).toFixed(2)}`;
                option.dataset.precio = servicio.orden_costo_total;
                option.dataset.descripcion = `${servicio.tipo_servicio_nombre} - ${dispositivo}`;
                selectServicio.appendChild(option);
            });
        } else {
            console.log('No se encontraron servicios o error en la respuesta');
        }
    } catch (error) {
        console.error('Error cargando servicios:', error);
    }
};

// Agregar producto al detalle
const agregarProducto = () => {
    const selectProducto = document.getElementById('producto_id');
    const cantidadInput = document.getElementById('cantidad_producto');
    
    const selectedOption = selectProducto.selectedOptions[0];
    const cantidad = parseInt(cantidadInput.value) || 1;
    
    if (!selectedOption || selectedOption.value === '') {
        Swal.fire('Error', 'Debe seleccionar un producto', 'error');
        return;
    }
    
    const stock = parseInt(selectedOption.dataset.stock);
    if (cantidad > stock) {
        Swal.fire('Error', `Stock insuficiente. Disponible: ${stock}`, 'error');
        return;
    }
    
    const precio = parseFloat(selectedOption.dataset.precio);
    const descripcion = selectedOption.dataset.descripcion;
    const subtotal = cantidad * precio;
    
    // Verificar si el producto ya está en el detalle
    const existente = detalleVenta.findIndex(item => 
        item.tipo_item === 'PRODUCTO' && item.inventario_id == selectedOption.value
    );
    
    if (existente !== -1) {
        // Actualizar cantidad existente
        const nuevaCantidad = detalleVenta[existente].cantidad + cantidad;
        if (nuevaCantidad > stock) {
            Swal.fire('Error', `Stock insuficiente. Disponible: ${stock}`, 'error');
            return;
        }
        detalleVenta[existente].cantidad = nuevaCantidad;
        detalleVenta[existente].subtotal = nuevaCantidad * precio;
    } else {
        // Agregar nuevo producto
        detalleVenta.push({
            tipo_item: 'PRODUCTO',
            inventario_id: selectedOption.value,
            orden_id: null,
            descripcion: descripcion,
            cantidad: cantidad,
            precio_unitario: precio,
            subtotal: subtotal
        });
    }
    
    // Limpiar selección
    selectProducto.value = '';
    cantidadInput.value = '1';
    
    actualizarTablaDetalle();
    calcularTotales();
};

// Agregar servicio al detalle
const agregarServicio = () => {
    const selectServicio = document.getElementById('servicio_id');
    
    const selectedOption = selectServicio.selectedOptions[0];
    
    if (!selectedOption || selectedOption.value === '') {
        Swal.fire('Error', 'Debe seleccionar un servicio', 'error');
        return;
    }
    
    // Verificar si el servicio ya está en el detalle
    const existente = detalleVenta.findIndex(item => 
        item.tipo_item === 'SERVICIO' && item.orden_id == selectedOption.value
    );
    
    if (existente !== -1) {
        Swal.fire('Error', 'Este servicio ya está agregado', 'error');
        return;
    }
    
    const precio = parseFloat(selectedOption.dataset.precio);
    const descripcion = selectedOption.dataset.descripcion;
    
    detalleVenta.push({
        tipo_item: 'SERVICIO',
        inventario_id: null,
        orden_id: selectedOption.value,
        descripcion: descripcion,
        cantidad: 1,
        precio_unitario: precio,
        subtotal: precio
    });
    
    // Limpiar selección
    selectServicio.value = '';
    
    actualizarTablaDetalle();
    calcularTotales();
};

// Actualizar tabla de detalle
const actualizarTablaDetalle = () => {
    const tbody = TablaDetalle.querySelector('tbody');
    tbody.innerHTML = '';
    
    if (detalleVenta.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="text-center text-muted py-4">
                    <i class="bi bi-basket me-2"></i>No hay productos agregados
                </td>
            </tr>
        `;
        return;
    }
    
    detalleVenta.forEach((item, index) => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${item.descripcion}</td>
            <td class="text-center">
                <input type="number" class="form-control form-control-sm cantidad-input" 
                       value="${item.cantidad}" min="1" max="${item.tipo_item === 'PRODUCTO' ? getStockProducto(item.inventario_id) : 1}"
                       data-index="${index}" style="width: 80px;">
            </td>
            <td class="text-end">Q. ${item.precio_unitario.toFixed(2)}</td>
            <td class="text-end">Q. ${item.subtotal.toFixed(2)}</td>
            <td class="text-center">
                <button type="button" class="btn btn-danger btn-sm eliminar-item" data-index="${index}">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(row);
    });
    
    // Event listeners para los inputs de cantidad
    TablaDetalle.querySelectorAll('.cantidad-input').forEach(input => {
        input.addEventListener('change', (e) => {
            const index = parseInt(e.target.dataset.index);
            const nuevaCantidad = parseInt(e.target.value) || 1;
            
            if (detalleVenta[index].tipo_item === 'PRODUCTO') {
                const stock = getStockProducto(detalleVenta[index].inventario_id);
                if (nuevaCantidad > stock) {
                    Swal.fire('Error', `Stock insuficiente. Disponible: ${stock}`, 'error');
                    e.target.value = detalleVenta[index].cantidad;
                    return;
                }
            }
            
            detalleVenta[index].cantidad = nuevaCantidad;
            detalleVenta[index].subtotal = nuevaCantidad * detalleVenta[index].precio_unitario;
            
            actualizarTablaDetalle();
            calcularTotales();
        });
    });
    
    // Event listeners para eliminar items
    TablaDetalle.querySelectorAll('.eliminar-item').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const index = parseInt(e.target.closest('button').dataset.index);
            eliminarItem(index);
        });
    });
};

// Obtener stock de un producto
const getStockProducto = (inventarioId) => {
    const selectProducto = document.getElementById('producto_id');
    const option = selectProducto.querySelector(`option[value="${inventarioId}"]`);
    return option ? parseInt(option.dataset.stock) : 0;
};

// Eliminar item del detalle
const eliminarItem = (index) => {
    detalleVenta.splice(index, 1);
    actualizarTablaDetalle();
    calcularTotales();
};

// Calcular totales
const calcularTotales = () => {
    const subtotal = detalleVenta.reduce((sum, item) => sum + item.subtotal, 0);
    const descuento = parseFloat(document.getElementById('venta_descuento_input').value) || 0;
    const impuestos = parseFloat(document.getElementById('venta_impuestos_input').value) || 0;
    const total = subtotal - descuento + impuestos;
    
    // Actualizar campos hidden
    document.getElementById('venta_subtotal').value = subtotal.toFixed(2);
    document.getElementById('venta_descuento').value = descuento.toFixed(2);
    document.getElementById('venta_impuestos').value = impuestos.toFixed(2);
    document.getElementById('venta_total').value = total.toFixed(2);
    
    // Actualizar visualización
    document.getElementById('subtotal_display').textContent = `Q. ${subtotal.toFixed(2)}`;
    if (document.getElementById('total_display')) {
        document.getElementById('total_display').textContent = `Q. ${total.toFixed(2)}`;
    }
};

// Guardar venta
const GuardarVenta = async (event) => {
    event.preventDefault();
    BtnGuardar.disabled = true;

    if (!validarFormulario(FormularioVenta, ['venta_id'])) {
        Swal.fire('Error', 'Complete todos los campos requeridos', 'error');
        BtnGuardar.disabled = false;
        return;
    }
    
    if (detalleVenta.length === 0) {
        Swal.fire('Error', 'Debe agregar al menos un producto o servicio', 'error');
        BtnGuardar.disabled = false;
        return;
    }
    
    const clienteId = document.getElementById('cliente_id').value;
    if (!clienteId) {
        Swal.fire('Error', 'Debe seleccionar un cliente', 'error');
        BtnGuardar.disabled = false;
        return;
    }

    try {
        const formData = new FormData(FormularioVenta);
        formData.append('usuario_id', usuarioActual);
        formData.append('detalles', JSON.stringify(detalleVenta));
        
        const respuesta = await fetch('/empresa_celulares/venta/guardarAPI', {
            method: 'POST',
            body: formData
        });
        
        const data = await respuesta.json();
        
        if (data.codigo === 1) {
            Swal.fire({
                icon: 'success',
                title: 'Venta registrada',
                text: `${data.mensaje}\n\nNúmero: ${data.numero_venta}`,
                confirmButtonText: 'Aceptar'
            });
            
            limpiarTodo();
            BuscarVentas();
        } else {
            Swal.fire('Error', data.mensaje, 'error');
        }
    } catch (error) {
        console.error('Error guardando venta:', error);
        Swal.fire('Error', 'Error de conexión', 'error');
    }
    
    BtnGuardar.disabled = false;
};

// Buscar ventas
const BuscarVentas = async () => {
    try {
        const res = await fetch('/empresa_celulares/venta/buscarAPI');
        const { codigo, mensaje, data } = await res.json();
        
        if (codigo == 1) {
            if (typeof datatable !== 'undefined') {
                datatable.clear().draw();
                if (data && Array.isArray(data)) {
                    datatable.rows.add(data).draw();
                }
            }
        } else {
            console.error('Error buscando ventas:', mensaje);
        }
    } catch (error) {
        console.error('Error buscando ventas:', error);
    }
};

// DataTable configuración
let datatable;

// Inicializar DataTable solo si existe el elemento
const inicializarDataTable = () => {
    const tablaVentas = document.getElementById('TablaVentas');
    if (tablaVentas) {
        datatable = new DataTable('#TablaVentas', {
            language: lenguaje,
            data: [],
            columns: [
                { 
                    title: "No. Venta", 
                    data: null,
                    render: (data, type, row) => {
                        if (row.venta_fecha && row.venta_id) {
                            const fecha = new Date(row.venta_fecha);
                            const año = fecha.getFullYear();
                            const mes = String(fecha.getMonth() + 1).padStart(2, '0');
                            return `V-${año}${mes}-${String(row.venta_id).padStart(4, '0')}`;
                        }
                        return 'N/A';
                    }
                },
                { 
                    title: "Fecha", 
                    data: "venta_fecha",
                    render: (data) => {
                        if (data) {
                            const fecha = new Date(data);
                            return fecha.toLocaleDateString('es-GT') + ' ' + 
                                   fecha.toLocaleTimeString('es-GT', {hour: '2-digit', minute: '2-digit'});
                        }
                        return 'N/A';
                    }
                },
                { title: "Cliente", data: "cliente_nombre" },
                { title: "Usuario", data: "usuario_nombre" },
                { 
                    title: "Subtotal", 
                    data: "venta_subtotal",
                    render: (data) => `Q. ${parseFloat(data).toLocaleString('es-GT', {minimumFractionDigits: 2})}`,
                    className: 'text-end'
                },
                { 
                    title: "Total", 
                    data: "venta_total",
                    render: (data) => `Q. ${parseFloat(data).toLocaleString('es-GT', {minimumFractionDigits: 2})}`,
                    className: 'text-end'
                },
                { 
                    title: "Estado", 
                    data: "venta_estado",
                    render: (data) => {
                        const estados = {
                            'PENDIENTE': { texto: 'Pendiente', clase: 'warning' },
                            'PROCESANDO': { texto: 'Procesando', clase: 'info' },
                            'COMPLETADA': { texto: 'Completada', clase: 'success' },
                            'CANCELADA': { texto: 'Cancelada', clase: 'danger' },
                            'FACTURADA': { texto: 'Facturada', clase: 'primary' }
                        };
                        
                        const estado = estados[data] || { texto: data || 'N/A', clase: 'secondary' };
                        return `<span class="badge bg-${estado.clase}">${estado.texto}</span>`;
                    },
                    className: 'text-center'
                },
                { title: "Forma Pago", data: "venta_forma_pago" },
                {
                    title: "Acciones", 
                    data: "venta_id",
                    render: (id, t, row) => `
                        <div class="d-flex justify-content-center gap-1">
                            <button class="btn btn-sm btn-outline-info ver-detalle" data-id="${id}" title="Ver Detalle">
                                <i class="bi bi-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-primary cambiar-estado" data-id="${id}" title="Cambiar Estado">
                                <i class="bi bi-arrow-repeat"></i>
                            </button>
                            <button class="btn btn-danger btn-sm eliminar" data-id="${id}" title="Eliminar">🗑️</button>
                        </div>
                    `,
                    orderable: false,
                    className: 'text-center'
                }
            ],
            order: [[1, 'desc']],
            pageLength: 10
        });

        // Event listeners para la tabla
        datatable.on('click', '.ver-detalle', verDetalle);
        datatable.on('click', '.eliminar', EliminarVenta);
        datatable.on('click', '.cambiar-estado', CambiarEstado);
    }
};

// Ver detalle de venta
const verDetalle = async (e) => {
    const id = e.currentTarget.dataset.id;
    
    try {
        const res = await fetch('/empresa_celulares/venta/buscarAPI');
        const { codigo, data } = await res.json();
        
        if (codigo === 1) {
            const venta = data.find(v => v.venta_id == id);
            if (venta && venta.detalle) {
                let detalleHtml = '<table class="table table-sm"><thead><tr><th>Descripción</th><th>Cant.</th><th>Precio</th><th>Subtotal</th></tr></thead><tbody>';
                
                venta.detalle.forEach(item => {
                    detalleHtml += `
                        <tr>
                            <td>${item.item_descripcion}</td>
                            <td class="text-center">${item.detalle_cantidad}</td>
                            <td class="text-end">Q. ${parseFloat(item.detalle_precio_unitario).toFixed(2)}</td>
                            <td class="text-end">Q. ${parseFloat(item.detalle_subtotal).toFixed(2)}</td>
                        </tr>
                    `;
                });
                
                detalleHtml += '</tbody></table>';
                
                Swal.fire({
                    title: `Detalle de Venta ${venta.numero_venta}`,
                    html: detalleHtml,
                    width: 600,
                    confirmButtonText: 'Cerrar'
                });
            }
        }
    } catch (error) {
        console.error('Error obteniendo detalle:', error);
    }
};

// Limpiar formulario
const limpiarTodo = () => {
    FormularioVenta.reset();
    detalleVenta = [];
    actualizarTablaDetalle();
    calcularTotales();
    
    FormularioVenta.querySelectorAll('.is-valid, .is-invalid').forEach(el => {
        el.classList.remove('is-valid', 'is-invalid');
    });
    
    // Resetear fecha actual
    document.getElementById('venta_fecha').value = new Date().toISOString().split('T')[0];
};

// Eliminar venta
const EliminarVenta = async (e) => {
    const id = e.currentTarget.dataset.id;
    const confirmar = await Swal.fire({
        icon: "warning", 
        title: "¿Eliminar venta?", 
        text: "Esta acción no se puede deshacer.",
        showCancelButton: true, 
        confirmButtonText: "Sí, eliminar", 
        cancelButtonText: "Cancelar",
    });

    if (confirmar.isConfirmed) {
        try {
            const res = await fetch('/empresa_celulares/venta/eliminarAPI', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ venta_id: id })
            });
            const { codigo, mensaje } = await res.json();
            if (codigo == 1) {
                Swal.fire({ icon: "success", title: "Eliminado", text: mensaje });
                BuscarVentas();
            } else {
                Swal.fire({ icon: "error", title: "Error", text: mensaje });
            }
        } catch (error) {
            console.error(error);
        }
    }
};

// Cambiar estado
const CambiarEstado = async (e) => {
    const id = e.currentTarget.dataset.id;
    
    const { value: nuevoEstado } = await Swal.fire({
        title: 'Cambiar Estado',
        input: 'select',
        inputOptions: {
            'PENDIENTE': 'Pendiente',
            'PROCESANDO': 'Procesando',
            'COMPLETADA': 'Completada',
            'CANCELADA': 'Cancelada',
            'FACTURADA': 'Facturada'
        },
        inputPlaceholder: 'Seleccione el nuevo estado',
        showCancelButton: true,
        confirmButtonText: 'Cambiar',
        cancelButtonText: 'Cancelar'
    });

    if (nuevoEstado) {
        try {
            const res = await fetch('/empresa_celulares/venta/cambiarEstadoAPI', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `venta_id=${id}&nuevo_estado=${nuevoEstado}`
            });
            const { codigo, mensaje } = await res.json();
            if (codigo == 1) {
                Swal.fire({ icon: "success", title: "Estado actualizado", text: mensaje });
                BuscarVentas();
            } else {
                Swal.fire({ icon: "error", title: "Error", text: mensaje });
            }
        } catch (error) {
            console.error(error);
        }
    }
};

// Cargar servicios disponibles
const cargarServicios = async () => {
    try {
        console.log('Iniciando carga de servicios...');
        
        const respuesta = await fetch('/empresa_celulares/venta/obtenerServiciosAPI');
        const data = await respuesta.json();
        
        console.log('Respuesta completa servicios:', data);
        
        const selectServicio = document.getElementById('servicio_id');
        if (!selectServicio) {
            console.error('Elemento servicio_id no encontrado');
            return;
        }
        
        selectServicio.innerHTML = '<option value="">Seleccione un servicio</option>';
        
        if (data.codigo === 1) {
            console.log('Servicios encontrados:', data.data?.length || 0);
            
            if (data.data && Array.isArray(data.data) && data.data.length > 0) {
                data.data.forEach((servicio, index) => {
                    console.log(`Procesando servicio ${index + 1}:`, servicio);
                    
                    const dispositivo = servicio.dispositivo_descripcion || 
                                      `${servicio.recepcion_marca || ''} ${servicio.recepcion_modelo || ''}`.trim() ||
                                      servicio.recepcion_tipo_celular || 
                                      'Dispositivo';
                    
                    const descripcionServicio = servicio.tipo_servicio_nombre || 'Servicio';
                    const costo = parseFloat(servicio.orden_costo_total || 0);
                    
                    const option = document.createElement('option');
                    option.value = servicio.orden_id;
                    option.textContent = `${descripcionServicio} - ${dispositivo} - Q.${costo.toFixed(2)}`;
                    option.dataset.precio = costo;
                    option.dataset.descripcion = `${descripcionServicio} - ${dispositivo}`;
                    
                    selectServicio.appendChild(option);
                    
                    console.log(`Servicio agregado: ${option.textContent}`);
                });
                
                console.log(`Total de servicios cargados: ${data.data.length}`);
            } else {
                console.log('No hay servicios disponibles');
                
                // Agregar opción informativa
                const option = document.createElement('option');
                option.value = '';
                option.textContent = 'No hay servicios completados disponibles';
                option.disabled = true;
                selectServicio.appendChild(option);
            }
        } else {
            console.error('Error en la respuesta:', data.mensaje);
            
            // Agregar opción de error
            const option = document.createElement('option');
            option.value = '';
            option.textContent = 'Error al cargar servicios';
            option.disabled = true;
            selectServicio.appendChild(option);
        }
        
    } catch (error) {
        console.error('Error cargando servicios:', error);
        
        const selectServicio = document.getElementById('servicio_id');
        if (selectServicio) {
            const option = document.createElement('option');
            option.value = '';
            option.textContent = 'Error de conexión';
            option.disabled = true;
            selectServicio.appendChild(option);
        }
    }
};

// Event Listeners
document.addEventListener('DOMContentLoaded', () => {
    // Verificar elementos del DOM
    if (!FormularioVenta) {
        console.error('FormularioVenta no encontrado');
        return;
    }
    
    console.log('Inicializando sistema de ventas...');
    
    // Cargar datos iniciales
    cargarClientes();
    cargarProductos();
    cargarServicios();
    
    // Inicializar DataTable si existe
    inicializarDataTable();
    BuscarVentas();
    
    // Event listeners del formulario
    FormularioVenta.addEventListener('submit', GuardarVenta);
    if (BtnLimpiar) BtnLimpiar.addEventListener('click', limpiarTodo);
    
    // Event listeners para agregar items
    const btnAgregarProducto = document.getElementById('btn-agregar-producto');
    const btnAgregarServicio = document.getElementById('btn-agregar-servicio');
    
    if (btnAgregarProducto) {
        btnAgregarProducto.addEventListener('click', agregarProducto);
    }
    
    if (btnAgregarServicio) {
        btnAgregarServicio.addEventListener('click', agregarServicio);
    }
    
    // Event listeners para cálculos
    const descuentoInput = document.getElementById('venta_descuento_input');
    const impuestosInput = document.getElementById('venta_impuestos_input');
    
    if (descuentoInput) {
        descuentoInput.addEventListener('input', calcularTotales);
    }
    
    if (impuestosInput) {
        impuestosInput.addEventListener('input', calcularTotales);
    }
    
    console.log('Módulo de ventas inicializado correctamente');
});