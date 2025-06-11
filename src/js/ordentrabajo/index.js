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
const FormularioOrdenTrabajo = document.getElementById('FormularioOrdenTrabajo');
const BtnGuardar = document.getElementById('BtnGuardar');
const BtnModificar = document.getElementById('BtnModificar');
const BtnLimpiar = document.getElementById('BtnLimpiar');

let ordenEditando = null;

// Cargar recepciones disponibles para el select
const cargarRecepciones = async () => {
    try {
        console.log('Cargando recepciones...');
        const respuesta = await fetch('/empresa_celulares/ordentrabajo/obtenerRecepcionesAPI');
        const data = await respuesta.json();
        
        console.log('Respuesta recepciones:', data);
        
        const selectRecepcion = document.getElementById('recepcion_id');
        selectRecepcion.innerHTML = '<option value="">Seleccione una recepción</option>';
        
        if (data.codigo === 1 && data.data) {
            data.data.forEach(recepcion => {
                const dispositivo = `${recepcion.recepcion_marca} ${recepcion.recepcion_modelo || ''} (${recepcion.recepcion_tipo_celular})`.trim();
                const option = document.createElement('option');
                option.value = recepcion.recepcion_id;
                option.textContent = `${recepcion.cliente_nombre} - ${dispositivo} - ${recepcion.recepcion_motivo_ingreso.substring(0, 30)}...`;
                selectRecepcion.appendChild(option);
            });
            console.log('Recepciones cargadas exitosamente');
        } else {
            console.warn('No hay recepciones disponibles');
        }
    } catch (error) {
        console.error('Error cargando recepciones:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'No se pudieron cargar las recepciones'
        });
    }
};

// Cargar empleados para el select
const cargarEmpleados = async () => {
    try {
        console.log('Cargando empleados...');
        const respuesta = await fetch('/empresa_celulares/empleado/buscarAPI');
        const data = await respuesta.json();
        
        console.log('Respuesta empleados:', data);
        
        const selectEmpleado = document.getElementById('empleado_id');
        selectEmpleado.innerHTML = '<option value="">Sin asignar</option>';
        
        if (data.codigo === 1 && data.data) {
            data.data.forEach(empleado => {
                const nombreCompleto = `${empleado.empleado_nom1} ${empleado.empleado_nom2 || ''} ${empleado.empleado_ape1} ${empleado.empleado_ape2 || ''}`.trim();
                const option = document.createElement('option');
                option.value = empleado.empleado_id;
                option.textContent = `${nombreCompleto} - ${empleado.empleado_especialidad || ''}`;
                selectEmpleado.appendChild(option);
            });
            console.log('Empleados cargados exitosamente');
        } else {
            console.warn('No hay empleados disponibles');
        }
    } catch (error) {
        console.error('Error cargando empleados:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'No se pudieron cargar los empleados'
        });
    }
};

// Cargar tipos de servicio para el select
const cargarTiposServicio = async () => {
    try {
        console.log('Cargando tipos de servicio...');
        const respuesta = await fetch('/empresa_celulares/ordentrabajo/obtenerTiposServicioAPI');
        const data = await respuesta.json();
        
        console.log('Respuesta tipos de servicio:', data);
        
        const selectTipoServicio = document.getElementById('tipo_servicio_id');
        selectTipoServicio.innerHTML = '<option value="">Seleccione un tipo de servicio</option>';
        
        if (data.codigo === 1 && data.data) {
            data.data.forEach(tipo => {
                const option = document.createElement('option');
                option.value = tipo.tipo_servicio_id;
                option.textContent = `${tipo.tipo_servicio_nombre} - Q.${parseFloat(tipo.tipo_servicio_precio_base).toFixed(2)}`;
                option.dataset.precio = tipo.tipo_servicio_precio_base;
                selectTipoServicio.appendChild(option);
            });
            console.log('Tipos de servicio cargados exitosamente');
        } else {
            console.warn('No hay tipos de servicio disponibles');
        }
    } catch (error) {
        console.error('Error cargando tipos de servicio:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'No se pudieron cargar los tipos de servicio'
        });
    }
};

