// cliente/index.js
import Swal from "sweetalert2";
import DataTable from "datatables.net-bs5";
import { validarFormulario } from "../funciones";
import { lenguaje } from "../lenguaje";

// Elementos del DOM
const FormularioClientes = document.getElementById('FormularioClientes');
const BtnGuardar = document.getElementById('BtnGuardar');
const BtnModificar = document.getElementById('BtnModificar');
const BtnLimpiar = document.getElementById('BtnLimpiar');
const cliente_nit = document.getElementById('cliente_nit');
const cliente_tel = document.getElementById('cliente_tel');
const cliente_dpi = document.getElementById('cliente_dpi');

// Validar Teléfono
const ValidarTelefono = () => {
    const numero = cliente_tel.value;
    if (numero.length === 8) {
        cliente_tel.classList.add('is-valid');
        cliente_tel.classList.remove('is-invalid');
    } else if (numero.length > 0) {
        cliente_tel.classList.add('is-invalid');
        cliente_tel.classList.remove('is-valid');
        Swal.fire({ icon: "error", title: "Teléfono inválido", text: "Debe contener exactamente 8 dígitos" });
    } else {
        cliente_tel.classList.remove('is-valid', 'is-invalid');
    }
};

    const ValidarDPI = () => {
        const numeroDPI = cliente_dpi.value.trim();
        
        // Si está vacío, quitar clases
        if (numeroDPI.length === 0) {
            cliente_dpi.classList.remove('is-valid', 'is-invalid');
            return;
        }
        
        // Validar que solo contenga números
        if (!/^\d+$/.test(numeroDPI)) {
            cliente_dpi.classList.add('is-invalid');
            cliente_dpi.classList.remove('is-valid');
            Swal.fire({ 
                icon: "error", 
                title: "DPI inválido", 
                text: "El DPI debe contener solo números" 
            });
            return;
        }
        
        // Si tiene exactamente 13 dígitos
        if (numeroDPI.length === 13) {
            cliente_dpi.classList.add('is-valid');
            cliente_dpi.classList.remove('is-invalid');
        } 
        // Si tiene cualquier otra cantidad (menos de 13 o más de 13)
        else {
            cliente_dpi.classList.add('is-invalid');
            cliente_dpi.classList.remove('is-valid');
            
            if (numeroDPI.length < 13) {
                Swal.fire({ 
                    icon: "error", 
                    title: "DPI inválido", 
                    text: `Debe contener exactamente 13 dígitos. Faltan ${13 - numeroDPI.length} dígitos.` 
                });
            } else {
                Swal.fire({ 
                    icon: "error", 
                    title: "DPI inválido", 
                    text: `Debe contener exactamente 13 dígitos. Sobran ${numeroDPI.length - 13} dígitos.` 
                });
            }
        }
    };

// Validar NIT
 const validarNit = () => {
     const nit = cliente_nit.value.trim();
     let nd, add = 0;
     if (nd = /^(\d+)-?([\dkK])$/.exec(nit)) {
         nd[2] = (nd[2].toLowerCase() === 'k') ? 10 : parseInt(nd[2], 10);
         for (let i = 0; i < nd[1].length; i++) {
             add += ((((i - nd[1].length) * -1) + 1) * parseInt(nd[1][i], 10));
         }
         return ((11 - (add % 11)) % 11) === nd[2];
 }
     return false;
};

    const EsValidoNit = () => {
        const nit = cliente_nit.value.trim();

        if (nit === '') {
            cliente_nit.classList.remove('is-valid');
            cliente_nit.classList.add('is-invalid');
            Swal.fire({ icon: "error", title: "NIT requerido", text: "Por favor complete el campo NIT" });
            return;
        }

        if (/^\d+-?[\dkK]$/.test(nit) && validarNit(nit)) {
            cliente_nit.classList.add('is-valid');
            cliente_nit.classList.remove('is-invalid');
        } else {
            cliente_nit.classList.remove('is-valid');
            cliente_nit.classList.add('is-invalid');
            Swal.fire({ icon: "error", title: "NIT inválido", text: "Por favor ingrese un NIT válido (ej. 548789-K)" });
        }
    };


// Guardar Cliente
const GuardarCliente = async (event) => {
    event.preventDefault();
    BtnGuardar.disabled = true;

    if (!validarFormulario(FormularioClientes, ['cliente_id'])) {
        Swal.fire({ icon: "info", title: "Formulario incompleto", text: "Debe completar todos los campos requeridos" });
        BtnGuardar.disabled = false;
        return;
    }

    if (cliente_nit.classList.contains('is-invalid')) {
        Swal.fire({ icon: "error", title: "NIT inválido", text: "Debe corregir el NIT antes de continuar" });
        BtnGuardar.disabled = false;
        return;
    }

    const body = new FormData(FormularioClientes);
    const url = '/empresa_celulares/cliente/guardarAPI';

    try {
        const respuesta = await fetch(url, { method: 'POST', body });
        const { codigo, mensaje } = await respuesta.json();
        if (codigo == 1) {
            Swal.fire({ icon: "success", title: "Cliente registrado", text: mensaje });
            limpiarTodo();
            BuscarClientes();
        } else {
            Swal.fire({ icon: "info", title: "Error", text: mensaje });
        }
    } catch (error) {
        console.error(error);
    }
    BtnGuardar.disabled = false;
};

