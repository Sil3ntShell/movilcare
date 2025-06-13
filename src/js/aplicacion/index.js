// aplicacion/index.js
import Swal from "sweetalert2";
import DataTable from "datatables.net-bs5";
import { validarFormulario } from "../funciones";
import { lenguaje } from "../lenguaje";

// Elementos del DOM
const FormularioAplicaciones = document.getElementById('FormularioAplicaciones');
const BtnGuardar = document.getElementById('BtnGuardar');
const BtnModificar = document.getElementById('BtnModificar');
const BtnLimpiar = document.getElementById('BtnLimpiar');
const app_nombre_corto = document.getElementById('app_nombre_corto');

// Validar Nombre Corto (solo letras, números y guiones)
const ValidarNombreCorto = () => {
    const nombreCorto = app_nombre_corto.value.trim().toUpperCase();
    
    if (nombreCorto.length < 1) {
        app_nombre_corto.classList.remove('is-valid', 'is-invalid');
        return true;
    }
    
    // Solo letras, números y guiones bajos
    if (!/^[A-Z0-9_]+$/.test(nombreCorto)) {
        app_nombre_corto.classList.add('is-invalid');
        app_nombre_corto.classList.remove('is-valid');
        Swal.fire({ 
            icon: "error", 
            title: "Nombre corto inválido", 
            text: "Solo se permiten letras mayúsculas, números y guiones bajos (_)" 
        });
        return false;
    }
    
    if (nombreCorto.length < 2) {
        app_nombre_corto.classList.add('is-invalid');
        app_nombre_corto.classList.remove('is-valid');
        Swal.fire({ 
            icon: "error", 
            title: "Nombre corto muy corto", 
            text: "Debe tener al menos 2 caracteres" 
        });
        return false;
    }
    
    app_nombre_corto.classList.remove('is-invalid');
    app_nombre_corto.classList.add('is-valid');
    app_nombre_corto.value = nombreCorto; // Convertir a mayúsculas automáticamente
    return true;
};

// Guardar Aplicación
const GuardarAplicacion = async (event) => {
    event.preventDefault();
    BtnGuardar.disabled = true;

    if (!validarFormulario(FormularioAplicaciones, ['app_id'])) {
        Swal.fire({ icon: "info", title: "Formulario incompleto", text: "Debe completar todos los campos requeridos" });
        BtnGuardar.disabled = false;
        return;
    }

    if (!ValidarNombreCorto()) {
        Swal.fire({ icon: "error", title: "Nombre corto inválido", text: "Debe corregir el nombre corto antes de continuar" });
        BtnGuardar.disabled = false;
        return;
    }

    const body = new FormData(FormularioAplicaciones);
    const url = '/empresa_celulares/aplicacion/guardarAPI';

    try {
        const respuesta = await fetch(url, { method: 'POST', body });
        const { codigo, mensaje } = await respuesta.json();
        if (codigo == 1) {
            Swal.fire({ icon: "success", title: "Aplicación registrada", text: mensaje });
            limpiarTodo();
            BuscarAplicaciones();
        } else {
            Swal.fire({ icon: "info", title: "Error", text: mensaje });
        }
    } catch (error) {
        console.error(error);
        Swal.fire({ icon: "error", title: "Error de conexión", text: "No se pudo completar la operación" });
    }
    BtnGuardar.disabled = false;
};

// Buscar Aplicaciones
const BuscarAplicaciones = async () => {
    const url = '/empresa_celulares/aplicacion/buscarAPI';
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
        Swal.fire({ icon: "error", title: "Error de conexión", text: "No se pudieron cargar las aplicaciones" });
    }
};

// DataTable Configuración
const datatable = new DataTable('#TableAplicaciones', {
    language: lenguaje,
    data: [],
    columns: [
        { 
            title: "ID", 
            data: "app_id", 
            render: (data, type, row, meta) => meta.row + 1,
            width: '8%'
        },
        { 
            title: "Nombre Corto", 
            data: "app_nombre_corto",
            render: (data) => `<span class="badge bg-primary fs-6">${data}</span>`,
            width: '15%'
        },
        { 
            title: "Nombre Mediano", 
            data: "app_nombre_medium",
            width: '25%'
        },
        { 
            title: "Nombre Largo", 
            data: "app_nombre_largo",
            width: '30%'
        },
        { 
            title: "Fecha Creación", 
            data: "app_fecha_creacion",
            render: (data) => {
                if (!data) return 'N/A';
                const fecha = new Date(data);
                return fecha.toLocaleDateString('es-GT');
            },
            className: 'text-center',
            width: '12%'
        },
        {
            title: "Acciones", 
            data: "app_id",
            render: (id, t, row) => `
                <div class="d-flex justify-content-center gap-1">
                    <button class="btn btn-warning btn-sm modificar" data-id="${id}" data-json='${JSON.stringify(row)}' title="Modificar">
                        <i class="bi bi-pencil-square"></i>
                    </button>
                    <button class="btn btn-danger btn-sm eliminar" data-id="${id}" title="Eliminar">
                        <i class="bi bi-trash3"></i>
                    </button>
                </div>
            `,
            orderable: false,
            searchable: false,
            width: '10%'
        }
    ]
});