// Calcular costo total automáticamente
const calcularCostoTotal = () => {
    const costoRepuestos = parseFloat(document.getElementById('orden_costo_repuestos').value) || 0;
    const costoManoObra = parseFloat(document.getElementById('orden_costo_mano_obra').value) || 0;
    const total = costoRepuestos + costoManoObra;
    
    document.getElementById('orden_costo_total').value = total.toFixed(2);
};

// Auto-llenar precio base cuando se selecciona un tipo de servicio
const autoLlenarPrecio = () => {
    const selectTipoServicio = document.getElementById('tipo_servicio_id');
    const selectedOption = selectTipoServicio.selectedOptions[0];
    
    if (selectedOption && selectedOption.dataset.precio) {
        const precioBase = parseFloat(selectedOption.dataset.precio);
        document.getElementById('orden_costo_mano_obra').value = precioBase.toFixed(2);
        calcularCostoTotal();
    }
};

// Guardar Orden de Trabajo
const GuardarOrdenTrabajo = async (event) => {
    event.preventDefault();
    BtnGuardar.disabled = true;

    // Validar formulario
    if (!validarFormulario(FormularioOrdenTrabajo, ['orden_id'])) {
        Swal.fire({ 
            icon: "info", 
            title: "Formulario incompleto", 
            text: "Debe completar todos los campos requeridos" 
        });
        BtnGuardar.disabled = false;
        return;
    }

    // Validar selects específicos
    const recepcionId = document.getElementById('recepcion_id').value;
    const tipoServicioId = document.getElementById('tipo_servicio_id').value;
    
    if (!recepcionId) {
        Swal.fire({
            icon: 'error',
            title: 'Recepción requerida',
            text: 'Debe seleccionar una recepción'
        });
        BtnGuardar.disabled = false;
        return;
    }
    
    if (!tipoServicioId) {
        Swal.fire({
            icon: 'error',
            title: 'Tipo de servicio requerido',
            text: 'Debe seleccionar un tipo de servicio'
        });
        BtnGuardar.disabled = false;
        return;
    }

    const body = new FormData(FormularioOrdenTrabajo);
    const url = ordenEditando ? 
        '/empresa_celulares/ordentrabajo/modificarAPI' : 
        '/empresa_celulares/ordentrabajo/guardarAPI';
    
    if (ordenEditando) {
        body.append('orden_id', ordenEditando);
    }

    try {
        console.log('Enviando formulario a:', url);
        const respuesta = await fetch(url, { method: 'POST', body });
        const data = await respuesta.json();
        
        console.log('Respuesta del servidor:', data);
        
        if (data.codigo == 1) {
            let mensaje = data.mensaje;
            if (data.numero_orden) {
                mensaje += `\n\nNúmero de orden: ${data.numero_orden}`;
            }
            
            Swal.fire({ 
                icon: "success", 
                title: ordenEditando ? "Orden actualizada" : "Orden registrada", 
                text: mensaje,
                confirmButtonText: 'Aceptar'
            });
            
            limpiarTodo();
            BuscarOrdenesTrabajo();
        } else {
            Swal.fire({ icon: "error", title: "Error", text: data.mensaje });
        }
    } catch (error) {
        console.error('Error en guardar:', error);
        Swal.fire({ icon: "error", title: "Error", text: "Error de conexión al guardar" });
    }
    BtnGuardar.disabled = false;
};

