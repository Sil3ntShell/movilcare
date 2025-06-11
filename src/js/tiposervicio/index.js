import DataTable from "datatables.net-bs5";
import { validarFormulario } from '../funciones';
import Swal from "sweetalert2";
import { lenguaje } from "../lenguaje";

// Elementos del DOM
const FormularioTipoServicio = document.getElementById('FormularioTipoServicio');
const BtnGuardar = document.getElementById('BtnGuardar');
const BtnModificar = document.getElementById('BtnModificar');
const BtnLimpiar = document.getElementById('BtnLimpiar');

let tipoServicioEditando = null;
let datatable = null;

// Guardar Tipo de Servicio
const GuardarTipoServicio = async (event) => {
    event.preventDefault();
    BtnGuardar.disabled = true;

    if (!validarFormulario(FormularioTipoServicio, ['tipo_servicio_id'])) {
        Swal.fire({ icon: "info", title: "Formulario incompleto", text: "Debe completar todos los campos requeridos" });
        BtnGuardar.disabled = false;
        return;
    }

    const body = new FormData(FormularioTipoServicio);
    const url = tipoServicioEditando ? '/empresa_celulares/tiposervicio/actualizarAPI' : '/empresa_celulares/tiposervicio/guardarAPI';
    
    if (tipoServicioEditando) {
        body.append('tipo_servicio_id', tipoServicioEditando);
    }

    try {
        const respuesta = await fetch(url, { method: 'POST', body });
        const { codigo, mensaje } = await respuesta.json();
        if (codigo == 1) {
            Swal.fire({ icon: "success", title: tipoServicioEditando ? "Servicio actualizado" : "Servicio registrado", text: mensaje });
            limpiarTodo();
            BuscarTiposServicio();
        } else {
            Swal.fire({ icon: "error", title: "Error", text: mensaje });
        }
    } catch (error) {
        console.error(error);
        Swal.fire({ icon: "error", title: "Error", text: "Error de conexión" });
    }
    BtnGuardar.disabled = false;
};

// Buscar Tipos de Servicio
const BuscarTiposServicio = async () => {
    try {
        const res = await fetch('/empresa_celulares/tiposervicio/buscarAPI');
        const { codigo, mensaje, data } = await res.json();
        if (codigo == 1) {
            datatable.clear().draw();
            datatable.rows.add(data).draw();
        } else {
            Swal.fire({ icon: "info", title: "Error", text: mensaje });
        }
    } catch (error) {
        console.error(error);
    }
};

// DataTable Configuración
datatable = new DataTable('#TablaTipoServicio', {
    language: lenguaje,
    data: [],
    columns: [
        { title: "ID", data: "tipo_servicio_id", render: (data, type, row, meta) => meta.row + 1 },
        { title: "Nombre del Servicio", data: "tipo_servicio_nombre" },
        { 
            title: "Descripción", 
            data: "tipo_servicio_descripcion",
            render: (data) => {
                if (!data || data.trim() === '') return 'Sin descripción';
                return data.length > 50 ? data.substring(0, 50) + '...' : data;
            }
        },
        { 
            title: "Precio Base", 
            data: "tipo_servicio_precio_base",
            render: (data) => `Q. ${parseFloat(data).toLocaleString('es-GT', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`,
            className: 'text-end'
        },
        { 
            title: "Tiempo Estimado", 
            data: "tipo_servicio_tiempo_estimado",
            render: (data) => {
                const minutos = parseInt(data);
                if (minutos < 60) {
                    return minutos + ' min';
                } else if (minutos < 1440) {
                    const horas = Math.floor(minutos / 60);
                    const mins = minutos % 60;
                    return horas + 'h' + (mins > 0 ? ' ' + mins + 'm' : '');
                } else {
                    const dias = Math.floor(minutos / 1440);
                    const horasRestantes = Math.floor((minutos % 1440) / 60);
                    return dias + ' días' + (horasRestantes > 0 ? ' ' + horasRestantes + 'h' : '');
                }
            },
            className: 'text-center'
        },
        {
            title: "Acciones", 
            data: "tipo_servicio_id",
            render: (id, t, row) => `
                <div class="d-flex justify-content-center gap-1">
                    <button class="btn btn-warning btn-sm modificar" data-id="${id}" data-json='${JSON.stringify(row)}'>✏️</button>
                    <button class="btn btn-danger btn-sm eliminar" data-id="${id}">🗑️</button>
                </div>
            `,
            orderable: false,
            className: 'text-center'
        }
    ]
});

