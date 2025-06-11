// modelo/index.js
import Swal from "sweetalert2";
import DataTable from "datatables.net-bs5";
import { validarFormulario } from "../funciones";
import { lenguaje } from "../lenguaje";

// Elementos del DOM
const FormularioModelos = document.getElementById('FormularioModelos');
const BtnGuardar = document.getElementById('BtnGuardar');
const BtnModificar = document.getElementById('BtnModificar');
const BtnLimpiar = document.getElementById('BtnLimpiar');
const marca_id = document.getElementById('marca_id');
const modelo_nombre = document.getElementById('modelo_nombre');

// Validar Nombre de Modelo
const ValidarNombreModelo = () => {
    const nombre = modelo_nombre.value.trim();
    if (nombre.length >= 2) {
        modelo_nombre.classList.add('is-valid');
        modelo_nombre.classList.remove('is-invalid');
    } else if (nombre.length > 0) {
        modelo_nombre.classList.add('is-invalid');
        modelo_nombre.classList.remove('is-valid');
        Swal.fire({ 
            icon: "error", 
            title: "Nombre inválido", 
            text: "El nombre debe tener al menos 2 caracteres" 
        });
    } else {
        modelo_nombre.classList.remove('is-valid', 'is-invalid');
    }
};

// Validar Marca seleccionada
const ValidarMarca = () => {
    if (marca_id.value && marca_id.value !== '') {
        marca_id.classList.add('is-valid');
        marca_id.classList.remove('is-invalid');
    } else {
        marca_id.classList.add('is-invalid');
        marca_id.classList.remove('is-valid');
        Swal.fire({ 
            icon: "error", 
            title: "Marca requerida", 
            text: "Debe seleccionar una marca" 
        });
    }
};

// Cargar Marcas en el select
const CargarMarcas = async () => {
    const url = '/empresa_celulares/modelo/obtenerMarcasAPI';
    try {
        const res = await fetch(url);
        const { codigo, data } = await res.json();
        if (codigo == 1) {
            marca_id.innerHTML = '<option value="">Seleccione una marca...</option>';
            data.forEach(marca => {
                marca_id.innerHTML += `<option value="${marca.marca_id}">${marca.marca_nombre}</option>`;
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

// Guardar Modelo
const GuardarModelo = async (event) => {
    event.preventDefault();
    BtnGuardar.disabled = true;

    if (!validarFormulario(FormularioModelos, ['modelo_id'])) {
        Swal.fire({ 
            icon: "info", 
            title: "Formulario incompleto", 
            text: "Debe completar todos los campos requeridos" 
        });
        BtnGuardar.disabled = false;
        return;
    }

    if (modelo_nombre.classList.contains('is-invalid') || marca_id.classList.contains('is-invalid')) {
        Swal.fire({ 
            icon: "error", 
            title: "Datos inválidos", 
            text: "Debe corregir los errores antes de continuar" 
        });
        BtnGuardar.disabled = false;
        return;
    }

    const body = new FormData(FormularioModelos);
    const url = '/empresa_celulares/modelo/guardarAPI';

    try {
        const respuesta = await fetch(url, { method: 'POST', body });
        const { codigo, mensaje } = await respuesta.json();
        if (codigo == 1) {
            Swal.fire({ 
                icon: "success", 
                title: "Modelo registrado", 
                text: mensaje 
            });
            limpiarTodo();
            BuscarModelos();
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

// Buscar Modelos
const BuscarModelos = async () => {
    const url = '/empresa_celulares/modelo/buscarAPI';
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
            text: "Error al cargar los modelos" 
        });
    }
};

// DataTable Configuración
const datatable = new DataTable('#TableModelos', {
    language: lenguaje,
    data: [],
    columns: [
        { 
            title: "No.", 
            data: "modelo_id", 
            render: (data, type, row, meta) => meta.row + 1 
        },
        { 
            title: "Marca", 
            data: "marca_nombre" 
        },
        { 
            title: "Modelo", 
            data: "modelo_nombre" 
        },
        { 
            title: "Descripción", 
            data: "modelo_descripcion",
            render: (data) => {
                if (data && data.length > 50) {
                    return data.substring(0, 50) + '...';
                }
                return data || '';
            }
        },
        { 
            title: "Fecha de Creación", 
            data: "modelo_fecha_creacion",
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
            data: "modelo_id",
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
    FormularioModelos.reset();
    BtnGuardar.classList.remove('d-none');
    BtnModificar.classList.add('d-none');
    FormularioModelos.querySelectorAll('.is-valid, .is-invalid').forEach(el => {
        el.classList.remove('is-valid', 'is-invalid');
    });
};

// Modificar Modelo
const ModificarModelo = async (event) => {
    event.preventDefault();
    BtnModificar.disabled = true;

    if (!validarFormulario(FormularioModelos, ['modelo_id'])) {
        Swal.fire({ 
            icon: "info", 
            title: "Formulario incompleto", 
            text: "Complete todos los campos" 
        });
        BtnModificar.disabled = false;
        return;
    }

    if (modelo_nombre.classList.contains('is-invalid') || marca_id.classList.contains('is-invalid')) {
        Swal.fire({ 
            icon: "error", 
            title: "Datos inválidos", 
            text: "Debe corregir los errores antes de continuar" 
        });
        BtnModificar.disabled = false;
        return;
    }

    const body = new FormData(FormularioModelos);
    const url = '/empresa_celulares/modelo/modificarAPI';

    try {
        const respuesta = await fetch(url, { method: 'POST', body });
        const { codigo, mensaje } = await respuesta.json();
        if (codigo == 1) {
            Swal.fire({ 
                icon: "success", 
                title: "Modelo modificado", 
                text: mensaje 
            });
            limpiarTodo();
            BuscarModelos();
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

// Eliminar Modelo
const EliminarModelo = async (e) => {
    const id = e.currentTarget.dataset.id;
    const confirmar = await Swal.fire({
        icon: "warning", 
        title: "¿Eliminar modelo?", 
        text: "Esta acción no se puede deshacer.",
        showCancelButton: true, 
        confirmButtonText: "Sí, eliminar", 
        cancelButtonText: "Cancelar",
        confirmButtonColor: "#d33",
        cancelButtonColor: "#6c757d"
    });

    if (confirmar.isConfirmed) {
        const url = `/empresa_celulares/modelo/eliminar?id=${id}`;
        try {
            const res = await fetch(url);
            const { codigo, mensaje } = await res.json();
            if (codigo == 1) {
                Swal.fire({ 
                    icon: "success", 
                    title: "Eliminado", 
                    text: mensaje 
                });
                BuscarModelos();
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
                text: "Error al eliminar el modelo" 
            });
        }
    }
};

// Eventos del DOM
document.addEventListener('DOMContentLoaded', () => {
    CargarMarcas();
    BuscarModelos();
    modelo_nombre.addEventListener('change', ValidarNombreModelo);
    marca_id.addEventListener('change', ValidarMarca);
    FormularioModelos.addEventListener('submit', GuardarModelo);
    BtnModificar.addEventListener('click', ModificarModelo);
    BtnLimpiar.addEventListener('click', limpiarTodo);
    datatable.on('click', '.modificar', llenarFormulario);
    datatable.on('click', '.eliminar', EliminarModelo);
});