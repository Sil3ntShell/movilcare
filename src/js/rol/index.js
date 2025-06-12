// rol/index.js
import Swal from "sweetalert2";
import DataTable from "datatables.net-bs5";
import { validarFormulario } from "../funciones";
import { lenguaje } from "../lenguaje";


// Elementos del DOM
const FormularioRoles = document.getElementById('FormularioRoles');
const BtnGuardar = document.getElementById('BtnGuardar');
const BtnModificar = document.getElementById('BtnModificar');
const BtnLimpiar = document.getElementById('BtnLimpiar');
const rol_nombre = document.getElementById('rol_nombre');

// Validar Nombre de Rol
const ValidarNombreRol = () => {
    const nombre = rol_nombre.value.trim();
    if (nombre.length >= 2) {
        rol_nombre.classList.add('is-valid');
        rol_nombre.classList.remove('is-invalid');
    } else if (nombre.length > 0) {
        rol_nombre.classList.add('is-invalid');
        rol_nombre.classList.remove('is-valid');
        Swal.fire({ 
            icon: "error", 
            title: "Nombre inválido", 
            text: "El nombre debe tener al menos 2 caracteres" 
        });
    } else {
        rol_nombre.classList.remove('is-valid', 'is-invalid');
    }
};

// Guardar Rol
const GuardarRol = async (event) => {
    event.preventDefault();
    BtnGuardar.disabled = true;

    if (!validarFormulario(FormularioRoles, ['rol_id'])) {
        Swal.fire({ 
            icon: "info", 
            title: "Formulario incompleto", 
            text: "Debe completar todos los campos requeridos" 
        });
        BtnGuardar.disabled = false;
        return;
    }

    if (rol_nombre.classList.contains('is-invalid')) {
        Swal.fire({ 
            icon: "error", 
            title: "Nombre inválido", 
            text: "Debe corregir el nombre antes de continuar" 
        });
        BtnGuardar.disabled = false;
        return;
    }

    const body = new FormData(FormularioRoles);
    const url = '/empresa_celulares/rol/guardarAPI';

    try {
        const respuesta = await fetch(url, { method: 'POST', body });
        const { codigo, mensaje } = await respuesta.json();
        if (codigo == 1) {
            Swal.fire({ 
                icon: "success", 
                title: "Rol registrado", 
                text: mensaje 
            });
            limpiarTodo();
            BuscarRoles();
        } else {
            Swal.fire({ 
                icon: "info", 
                title: "Error", 
                text: mensaje 
            });
        }
    } catch (error) {
        console.error(error);
        Swal.fire({ 
            icon: "error", 
            title: "Error", 
            text: "Ocurrió un error al procesar la solicitud" 
        });
    }
    BtnGuardar.disabled = false;
};

// Buscar Roles
const BuscarRoles = async () => {
    const url = '/empresa_celulares/rol/buscarAPI';
    try {
        const res = await fetch(url);
        const { codigo, mensaje, data } = await res.json();
        if (codigo == 1) {
            datatable.clear().draw();
            datatable.rows.add(data).draw();
        } else {
            Swal.fire({ 
                icon: "info", 
                title: "Error", 
                text: mensaje 
            });
        }
    } catch (error) {
        console.error(error);
        Swal.fire({ 
            icon: "error", 
            title: "Error", 
            text: "Error al cargar los roles" 
        });
    }
};

// DataTable Configuración
const datatable = new DataTable('#TableRoles', {
    language: lenguaje,
    data: [],
    columns: [
        { 
            title: "No.", 
            data: "rol_id", 
            render: (data, type, row, meta) => meta.row + 1 
        },
        { 
            title: "Nombre", 
            data: "rol_nombre" 
        },
        { 
            title: "Descripción", 
            data: "rol_descripcion" 
        },
        { 
            title: "Fecha de Creación", 
            data: "rol_fecha_creacion",
            render: (data) => {
                if (data) {
                    const fecha = new Date(data);
                    return fecha.toLocaleDateString('es-GT');
                }
                return '';
            }
        },
        {
            title: "Acciones", 
            data: "rol_id",
            render: (id, type, row) => `
                <div class="d-flex justify-content-center gap-1">
                    <button class="btn btn-warning btn-sm modificar" 
                            data-id="${id}" 
                            data-json='${JSON.stringify(row)}'
                            title="Modificar">
                        ✏️
                    </button>
                    <button class="btn btn-danger btn-sm eliminar" 
                            data-id="${id}"
                            title="Eliminar">
                        🗑️
                    </button>
                </div>
            `
        }
    ]
});

