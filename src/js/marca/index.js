// marca/index.js
import Swal from "sweetalert2";
import DataTable from "datatables.net-bs5";
import { validarFormulario } from "../funciones";
import { lenguaje } from "../lenguaje";

// Elementos del DOM
const FormularioMarcas = document.getElementById('FormularioMarcas');
const BtnGuardar = document.getElementById('BtnGuardar');
const BtnModificar = document.getElementById('BtnModificar');
const BtnLimpiar = document.getElementById('BtnLimpiar');
const marca_nombre = document.getElementById('marca_nombre');

// Validar Nombre de Marca
const ValidarNombreMarca = () => {
    const nombre = marca_nombre.value.trim();
    if (nombre.length >= 2) {
        marca_nombre.classList.add('is-valid');
        marca_nombre.classList.remove('is-invalid');
    } else if (nombre.length > 0) {
        marca_nombre.classList.add('is-invalid');
        marca_nombre.classList.remove('is-valid');
        Swal.fire({ 
            icon: "error", 
            title: "Nombre inválido", 
            text: "El nombre debe tener al menos 2 caracteres" 
        });
    } else {
        marca_nombre.classList.remove('is-valid', 'is-invalid');
    }
};

// Guardar Marca
const GuardarMarca = async (event) => {
    event.preventDefault();
    BtnGuardar.disabled = true;

    if (!validarFormulario(FormularioMarcas, ['marca_id'])) {
        Swal.fire({ 
            icon: "info", 
            title: "Formulario incompleto", 
            text: "Debe completar todos los campos requeridos" 
        });
        BtnGuardar.disabled = false;
        return;
    }

    if (marca_nombre.classList.contains('is-invalid')) {
        Swal.fire({ 
            icon: "error", 
            title: "Nombre inválido", 
            text: "Debe corregir el nombre antes de continuar" 
        });
        BtnGuardar.disabled = false;
        return;
    }

    const body = new FormData(FormularioMarcas);
    const url = '/empresa_celulares/marca/guardarAPI';

    try {
        const respuesta = await fetch(url, { method: 'POST', body });
        const { codigo, mensaje } = await respuesta.json();
        if (codigo == 1) {
            Swal.fire({ 
                icon: "success", 
                title: "Marca registrada", 
                text: mensaje 
            });
            limpiarTodo();
            BuscarMarcas();
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

// Buscar Marcas
const BuscarMarcas = async () => {
    const url = '/empresa_celulares/marca/buscarAPI';
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
            text: "Error al cargar las marcas" 
        });
    }
};

// DataTable Configuración
const datatable = new DataTable('#TableMarcas', {
    language: lenguaje,
    data: [],
    columns: [
        { 
            title: "No.", 
            data: "marca_id", 
            render: (data, type, row, meta) => meta.row + 1 
        },
        { 
            title: "Nombre", 
            data: "marca_nombre" 
        },
        { 
            title: "Descripción", 
            data: "marca_descripcion" 
        },
        { 
            title: "Fecha de Creación", 
            data: "marca_fecha_creacion",
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
            data: "marca_id",
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
    FormularioMarcas.reset();
    BtnGuardar.classList.remove('d-none');
    BtnModificar.classList.add('d-none');
    FormularioMarcas.querySelectorAll('.is-valid, .is-invalid').forEach(el => {
        el.classList.remove('is-valid', 'is-invalid');
    });
};

// Modificar Marca
const ModificarMarca = async (event) => {
    event.preventDefault();
    BtnModificar.disabled = true;

    if (!validarFormulario(FormularioMarcas, ['marca_id'])) {
        Swal.fire({ 
            icon: "info", 
            title: "Formulario incompleto", 
            text: "Complete todos los campos" 
        });
        BtnModificar.disabled = false;
        return;
    }

    if (marca_nombre.classList.contains('is-invalid')) {
        Swal.fire({ 
            icon: "error", 
            title: "Nombre inválido", 
            text: "Debe corregir el nombre antes de continuar" 
        });
        BtnModificar.disabled = false;
        return;
    }

    const body = new FormData(FormularioMarcas);
    const url = '/empresa_celulares/marca/modificarAPI';

    try {
        const respuesta = await fetch(url, { method: 'POST', body });
        const { codigo, mensaje } = await respuesta.json();
        if (codigo == 1) {
            Swal.fire({ 
                icon: "success", 
                title: "Marca modificada", 
                text: mensaje 
            });
            limpiarTodo();
            BuscarMarcas();
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

// Eliminar Marca
const EliminarMarca = async (e) => {
    const id = e.currentTarget.dataset.id;
    const confirmar = await Swal.fire({
        icon: "warning", 
        title: "¿Eliminar marca?", 
        text: "Esta acción no se puede deshacer.",
        showCancelButton: true, 
        confirmButtonText: "Sí, eliminar", 
        cancelButtonText: "Cancelar",
        confirmButtonColor: "#d33",
        cancelButtonColor: "#6c757d"
    });

    if (confirmar.isConfirmed) {
        const url = `/empresa_celulares/marca/eliminar?id=${id}`;
        try {
            const res = await fetch(url);
            const { codigo, mensaje } = await res.json();
            if (codigo == 1) {
                Swal.fire({ 
                    icon: "success", 
                    title: "Eliminado", 
                    text: mensaje 
                });
                BuscarMarcas();
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
                text: "Error al eliminar la marca" 
            });
        }
    }
};

// Eventos del DOM
document.addEventListener('DOMContentLoaded', () => {
    BuscarMarcas();
    marca_nombre.addEventListener('change', ValidarNombreMarca);
    FormularioMarcas.addEventListener('submit', GuardarMarca);
    BtnModificar.addEventListener('click', ModificarMarca);
    BtnLimpiar.addEventListener('click', limpiarTodo);
    datatable.on('click', '.modificar', llenarFormulario);
    datatable.on('click', '.eliminar', EliminarMarca);
});