// Buscar Órdenes de Trabajo
const BuscarOrdenesTrabajo = async () => {
    try {
        console.log('Buscando órdenes de trabajo...');
        const res = await fetch('/empresa_celulares/ordentrabajo/buscarAPI');
        const { codigo, mensaje, data } = await res.json();
        
        console.log('Órdenes encontradas:', { codigo, mensaje, data });
        
        if (codigo == 1) {
            datatable.clear().draw();
            if (data && Array.isArray(data)) {
                datatable.rows.add(data).draw();
            }
        } else {
            console.warn('Error al buscar órdenes:', mensaje);
            Swal.fire({ icon: "info", title: "Error", text: mensaje });
        }
    } catch (error) {
        console.error('Error buscando órdenes:', error);
        Swal.fire({ icon: "error", title: "Error", text: "Error al cargar órdenes de trabajo" });
    }
};

// DataTable Configuración
const datatable = new DataTable('#TablaOrdenesTrabajo', {
    language: lenguaje,
    data: [],
    columns: [
        { 
            title: "No. Orden", 
            data: null,
            render: (data, type, row) => {
                if (row.orden_fecha_asignacion && row.orden_id) {
                    const fecha = new Date(row.orden_fecha_asignacion);
                    const año = fecha.getFullYear();
                    const mes = String(fecha.getMonth() + 1).padStart(2, '0');
                    return `OT-${año}${mes}-${String(row.orden_id).padStart(4, '0')}`;
                }
                return 'N/A';
            }
        },
        { 
            title: "Fecha Asignación", 
            data: "orden_fecha_asignacion",
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
        { title: "Dispositivo", data: "dispositivo_info" },
        { title: "Tipo Servicio", data: "tipo_servicio_nombre" },
        { title: "Empleado", data: "empleado_nombre" },
        { 
            title: "Estado", 
            data: "orden_estado",
            render: (data, type, row) => {
                const estados = {
                    'ASIGNADA': { texto: 'Asignada', clase: 'primary' },
                    'EN_PROGRESO': { texto: 'En Progreso', clase: 'warning' },
                    'EN_ESPERA_REPUESTOS': { texto: 'Esperando Repuestos', clase: 'info' },
                    'PAUSADA': { texto: 'Pausada', clase: 'secondary' },
                    'COMPLETADA': { texto: 'Completada', clase: 'success' },
                    'CANCELADA': { texto: 'Cancelada', clase: 'danger' },
                    'ENTREGADA': { texto: 'Entregada', clase: 'dark' }
                };
                
                const estado = estados[data] || { texto: data || 'N/A', clase: 'secondary' };
                return `<span class="badge bg-${estado.clase}">${estado.texto}</span>`;
            },
            className: 'text-center'
        },
        { 
            title: "Costo Total", 
            data: "orden_costo_total",
            render: (data) => {
                if (data && data > 0) {
                    return `Q. ${parseFloat(data).toLocaleString('es-GT', {minimumFractionDigits: 2})}`;
                }
                return 'Q. 0.00';
            },
            className: 'text-end'
        },
        {
            title: "Acciones", 
            data: "orden_id",
            render: (id, t, row) => `
                <div class="d-flex justify-content-center gap-1">
                    <button class="btn btn-sm btn-outline-info cambiar-estado" data-id="${id}" title="Cambiar Estado">
                        <i class="bi bi-arrow-repeat"></i>
                    </button>
                    <button class="btn btn-warning btn-sm modificar" data-id="${id}" data-json='${JSON.stringify(row)}' title="Editar">✏️</button>
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

// Llenar formulario para editar
const llenarFormulario = (e) => {
    const datos = JSON.parse(e.currentTarget.dataset.json);
    console.log('Llenando formulario:', datos);
    
    document.getElementById('recepcion_id').value = datos.recepcion_id || '';
    document.getElementById('empleado_id').value = datos.empleado_id || '';
    document.getElementById('tipo_servicio_id').value = datos.tipo_servicio_id || '';
    
    // Formatear fechas para inputs datetime-local
    if (datos.orden_fecha_inicio) {
        try {
            // Intentar diferentes formatos de fecha
            let fechaInicio;
            if (datos.orden_fecha_inicio.includes('T')) {
                // Ya está en formato ISO
                fechaInicio = new Date(datos.orden_fecha_inicio);
            } else {
                // Formato YYYY-MM-DD HH:MM:SS
                fechaInicio = new Date(datos.orden_fecha_inicio.replace(' ', 'T'));
            }
            
            if (!isNaN(fechaInicio.getTime())) {
                // Convertir a formato local para datetime-local input
                const fechaInicioLocal = new Date(fechaInicio.getTime() - (fechaInicio.getTimezoneOffset() * 60000));
                document.getElementById('orden_fecha_inicio').value = fechaInicioLocal.toISOString().slice(0, 16);
            }
        } catch (e) {
            console.warn('Error parseando fecha de inicio:', e, datos.orden_fecha_inicio);
        }
    }
    
    if (datos.orden_fecha_finalizacion) {
        try {
            let fechaFin;
            if (datos.orden_fecha_finalizacion.includes('T')) {
                fechaFin = new Date(datos.orden_fecha_finalizacion);
            } else {
                fechaFin = new Date(datos.orden_fecha_finalizacion.replace(' ', 'T'));
            }
            
            if (!isNaN(fechaFin.getTime())) {
                const fechaFinLocal = new Date(fechaFin.getTime() - (fechaFin.getTimezoneOffset() * 60000));
                document.getElementById('orden_fecha_finalizacion').value = fechaFinLocal.toISOString().slice(0, 16);
            }
        } catch (e) {
            console.warn('Error parseando fecha de finalización:', e, datos.orden_fecha_finalizacion);
        }
    }
    
    document.getElementById('orden_diagnostico').value = datos.orden_diagnostico || '';
    document.getElementById('orden_trabajo_realizado').value = datos.orden_trabajo_realizado || '';
    document.getElementById('orden_repuestos_utilizados').value = datos.orden_repuestos_utilizados || '';
    document.getElementById('orden_costo_repuestos').value = datos.orden_costo_repuestos || '0';
    document.getElementById('orden_costo_mano_obra').value = datos.orden_costo_mano_obra || '0';
    document.getElementById('orden_costo_total').value = datos.orden_costo_total || '0';
    document.getElementById('orden_estado').value = datos.orden_estado || 'ASIGNADA';
    document.getElementById('orden_observaciones').value = datos.orden_observaciones || '';

    // Mostrar sección de fechas para edición
    const fechasSection = document.getElementById('fechas-section');
    if (fechasSection) {
        fechasSection.style.display = 'block';
    }

    ordenEditando = datos.orden_id;
    BtnGuardar.textContent = 'Actualizar Orden';
    BtnGuardar.className = 'btn btn-warning px-4';
    BtnGuardar.classList.remove('d-none');
    if (BtnModificar) BtnModificar.classList.add('d-none');
    
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

// Limpiar todo
const limpiarTodo = () => {
    if (FormularioOrdenTrabajo) {
        FormularioOrdenTrabajo.reset();
    }
    
    ordenEditando = null;
    BtnGuardar.textContent = 'Registrar Orden';
    BtnGuardar.className = 'btn btn-success px-4';
    BtnGuardar.classList.remove('d-none');
    if (BtnModificar) BtnModificar.classList.add('d-none');
    
    // Ocultar sección de fechas para nuevas órdenes
    const fechasSection = document.getElementById('fechas-section');
    if (fechasSection) {
        fechasSection.style.display = 'none';
    }
    
    // Limpiar validaciones
    if (FormularioOrdenTrabajo) {
        FormularioOrdenTrabajo.querySelectorAll('.is-valid, .is-invalid').forEach(el => {
            el.classList.remove('is-valid', 'is-invalid');
        });
    }
};

// Eliminar orden de trabajo
const EliminarOrdenTrabajo = async (e) => {
    const id = e.currentTarget.dataset.id;
    const confirmar = await Swal.fire({
        icon: "warning", 
        title: "¿Eliminar orden de trabajo?", 
        text: "Esta acción desactivará la orden de trabajo.",
        showCancelButton: true, 
        confirmButtonText: "Sí, eliminar", 
        cancelButtonText: "Cancelar",
    });

    if (confirmar.isConfirmed) {
        try {
            const res = await fetch('/empresa_celulares/ordentrabajo/eliminarAPI', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ orden_id: id })
            });
            const { codigo, mensaje } = await res.json();
            if (codigo == 1) {
                Swal.fire({ icon: "success", title: "Eliminado", text: mensaje });
                BuscarOrdenesTrabajo();
            } else {
                Swal.fire({ icon: "error", title: "Error", text: mensaje });
            }
        } catch (error) {
            console.error(error);
            Swal.fire({ icon: "error", title: "Error", text: "Error al eliminar" });
        }
    }
};

// Cambiar estado de orden de trabajo
const CambiarEstado = async (e) => {
    const id = e.currentTarget.dataset.id;
    
    const { value: nuevoEstado } = await Swal.fire({
        title: 'Cambiar Estado',
        input: 'select',
        inputOptions: {
            'ASIGNADA': 'Asignada',
            'EN_PROGRESO': 'En Progreso',
            'EN_ESPERA_REPUESTOS': 'En Espera de Repuestos',
            'PAUSADA': 'Pausada',
            'COMPLETADA': 'Completada',
            'CANCELADA': 'Cancelada',
            'ENTREGADA': 'Entregada'
        },
        inputPlaceholder: 'Seleccione el nuevo estado',
        showCancelButton: true,
        confirmButtonText: 'Cambiar',
        cancelButtonText: 'Cancelar'
    });

    if (nuevoEstado) {
        try {
            const res = await fetch('/empresa_celulares/ordentrabajo/cambiarEstadoAPI', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `orden_id=${id}&nuevo_estado=${nuevoEstado}`
            });
            const { codigo, mensaje } = await res.json();
            if (codigo == 1) {
                Swal.fire({ icon: "success", title: "Estado actualizado", text: mensaje });
                BuscarOrdenesTrabajo();
            } else {
                Swal.fire({ icon: "error", title: "Error", text: mensaje });
            }
        } catch (error) {
            console.error(error);
            Swal.fire({ icon: "error", title: "Error", text: "Error al cambiar estado" });
        }
    }
};

// Event Listeners
document.addEventListener('DOMContentLoaded', () => {
    console.log('DOM cargado, inicializando aplicación...');
    
    // Verificar que los elementos existen
    if (!FormularioOrdenTrabajo) {
        console.error('FormularioOrdenTrabajo no encontrado');
        return;
    }
    
    if (!BtnGuardar) {
        console.error('BtnGuardar no encontrado');
        return;
    }
    
    // Cargar datos
    cargarRecepciones();
    cargarEmpleados();
    cargarTiposServicio();
    BuscarOrdenesTrabajo();
    
    // Event Listeners
    FormularioOrdenTrabajo.addEventListener('submit', GuardarOrdenTrabajo);
    if (BtnLimpiar) BtnLimpiar.addEventListener('click', limpiarTodo);
    
    // Event listeners para cálculos automáticos
    document.getElementById('orden_costo_repuestos').addEventListener('input', calcularCostoTotal);
    document.getElementById('orden_costo_mano_obra').addEventListener('input', calcularCostoTotal);
    document.getElementById('tipo_servicio_id').addEventListener('change', autoLlenarPrecio);
    
    // Event listeners para la tabla
    datatable.on('click', '.modificar', llenarFormulario);
    datatable.on('click', '.eliminar', EliminarOrdenTrabajo);
    datatable.on('click', '.cambiar-estado', CambiarEstado);
    
    console.log('Aplicación inicializada correctamente');
});