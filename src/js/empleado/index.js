import DataTable from "datatables.net-bs5";
import { validarFormulario } from '../funciones';
import Swal from "sweetalert2";
import { lenguaje } from "../lenguaje";

// Elementos del DOM
const FormularioEmpleados = document.getElementById('FormularioEmpleados');
const BtnGuardar = document.getElementById('BtnGuardar');
const BtnModificar = document.getElementById('BtnModificar');
const BtnLimpiar = document.getElementById('BtnLimpiar');
const empleado_tel = document.getElementById('empleado_tel');
const empleado_dpi = document.getElementById('empleado_dpi');

let empleadoEditando = null;
let datatable = null;

// Validar Teléfono
const ValidarTelefono = () => {
    const numero = empleado_tel.value;
    if (numero.length === 8) {
        empleado_tel.classList.add('is-valid');
        empleado_tel.classList.remove('is-invalid');
    } else if (numero.length > 0) {
        empleado_tel.classList.add('is-invalid');
        empleado_tel.classList.remove('is-valid');
        Swal.fire({ icon: "error", title: "Teléfono inválido", text: "Debe contener exactamente 8 dígitos" });
    } else {
        empleado_tel.classList.remove('is-valid', 'is-invalid');
    }
};

// Validar DPI
const ValidarDPI = () => {
    const numeroDPI = empleado_dpi.value.trim();
    
    if (numeroDPI.length === 0) {
        empleado_dpi.classList.remove('is-valid', 'is-invalid');
        return;
    }
    
    if (!/^\d+$/.test(numeroDPI)) {
        empleado_dpi.classList.add('is-invalid');
        empleado_dpi.classList.remove('is-valid');
        Swal.fire({ 
            icon: "error", 
            title: "DPI inválido", 
            text: "El DPI debe contener solo números" 
        });
        return;
    }
    
    if (numeroDPI.length === 13) {
        empleado_dpi.classList.add('is-valid');
        empleado_dpi.classList.remove('is-invalid');
    } else {
        empleado_dpi.classList.add('is-invalid');
        empleado_dpi.classList.remove('is-valid');
        
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

// Cargar usuarios para el select
const cargarUsuarios = async () => {
    try {
        const respuesta = await fetch('/empresa_celulares/usuario/buscarAPI');
        const data = await respuesta.json();
        
        const selectUsuario = document.getElementById('usuario_id');
        selectUsuario.innerHTML = '<option value="">Sin usuario asignado</option>';
        
        if (data.codigo === 1 && data.data) {
            data.data.forEach(usuario => {
                const nombreCompleto = `${usuario.usuario_nom1} ${usuario.usuario_nom2 || ''} ${usuario.usuario_ape1} ${usuario.usuario_ape2 || ''}`.trim();
                const option = document.createElement('option');
                option.value = usuario.usuario_id;
                option.textContent = `${nombreCompleto} (${usuario.usuario_correo})`;
                selectUsuario.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Error cargando usuarios:', error);
    }
};

// Guardar Empleado
const GuardarEmpleado = async (event) => {
    event.preventDefault();
    BtnGuardar.disabled = true;

    if (!validarFormulario(FormularioEmpleados, ['empleado_id'])) {
        Swal.fire({ icon: "info", title: "Formulario incompleto", text: "Debe completar todos los campos requeridos" });
        BtnGuardar.disabled = false;
        return;
    }

    if (empleado_dpi.classList.contains('is-invalid')) {
        Swal.fire({ icon: "error", title: "DPI inválido", text: "Debe corregir el DPI antes de continuar" });
        BtnGuardar.disabled = false;
        return;
    }

    if (empleado_tel.classList.contains('is-invalid')) {
        Swal.fire({ icon: "error", title: "Teléfono inválido", text: "Debe corregir el teléfono antes de continuar" });
        BtnGuardar.disabled = false;
        return;
    }

    const body = new FormData(FormularioEmpleados);
    const url = empleadoEditando ? '/empresa_celulares/empleado/actualizarAPI' : '/empresa_celulares/empleado/guardarAPI';
    
    if (empleadoEditando) {
        body.append('empleado_id', empleadoEditando);
    }

    try {
        const respuesta = await fetch(url, { method: 'POST', body });
        const { codigo, mensaje } = await respuesta.json();
        if (codigo == 1) {
            Swal.fire({ icon: "success", title: empleadoEditando ? "Empleado actualizado" : "Empleado registrado", text: mensaje });
            limpiarTodo();
            BuscarEmpleados();
        } else {
            Swal.fire({ icon: "error", title: "Error", text: mensaje });
        }
    } catch (error) {
        console.error(error);
        Swal.fire({ icon: "error", title: "Error", text: "Error de conexión" });
    }
    BtnGuardar.disabled = false;
};

// Buscar Empleados
const BuscarEmpleados = async () => {
    try {
        const res = await fetch('/empresa_celulares/empleado/buscarAPI');
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
datatable = new DataTable('#TablaEmpleados', {
    language: lenguaje,
    data: [],
    columns: [
        { title: "ID", data: "empleado_id", render: (data, type, row, meta) => meta.row + 1 },
        {
            title: "Foto",
            data: "usuario_fotografia",
            render: (data, type, row) => {
                if (data) {
                    return `<img src="/empresa_celulares/storage/fotos_usuarios/${data}" 
                           style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;" 
                           onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCA0MCA0MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPGNpcmNsZSBjeD0iMjAiIGN5PSIyMCIgcj0iMjAiIGZpbGw9IiM2Yzc1N2QiLz4KPGNpcmNsZSBjeD0iMjAiIGN5PSIxNiIgcj0iNCIgZmlsbD0iI2ZmZmZmZiIvPgo8cGF0aCBkPSJNMjggMjhBOCA4IDAgMCAwIDEyIDI4IiBmaWxsPSIjZmZmZmZmIi8+Cjwvc3ZnPg==';"/>`;
                } else {
                    return `<span class="badge bg-secondary rounded-circle" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; font-size: 8px;">Sin foto</span>`;
                }
            },
            orderable: false,
            className: 'text-center'
        },
        { 
            title: "Nombre Completo", 
            data: null, 
            render: (data, type, row) => `${row.empleado_nom1} ${row.empleado_nom2 || ''} ${row.empleado_ape1} ${row.empleado_ape2 || ''}`.trim()
        },
        { title: "DPI", data: "empleado_dpi" },
        { title: "Teléfono", data: "empleado_tel" },
        { title: "Correo", data: "empleado_correo" },
        { title: "Especialidad", data: "empleado_especialidad" },
        { 
            title: "Salario", 
            data: "empleado_salario",
            render: (data) => `Q. ${parseFloat(data).toLocaleString('es-GT', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`
        },
        { 
            title: "Fecha Contratación", 
            data: "empleado_fecha_contratacion",
            render: (data) => data ? new Date(data).toLocaleDateString('es-GT') : 'N/A'
        },
        {
            title: "Acciones", 
            data: "empleado_id",
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
    
    document.getElementById('empleado_nom1').value = datos.empleado_nom1;
    document.getElementById('empleado_nom2').value = datos.empleado_nom2 || '';
    document.getElementById('empleado_ape1').value = datos.empleado_ape1;
    document.getElementById('empleado_ape2').value = datos.empleado_ape2 || '';
    document.getElementById('empleado_tel').value = datos.empleado_tel;
    document.getElementById('empleado_dpi').value = datos.empleado_dpi;
    document.getElementById('empleado_correo').value = datos.empleado_correo;
    document.getElementById('empleado_especialidad').value = datos.empleado_especialidad;
    document.getElementById('empleado_salario').value = datos.empleado_salario;
    document.getElementById('usuario_id').value = datos.usuario_id || '';

    empleadoEditando = datos.empleado_id;
    BtnGuardar.textContent = 'Actualizar Empleado';
    BtnGuardar.className = 'btn btn-warning px-4';
    BtnGuardar.classList.remove('d-none');
    BtnModificar.classList.add('d-none');
    
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

// Limpiar todo
const limpiarTodo = () => {
    FormularioEmpleados.reset();
    empleadoEditando = null;
    BtnGuardar.textContent = 'Guardar';
    BtnGuardar.className = 'btn btn-success px-4';
    BtnGuardar.classList.remove('d-none');
    BtnModificar.classList.add('d-none');
    
    FormularioEmpleados.querySelectorAll('.is-valid, .is-invalid').forEach(el => {
        el.classList.remove('is-valid', 'is-invalid');
    });
};

// Eliminar empleado
const EliminarEmpleado = async (e) => {
    const id = e.currentTarget.dataset.id;
    const confirmar = await Swal.fire({
        icon: "warning", 
        title: "¿Eliminar empleado?", 
        text: "Esta acción desactivará el empleado.",
        showCancelButton: true, 
        confirmButtonText: "Sí, eliminar", 
        cancelButtonText: "Cancelar",
    });

    if (confirmar.isConfirmed) {
        try {
            const res = await fetch('/empresa_celulares/empleado/eliminarAPI', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ empleado_id: id })
            });
            const { codigo, mensaje } = await res.json();
            if (codigo == 1) {
                Swal.fire({ icon: "success", title: "Eliminado", text: mensaje });
                BuscarEmpleados();
            } else {
                Swal.fire({ icon: "error", title: "Error", text: mensaje });
            }
        } catch (error) {
            console.error(error);
        }
    }
};

// Event Listeners
document.addEventListener('DOMContentLoaded', () => {
    cargarUsuarios();
    BuscarEmpleados();
    
    if (empleado_tel) empleado_tel.addEventListener('change', ValidarTelefono);
    if (empleado_dpi) empleado_dpi.addEventListener('change', ValidarDPI);
    if (FormularioEmpleados) FormularioEmpleados.addEventListener('submit', GuardarEmpleado);
    if (BtnLimpiar) BtnLimpiar.addEventListener('click', limpiarTodo);
    
    datatable.on('click', '.modificar', llenarFormulario);
    datatable.on('click', '.eliminar', EliminarEmpleado);
});