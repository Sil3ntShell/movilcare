// tiposervicio/index.js
import Swal from "sweetalert2";
import DataTable from "datatables.net-bs5";
import { validarFormulario } from "../funciones";
import { lenguaje } from "../lenguaje";


// Elementos del DOM
const FormularioTipoServicio = document.getElementById('FormularioTipoServicio');
const BtnGuardar = document.getElementById('BtnGuardar');
const BtnModificar = document.getElementById('BtnModificar');
const BtnLimpiar = document.getElementById('BtnLimpiar');

// Función para limpiar formulario
const limpiarTodo = () => {
    FormularioTipoServicio.reset();
    BtnGuardar.classList.remove('d-none');
    BtnModificar.classList.add('d-none');
    FormularioTipoServicio.querySelectorAll('.is-valid, .is-invalid').forEach(el => {
        el.classList.remove('is-valid', 'is-invalid');
    });
};

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
    const url = '/empresa_celulares/tiposervicio/guardarAPI';

    try {
        const respuesta = await fetch(url, { method: 'POST', body });
        const { codigo, mensaje } = await respuesta.json();
        
        if (codigo == 1) {
            Swal.fire({ icon: "success", title: "Tipo de servicio registrado", text: mensaje });
            limpiarTodo();
            BuscarTiposServicio();
        } else {
            Swal.fire({ icon: "info", title: "Error", text: mensaje });
        }
    } catch (error) {
        console.error(error);
    }
    BtnGuardar.disabled = false;
};

// Buscar Tipos de Servicio
const BuscarTiposServicio = async () => {
    const url = '/empresa_celulares/tiposervicio/buscarAPI';
    try {
        const res = await fetch(url);
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
const datatable = new DataTable('#TablaTipoServicio', {
    language: lenguaje,
    data: [],
    columns: [
        { title: "ID", data: "tipo_servicio_id", render: (data, type, row, meta) => meta.row + 1 },
        { 
            title: "Nombre del Servicio", 
            data: "tipo_servicio_nombre",
            render: (data) => decodificarTexto(data)
        },
        { 
            title: "Descripción", 
            data: "tipo_servicio_descripcion",
            render: (data) => {
                if (!data || data.trim() === '') return 'Sin descripción';
                const textoDecodificado = decodificarTexto(data);
                return textoDecodificado.length > 50 ? textoDecodificado.substring(0, 50) + '...' : textoDecodificado;
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
            render: (id, type, row) => `
                <div class="d-flex justify-content-center gap-1">
                    <button class="btn btn-warning btn-sm modificar" data-id="${id}" data-json='${JSON.stringify(row)}'>✏️</button>
                    <button class="btn btn-danger btn-sm eliminar" data-id="${id}">🗑️</button>
                </div>
            `
        }
    ]
});

// Función para decodificar caracteres especiales
const decodificarTexto = (texto) => {
    if (!texto) return texto;
    const textArea = document.createElement('textarea');
    textArea.innerHTML = texto;
    return textArea.value;
};

// Llenar formulario para editar (decodificando caracteres especiales)
const llenarFormulario = (e) => {
    const datos = JSON.parse(e.currentTarget.dataset.json);
    for (let key in datos) {
        const input = document.getElementById(key);
        if (input) {
            // Decodificar el valor antes de asignarlo
            input.value = decodificarTexto(datos[key]);
        }
    }
    BtnGuardar.classList.add('d-none');
    BtnModificar.classList.remove('d-none');
    window.scrollTo({ top: 0 });
};

// Modificar Tipo de Servicio
const ModificarTipoServicio = async (event) => {
    event.preventDefault();
    BtnModificar.disabled = true;

    if (!validarFormulario(FormularioTipoServicio)) {
        Swal.fire({ icon: "info", title: "Formulario incompleto", text: "Complete todos los campos" });
        BtnModificar.disabled = false;
        return;
    }

    const body = new FormData(FormularioTipoServicio);
    const url = '/empresa_celulares/tiposervicio/modificarAPI';

    try {
        const respuesta = await fetch(url, { method: 'POST', body });
        const { codigo, mensaje } = await respuesta.json();
        
        if (codigo == 1) {
            Swal.fire({ icon: "success", title: "Tipo de servicio modificado", text: mensaje });
            limpiarTodo();
            BuscarTiposServicio();
        } else {
            Swal.fire({ icon: "error", title: "Error", text: mensaje });
        }
    } catch (error) {
        console.error(error);
        Swal.fire({ icon: "error", title: "Error", text: "Error de conexión" });
    }
    BtnModificar.disabled = false;
};

// Eliminar Tipo de Servicio
const EliminarTipoServicio = async (e) => {
    const id = e.currentTarget.dataset.id;
    const confirmar = await Swal.fire({
        icon: "warning", 
        title: "¿Eliminar tipo de servicio?", 
        text: "Esta acción no se puede deshacer.",
        showCancelButton: true, 
        confirmButtonText: "Sí, eliminar", 
        cancelButtonText: "Cancelar",
    });

    if (confirmar.isConfirmed) {
        const url = `/empresa_celulares/tiposervicio/eliminar?id=${id}`;
        try {
            const res = await fetch(url);
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

// Event listeners
document.addEventListener('DOMContentLoaded', () => {
    BuscarTiposServicio();
    FormularioTipoServicio.addEventListener('submit', GuardarTipoServicio);
    BtnModificar.addEventListener('click', ModificarTipoServicio);
    BtnLimpiar.addEventListener('click', limpiarTodo);
    datatable.on('click', '.modificar', llenarFormulario);
    datatable.on('click', '.eliminar', EliminarTipoServicio);
});