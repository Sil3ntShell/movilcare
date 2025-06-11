import DataTable from "datatables.net-bs5";
import { validarFormulario } from '../funciones';
import Swal from "sweetalert2";
import { lenguaje } from "../lenguaje";

// Elementos del DOM
const FormularioRecepcion = document.getElementById('FormularioRecepcion');
const BtnGuardar = document.getElementById('BtnGuardar');
const BtnModificar = document.getElementById('BtnModificar');
const BtnLimpiar = document.getElementById('BtnLimpiar');

let recepcionEditando = null;

// Cargar clientes para el select
const cargarClientes = async () => {
    try {
        console.log('Cargando clientes...');
        const respuesta = await fetch('/empresa_celulares/cliente/buscarAPI');
        const data = await respuesta.json();
        
        console.log('Respuesta clientes:', data);
        
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
            console.log('Clientes cargados exitosamente');
        } else {
            console.warn('No hay clientes disponibles');
        }
    } catch (error) {
        console.error('Error cargando clientes:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'No se pudieron cargar los clientes'
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
        selectEmpleado.innerHTML = '<option value="">Seleccione un empleado</option>';
        
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

// Guardar Recepción
const GuardarRecepcion = async (event) => {
    event.preventDefault();
    BtnGuardar.disabled = true;

    if (!validarFormulario(FormularioRecepcion, ['recepcion_id'])) {
        Swal.fire({ 
            icon: "info", 
            title: "Formulario incompleto", 
            text: "Debe completar todos los campos requeridos" 
        });
        BtnGuardar.disabled = false;
        return;
    }

    // Validar selects específicos
    const clienteId = document.getElementById('cliente_id').value;
    const empleadoId = document.getElementById('empleado_id').value;
    
    if (!clienteId) {
        Swal.fire({
            icon: 'error',
            title: 'Cliente requerido',
            text: 'Debe seleccionar un cliente'
        });
        BtnGuardar.disabled = false;
        return;
    }
    
    if (!empleadoId) {
        Swal.fire({
            icon: 'error',
            title: 'Empleado requerido',
            text: 'Debe seleccionar un empleado'
        });
        BtnGuardar.disabled = false;
        return;
    }

    const body = new FormData(FormularioRecepcion);
    const url = recepcionEditando ? 
        '/empresa_celulares/recepcion/modificarAPI' : 
        '/empresa_celulares/recepcion/guardarAPI';
    
    if (recepcionEditando) {
        body.append('recepcion_id', recepcionEditando);
    }

    try {
        console.log('Enviando formulario a:', url);
        const respuesta = await fetch(url, { method: 'POST', body });
        const data = await respuesta.json();
        
        console.log('Respuesta del servidor:', data);
        
        if (data.codigo == 1) {
            let mensaje = data.mensaje;
            if (data.numero_recepcion) {
                mensaje += `\n\nNúmero de recepción: ${data.numero_recepcion}`;
            }
            
            Swal.fire({ 
                icon: "success", 
                title: recepcionEditando ? "Recepción actualizada" : "Recepción registrada", 
                text: mensaje,
                confirmButtonText: 'Aceptar'
            });
            
            limpiarTodo();
            BuscarRecepciones();
        } else {
            Swal.fire({ icon: "error", title: "Error", text: data.mensaje });
        }
    } catch (error) {
        console.error('Error en guardar:', error);
        Swal.fire({ icon: "error", title: "Error", text: "Error de conexión al guardar" });
    }
    BtnGuardar.disabled = false;
};

// Buscar Recepciones
const BuscarRecepciones = async () => {
    try {
        console.log('Buscando recepciones...');
        const res = await fetch('/empresa_celulares/recepcion/buscarAPI');
        const { codigo, mensaje, data } = await res.json();
        
        console.log('Recepciones encontradas:', { codigo, mensaje, data });
        
        if (codigo == 1) {
            datatable.clear().draw();
            if (data && Array.isArray(data)) {
                datatable.rows.add(data).draw();
            }
        } else {
            console.warn('Error al buscar recepciones:', mensaje);
            Swal.fire({ icon: "info", title: "Error", text: mensaje });
        }
    } catch (error) {
        console.error('Error buscando recepciones:', error);
        Swal.fire({ icon: "error", title: "Error", text: "Error al cargar recepciones" });
    }
};

// DataTable Configuración
const datatable = new DataTable('#TablaRecepciones', {
    language: lenguaje,
    data: [],
    columns: [
        { 
            title: "No. Recepción", 
            data: null,
            render: (data, type, row) => {
                if (row.recepcion_fecha && row.recepcion_id) {
                    const fecha = new Date(row.recepcion_fecha);
                    const año = fecha.getFullYear();
                    const mes = String(fecha.getMonth() + 1).padStart(2, '0');
                    return `REC-${año}${mes}-${String(row.recepcion_id).padStart(4, '0')}`;
                }
                return 'N/A';
            }
        },
        { 
            title: "Fecha", 
            data: "recepcion_fecha",
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
        { title: "Empleado", data: "empleado_nombre" },
        { 
            title: "Dispositivo", 
            data: null,
            render: (data, type, row) => `${row.recepcion_marca || ''} ${row.recepcion_modelo || ''} (${row.recepcion_tipo_celular || ''})`.trim()
        },
        { 
            title: "Problema", 
            data: "recepcion_motivo_ingreso",
            render: (data) => data && data.length > 50 ? data.substring(0, 50) + '...' : (data || '')
        },
        { 
            title: "Estado", 
            data: "recepcion_estado",
            render: (data, type, row) => {
                const estados = {
                    'RECIBIDO': { texto: 'Recibido', clase: 'primary' },
                    'EN_DIAGNOSTICO': { texto: 'En Diagnóstico', clase: 'warning' },
                    'ESPERANDO_REPUESTOS': { texto: 'Esperando Repuestos', clase: 'info' },
                    'EN_REPARACION': { texto: 'En Reparación', clase: 'secondary' },
                    'REPARADO': { texto: 'Reparado', clase: 'success' },
                    'ENTREGADO': { texto: 'Entregado', clase: 'dark' },
                    'CANCELADO': { texto: 'Cancelado', clase: 'danger' }
                };
                
                const estado = estados[data] || { texto: data || 'N/A', clase: 'secondary' };
                return `<span class="badge bg-${estado.clase}">${estado.texto}</span>`;
            },
            className: 'text-center'
        },
        { 
            title: "Costo Est.", 
            data: "recepcion_costo_estimado",
            render: (data) => {
                if (data && data > 0) {
                    return `Q. ${parseFloat(data).toLocaleString('es-GT', {minimumFractionDigits: 2})}`;
                }
                return 'Por definir';
            },
            className: 'text-end'
        },
        {
            title: "Acciones", 
            data: "recepcion_id",
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
    
    document.getElementById('cliente_id').value = datos.cliente_id || '';
    document.getElementById('empleado_id').value = datos.empleado_id || '';
    document.getElementById('recepcion_tipo_celular').value = datos.recepcion_tipo_celular || '';
    document.getElementById('recepcion_marca').value = datos.recepcion_marca || '';
    document.getElementById('recepcion_modelo').value = datos.recepcion_modelo || '';
    document.getElementById('recepcion_imei').value = datos.recepcion_imei || '';
    document.getElementById('recepcion_numero_serie').value = datos.recepcion_numero_serie || '';
    document.getElementById('recepcion_motivo_ingreso').value = datos.recepcion_motivo_ingreso || '';
    document.getElementById('recepcion_estado_dispositivo').value = datos.recepcion_estado_dispositivo || '';
    document.getElementById('recepcion_accesorios').value = datos.recepcion_accesorios || '';
    document.getElementById('recepcion_observaciones_cliente').value = datos.recepcion_observaciones_cliente || '';
    document.getElementById('recepcion_costo_estimado').value = datos.recepcion_costo_estimado || '';
    document.getElementById('recepcion_tiempo_estimado').value = datos.recepcion_tiempo_estimado || 1;
    document.getElementById('recepcion_estado').value = datos.recepcion_estado || 'RECIBIDO';

    // Mostrar sección de estado
    const estadoSection = document.getElementById('estado-section');
    if (estadoSection) {
        estadoSection.style.display = 'block';
    }

    recepcionEditando = datos.recepcion_id;
    BtnGuardar.textContent = 'Actualizar Recepción';
    BtnGuardar.className = 'btn btn-warning px-4';
    BtnGuardar.classList.remove('d-none');
    if (BtnModificar) BtnModificar.classList.add('d-none');
    
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

// Limpiar todo
const limpiarTodo = () => {
    if (FormularioRecepcion) {
        FormularioRecepcion.reset();
    }
    
    recepcionEditando = null;
    BtnGuardar.textContent = 'Registrar Recepción';
    BtnGuardar.className = 'btn btn-success px-4';
    BtnGuardar.classList.remove('d-none');
    if (BtnModificar) BtnModificar.classList.add('d-none');
    
    // Ocultar sección de estado
    const estadoSection = document.getElementById('estado-section');
    if (estadoSection) {
        estadoSection.style.display = 'none';
    }
    
    // Limpiar validaciones
    if (FormularioRecepcion) {
        FormularioRecepcion.querySelectorAll('.is-valid, .is-invalid').forEach(el => {
            el.classList.remove('is-valid', 'is-invalid');
        });
    }
};

// Modificar Recepción
const ModificarRecepcion = async (event) => {
    event.preventDefault();
    BtnModificar.disabled = true;

    if (!validarFormulario(FormularioRecepcion, ['recepcion_id'])) {
        Swal.fire({ 
            icon: "info", 
            title: "Formulario incompleto", 
            text: "Complete todos los campos" 
        });
        BtnModificar.disabled = false;
        return;
    }

    const body = new FormData(FormularioRecepcion);
    const url = '/empresa_celulares/recepcion/modificarAPI';

    try {
        const respuesta = await fetch(url, { method: 'POST', body });
        const { codigo, mensaje } = await respuesta.json();
        if (codigo == 1) {
            Swal.fire({ 
                icon: "success", 
                title: "Recepción modificada", 
                text: mensaje 
            });
            limpiarTodo();
            BuscarRecepciones();
        } else {
            Swal.fire({ 
                icon: "error", 
                title: "Error", 
                text: mensaje 
            });
        }
    } catch (error) {
        console.error(error);
        Swal.fire({ 
            icon: "error", 
            title: "Error", 
            text: "Error de conexión" 
        });
    }
    BtnModificar.disabled = false;
};

// Eliminar recepción
const EliminarRecepcion = async (e) => {
    const id = e.currentTarget.dataset.id;
    const confirmar = await Swal.fire({
        icon: "warning", 
        title: "¿Eliminar recepción?", 
        text: "Esta acción desactivará la recepción.",
        showCancelButton: true, 
        confirmButtonText: "Sí, eliminar", 
        cancelButtonText: "Cancelar",
    });

    if (confirmar.isConfirmed) {
        try {
            const res = await fetch('/empresa_celulares/recepcion/eliminarAPI', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ recepcion_id: id })
            });
            const { codigo, mensaje } = await res.json();
            if (codigo == 1) {
                Swal.fire({ icon: "success", title: "Eliminado", text: mensaje });
                BuscarRecepciones();
            } else {
                Swal.fire({ icon: "error", title: "Error", text: mensaje });
            }
        } catch (error) {
            console.error(error);
            Swal.fire({ icon: "error", title: "Error", text: "Error al eliminar" });
        }
    }
};

// Cambiar estado de recepción
const CambiarEstado = async (e) => {
    const id = e.currentTarget.dataset.id;
    
    const { value: nuevoEstado } = await Swal.fire({
        title: 'Cambiar Estado',
        input: 'select',
        inputOptions: {
            'RECIBIDO': 'Recibido',
            'EN_DIAGNOSTICO': 'En Diagnóstico',
            'ESPERANDO_REPUESTOS': 'Esperando Repuestos',
            'EN_REPARACION': 'En Reparación',
            'REPARADO': 'Reparado',
            'ENTREGADO': 'Entregado',
            'CANCELADO': 'Cancelado'
        },
        inputPlaceholder: 'Seleccione el nuevo estado',
        showCancelButton: true,
        confirmButtonText: 'Cambiar',
        cancelButtonText: 'Cancelar'
    });

    if (nuevoEstado) {
        try {
            const res = await fetch('/empresa_celulares/recepcion/cambiarEstadoAPI', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `recepcion_id=${id}&nuevo_estado=${nuevoEstado}`
            });
            const { codigo, mensaje } = await res.json();
            if (codigo == 1) {
                Swal.fire({ icon: "success", title: "Estado actualizado", text: mensaje });
                BuscarRecepciones();
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
    if (!FormularioRecepcion) {
        console.error('FormularioRecepcion no encontrado');
        return;
    }
    
    if (!BtnGuardar) {
        console.error('BtnGuardar no encontrado');
        return;
    }
    
    // Cargar datos
    cargarClientes();
    cargarEmpleados();
    BuscarRecepciones();
    
    // Event Listeners
    FormularioRecepcion.addEventListener('submit', GuardarRecepcion);
    if (BtnModificar) BtnModificar.addEventListener('click', ModificarRecepcion);
    if (BtnLimpiar) BtnLimpiar.addEventListener('click', limpiarTodo);
    
    // Event listeners para la tabla
    datatable.on('click', '.modificar', llenarFormulario);
    datatable.on('click', '.eliminar', EliminarRecepcion);
    datatable.on('click', '.cambiar-estado', CambiarEstado);
    
    console.log('Aplicación inicializada correctamente');
});