// Llenar formulario para editar
const llenarFormulario = (e) => {
    const datos = JSON.parse(e.currentTarget.dataset.json);
    
    document.getElementById('tipo_servicio_nombre').value = datos.tipo_servicio_nombre;
    document.getElementById('tipo_servicio_descripcion').value = datos.tipo_servicio_descripcion || '';
    document.getElementById('tipo_servicio_precio_base').value = datos.tipo_servicio_precio_base;
    document.getElementById('tipo_servicio_tiempo_estimado').value = datos.tipo_servicio_tiempo_estimado;

    tipoServicioEditando = datos.tipo_servicio_id;
    BtnGuardar.textContent = 'Actualizar Servicio';
    BtnGuardar.className = 'btn btn-warning px-4';
    BtnGuardar.classList.remove('d-none');
    BtnModificar.classList.add('d-none');
    
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

// Limpiar todo
const limpiarTodo = () => {
    FormularioTipoServicio.reset();
    tipoServicioEditando = null;
    BtnGuardar.textContent = 'Guardar';
    BtnGuardar.className = 'btn btn-success px-4';
    BtnGuardar.classList.remove('d-none');
    BtnModificar.classList.add('d-none');
    
    FormularioTipoServicio.querySelectorAll('.is-valid, .is-invalid').forEach(el => {
        el.classList.remove('is-valid', 'is-invalid');
    });
};

// Eliminar tipo de servicio
const EliminarTipoServicio = async (e) => {
    const id = e.currentTarget.dataset.id;
    const confirmar = await Swal.fire({
        icon: "warning", 
        title: "¿Eliminar tipo de servicio?", 
        text: "Esta acción desactivará el tipo de servicio.",
        showCancelButton: true, 
        confirmButtonText: "Sí, eliminar", 
        cancelButtonText: "Cancelar",
    });

    if (confirmar.isConfirmed) {
        try {
            const res = await fetch('/empresa_celulares/tiposervicio/eliminarAPI', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ tipo_servicio_id: id })
            });
            const { codigo, mensaje } = await res.json();
            if (codigo == 1) {
                Swal.fire({ icon: "success", title: "Eliminado", text: mensaje });
                BuscarTiposServicio();
            } else {
                Swal.fire({ icon: "error", title: "Error", text: mensaje });
            }
        } catch (error) {
            console.error(error);
        }
    }
};

// Llenar formulario con ejemplos
const cargarEjemplo = (e) => {
    const btn = e.currentTarget;
    const nombre = btn.dataset.nombre;
    const precio = btn.dataset.precio;
    const tiempo = btn.dataset.tiempo;
    
    document.getElementById('tipo_servicio_nombre').value = nombre;
    document.getElementById('tipo_servicio_precio_base').value = precio;
    document.getElementById('tipo_servicio_tiempo_estimado').value = tiempo;
    
    // Descripción automática según el servicio
    const descripciones = {
        'Cambio de Pantalla': 'Reemplazo completo de pantalla LCD/OLED incluyendo touch y marco. Incluye limpieza interna y calibración.',
        'Cambio de Batería': 'Reemplazo de batería original. Incluye pruebas de carga y calibración del sistema.',
        'Reparación de Placa': 'Diagnóstico y reparación a nivel de componentes. Incluye soldadura, reballing y pruebas.',
        'Liberación de Equipo': 'Liberación por software para todas las compañías. Incluye respaldo de datos.',
        'Diagnóstico General': 'Evaluación completa del equipo para identificar fallas. Incluye reporte detallado.'
    };
    
    if (descripciones[nombre]) {
        document.getElementById('tipo_servicio_descripcion').value = descripciones[nombre];
    }
    
    // Efecto visual
    btn.classList.add('btn-info');
    btn.classList.remove('btn-outline-info');
    setTimeout(() => {
        btn.classList.remove('btn-info');
        btn.classList.add('btn-outline-info');
    }, 300);
};

// Event Listeners
document.addEventListener('DOMContentLoaded', () => {
    BuscarTiposServicio();
    
    if (FormularioTipoServicio) FormularioTipoServicio.addEventListener('submit', GuardarTipoServicio);
    if (BtnLimpiar) BtnLimpiar.addEventListener('click', limpiarTodo);
    
    // Event listeners para ejemplos
    document.querySelectorAll('.ejemplo-servicio').forEach(btn => {
        btn.addEventListener('click', cargarEjemplo);
    });
    
    datatable.on('click', '.modificar', llenarFormulario);
    datatable.on('click', '.eliminar', EliminarTipoServicio);
});