// Buscar Clientes
const BuscarClientes = async () => {
    const url = '/empresa_celulares/cliente/buscarAPI';
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
const datatable = new DataTable('#TableClientes', {
    language: lenguaje,
    data: [],
    columns: [
        { title: "ID", data: "cliente_id", render: (data, type, row, meta) => meta.row + 1 },
        { 
        title: "Nombre Completo", 
        data: null, 
        render: (data, type, row) => `${row.cliente_nom1} ${row.cliente_nom2} ${row.cliente_ape1} ${row.cliente_ape2}` 
        },
        { title: "Teléfono", data: "cliente_tel" },
        { title: "Correo", data: "cliente_correo" },
        { title: "NIT", data: "cliente_nit" },
        { title: "DPI", data: "cliente_dpi" },
        { title: "Dirección", data: "cliente_direc" },
        {
            title: "Acciones", data: "cliente_id",
            render: (id, t, row) => `
                <div class="d-flex justify-content-center gap-1">
                    <button class="btn btn-warning btn-sm modificar" data-id="${id}" data-json='${JSON.stringify(row)}'>✏️</button>
                    <button class="btn btn-danger btn-sm eliminar" data-id="${id}">🗑️</button>
                </div>
            `
        }
    ]
});

const llenarFormulario = (e) => {
    const datos = JSON.parse(e.currentTarget.dataset.json);
    for (let key in datos) {
        const input = document.getElementById(key);
        if (input) input.value = datos[key];
    }
    BtnGuardar.classList.add('d-none');
    BtnModificar.classList.remove('d-none');
    window.scrollTo({ top: 0 });
};

const limpiarTodo = () => {
    FormularioClientes.reset();
    BtnGuardar.classList.remove('d-none');
    BtnModificar.classList.add('d-none');
    FormularioClientes.querySelectorAll('.is-valid, .is-invalid').forEach(el => {
        el.classList.remove('is-valid', 'is-invalid');
    });
};

const ModificarCliente = async (event) => {
    event.preventDefault();
    BtnModificar.disabled = true;

    if (!validarFormulario(FormularioClientes, ['cliente_id'])) {
        Swal.fire({ icon: "info", title: "Formulario incompleto", text: "Complete todos los campos" });
        BtnModificar.disabled = false;
        return;
    }

    if (cliente_nit.classList.contains('is-invalid')) {
        Swal.fire({ icon: "error", title: "NIT inválido", text: "Debe corregir el NIT antes de continuar" });
        BtnModificar.disabled = false;
        return;
    }

    const body = new FormData(FormularioClientes);
    const url = '/empresa_celulares/cliente/modificarAPI';

    try {
        const respuesta = await fetch(url, { method: 'POST', body });
        const { codigo, mensaje } = await respuesta.json();
        if (codigo == 1) {
            Swal.fire({ icon: "success", title: "Cliente modificado", text: mensaje });
            limpiarTodo();
            BuscarClientes();
        } else {
            Swal.fire({ icon: "error", title: "Error", text: mensaje });
        }
    } catch (error) {
        console.error(error);
    }
    BtnModificar.disabled = false;
};

const EliminarClientes = async (e) => {
    const id = e.currentTarget.dataset.id;
    const confirmar = await Swal.fire({
        icon: "warning", title: "¿Eliminar cliente?", text: "Esta acción no se puede deshacer.",
        showCancelButton: true, confirmButtonText: "Sí, eliminar", cancelButtonText: "Cancelar",
    });

    if (confirmar.isConfirmed) {
        const url = `/empresa_celulares/cliente/eliminar?id=${id}`;
        try {
            const res = await fetch(url);
            const { codigo, mensaje } = await res.json();
            if (codigo == 1) {
                Swal.fire({ icon: "success", title: "Eliminado", text: mensaje });
                BuscarClientes();
            } else {
                Swal.fire({ icon: "error", title: "Error", text: mensaje });
            }
        } catch (error) {
            console.error(error);
        }
    }
};

document.addEventListener('DOMContentLoaded', () => {
    BuscarClientes();
    cliente_tel.addEventListener('change', ValidarTelefono);
    cliente_dpi.addEventListener('change', ValidarDPI);
    cliente_nit.addEventListener('change', EsValidoNit);
    FormularioClientes.addEventListener('submit', GuardarCliente);
    BtnModificar.addEventListener('click', ModificarCliente);
    BtnLimpiar.addEventListener('click', limpiarTodo);
    datatable.on('click', '.modificar', llenarFormulario);
    datatable.on('click', '.eliminar', EliminarClientes);
});