// Llenar formulario para modificar
const llenarFormulario = (e) => {
    const datos = JSON.parse(e.currentTarget.dataset.json);
    
    document.getElementById('app_id').value = datos.app_id;
    document.getElementById('app_nombre_largo').value = datos.app_nombre_largo;
    document.getElementById('app_nombre_medium').value = datos.app_nombre_medium;
    document.getElementById('app_nombre_corto').value = datos.app_nombre_corto;
    
    BtnGuardar.classList.add('d-none');
    BtnModificar.classList.remove('d-none');
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

// Limpiar formulario
const limpiarTodo = () => {
    FormularioAplicaciones.reset();
    BtnGuardar.classList.remove('d-none');
    BtnModificar.classList.add('d-none');
    FormularioAplicaciones.querySelectorAll('.is-valid, .is-invalid').forEach(el => {
        el.classList.remove('is-valid', 'is-invalid');
    });
};

// Modificar Aplicación
const ModificarAplicacion = async (event) => {
    event.preventDefault();
    BtnModificar.disabled = true;

    if (!validarFormulario(FormularioAplicaciones, ['app_id'])) {
        Swal.fire({ icon: "info", title: "Formulario incompleto", text: "Complete todos los campos" });
        BtnModificar.disabled = false;
        return;
    }

    if (!ValidarNombreCorto()) {
        Swal.fire({ icon: "error", title: "Nombre corto inválido", text: "Debe corregir el nombre corto antes de continuar" });
        BtnModificar.disabled = false;
        return;
    }

    const body = new FormData(FormularioAplicaciones);
    const url = '/empresa_celulares/aplicacion/modificarAPI';

    try {
        const respuesta = await fetch(url, { method: 'POST', body });
        const { codigo, mensaje } = await respuesta.json();
        if (codigo == 1) {
            Swal.fire({ icon: "success", title: "Aplicación modificada", text: mensaje });
            limpiarTodo();
            BuscarAplicaciones();
        } else {
            Swal.fire({ icon: "error", title: "Error", text: mensaje });
        }
    } catch (error) {
        console.error(error);
        Swal.fire({ icon: "error", title: "Error de conexión", text: "No se pudo completar la modificación" });
    }
    BtnModificar.disabled = false;
};

// Eliminar Aplicación
const EliminarAplicacion = async (e) => {
    const id = e.currentTarget.dataset.id;
    const confirmar = await Swal.fire({
        icon: "warning", 
        title: "¿Eliminar aplicación?", 
        text: "Esta acción no se puede deshacer.",
        showCancelButton: true, 
        confirmButtonText: "Sí, eliminar", 
        cancelButtonText: "Cancelar",
        confirmButtonColor: '#d33'
    });

    if (confirmar.isConfirmed) {
        const url = `/empresa_celulares/aplicacion/eliminar?id=${id}`;
        try {
            const res = await fetch(url);
            const { codigo, mensaje } = await res.json();
            if (codigo == 1) {
                Swal.fire({ icon: "success", title: "Eliminada", text: mensaje });
                BuscarAplicaciones();
            } else {
                Swal.fire({ icon: "error", title: "Error", text: mensaje });
            }
        } catch (error) {
            console.error(error);
            Swal.fire({ icon: "error", title: "Error de conexión", text: "No se pudo eliminar la aplicación" });
        }
    }
};

document.addEventListener('DOMContentLoaded', () => {
    BuscarAplicaciones();
    
    // Validaciones en tiempo real
    if (app_nombre_corto) {
        app_nombre_corto.addEventListener('input', (e) => {
            e.target.value = e.target.value.toUpperCase();
        });
        app_nombre_corto.addEventListener('change', ValidarNombreCorto);
    }
    
    // Eventos de formulario
    FormularioAplicaciones.addEventListener('submit', GuardarAplicacion);
    BtnModificar.addEventListener('click', ModificarAplicacion);
    BtnLimpiar.addEventListener('click', limpiarTodo);
    
    // Eventos de DataTable
    datatable.on('click', '.modificar', llenarFormulario);
    datatable.on('click', '.eliminar', EliminarAplicacion);
    
    console.log('Aplicación de aplicaciones inicializada correctamente');
});