// Llenar formulario para modificar
const llenarFormulario = (e) => {
    const datos = JSON.parse(e.currentTarget.dataset.json);
    for (let key in datos) {
        const input = document.getElementById(key);
        if (input) input.value = datos[key];
    }
    BtnGuardar.classList.add('d-none');
    BtnModificar.classList.remove('d-none');
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

// Limpiar todo el formulario
const limpiarTodo = () => {
    FormularioRoles.reset();
    BtnGuardar.classList.remove('d-none');
    BtnModificar.classList.add('d-none');
    FormularioRoles.querySelectorAll('.is-valid, .is-invalid').forEach(el => {
        el.classList.remove('is-valid', 'is-invalid');
    });
};

// Modificar Rol
const ModificarRol = async (event) => {
    event.preventDefault();
    BtnModificar.disabled = true;

    if (!validarFormulario(FormularioRoles, ['rol_id'])) {
        Swal.fire({ 
            icon: "info", 
            title: "Formulario incompleto", 
            text: "Complete todos los campos" 
        });
        BtnModificar.disabled = false;
        return;
    }

    if (rol_nombre.classList.contains('is-invalid')) {
        Swal.fire({ 
            icon: "error", 
            title: "Nombre inválido", 
            text: "Debe corregir el nombre antes de continuar" 
        });
        BtnModificar.disabled = false;
        return;
    }

    const body = new FormData(FormularioRoles);
    const url = '/empresa_celulares/rol/modificarAPI';

    try {
        const respuesta = await fetch(url, { method: 'POST', body });
        const { codigo, mensaje } = await respuesta.json();
        if (codigo == 1) {
            Swal.fire({ 
                icon: "success", 
                title: "Rol modificado", 
                text: mensaje 
            });
            limpiarTodo();
            BuscarRoles();
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
            text: "Ocurrió un error al procesar la solicitud" 
        });
    }
    BtnModificar.disabled = false;
};

// Eliminar Rol
const EliminarRol = async (e) => {
    const id = e.currentTarget.dataset.id;
    const confirmar = await Swal.fire({
        icon: "warning", 
        title: "¿Eliminar rol?", 
        text: "Esta acción no se puede deshacer. Si hay usuarios asignados a este rol, no se podrá eliminar.",
        showCancelButton: true, 
        confirmButtonText: "Sí, eliminar", 
        cancelButtonText: "Cancelar",
        confirmButtonColor: "#d33",
        cancelButtonColor: "#6c757d"
    });

    if (confirmar.isConfirmed) {
        const url = `/empresa_celulares/rol/eliminar?id=${id}`;
        try {
            const res = await fetch(url);
            const { codigo, mensaje } = await res.json();
            if (codigo == 1) {
                Swal.fire({ 
                    icon: "success", 
                    title: "Eliminado", 
                    text: mensaje 
                });
                BuscarRoles();
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
                text: "Error al eliminar el rol" 
            });
        }
    }
};

// Eventos del DOM
document.addEventListener('DOMContentLoaded', () => {
    BuscarRoles();
    rol_nombre.addEventListener('change', ValidarNombreRol);
    FormularioRoles.addEventListener('submit', GuardarRol);
    BtnModificar.addEventListener('click', ModificarRol);
    BtnLimpiar.addEventListener('click', limpiarTodo);
    datatable.on('click', '.modificar', llenarFormulario);
    datatable.on('click', '.eliminar', EliminarRol);
});