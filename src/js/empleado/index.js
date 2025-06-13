// empleado/index.js
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

// Validar Teléfono - Mejorado
const ValidarTelefono = () => {
    const numero = empleado_tel.value.trim();
    
    if (numero.length < 1) {
        empleado_tel.classList.remove('is-valid', 'is-invalid');
        return true;
    } else {
        if (numero.length !== 8) {
            empleado_tel.classList.add('is-invalid');
            empleado_tel.classList.remove('is-valid');
            Swal.fire({
                position: "center",
                icon: "warning",
                title: "Teléfono incorrecto",
                text: "Debe tener exactamente 8 dígitos",
                showConfirmButton: true,
            });
            return false;
        } else {
            empleado_tel.classList.remove('is-invalid');
            empleado_tel.classList.add('is-valid');
            return true;
        }
    }
};

// Validar DPI - Mejorado
const ValidarDPI = () => {
    const numeroDPI = empleado_dpi.value.trim();
    
    if (numeroDPI.length < 1) {
        empleado_dpi.classList.remove('is-valid', 'is-invalid');
        return true;
    } else {
        if (!/^\d+$/.test(numeroDPI)) {
            empleado_dpi.classList.add('is-invalid');
            empleado_dpi.classList.remove('is-valid');
            Swal.fire({ 
                icon: "error", 
                title: "DPI inválido", 
                text: "El DPI debe contener solo números" 
            });
            return false;
        }
        
        if (numeroDPI.length !== 13) {
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
            return false;
        } else {
            empleado_dpi.classList.remove('is-invalid');
            empleado_dpi.classList.add('is-valid');
            return true;
        }
    }
};


// Limpiar formulario - Mejorado
const limpiarTodo = () => {
    FormularioEmpleados.reset();
    empleadoEditando = null;
    BtnGuardar.textContent = 'Guardar';
    BtnGuardar.className = 'btn btn-success px-4';
    BtnGuardar.classList.remove('d-none');
    BtnModificar.classList.add('d-none');
    
    FormularioEmpleados.querySelectorAll('.form-control, .form-select').forEach(element => {
        element.classList.remove('is-valid', 'is-invalid');
        element.title = '';
    });
};

// Guardar/Modificar Empleado - Mejorado
const GuardarEmpleado = async (event) => {
    event.preventDefault();
    BtnGuardar.disabled = true;

    // Validar formulario y campos específicos
    const telefonoValido = ValidarTelefono();
    const dpiValido = ValidarDPI();

    if (!validarFormulario(FormularioEmpleados, ['empleado_id']) || !telefonoValido || !dpiValido) {
        Swal.fire({
            position: "center",
            icon: "info",
            title: "FORMULARIO INCOMPLETO",
            text: "Verifique todos los campos",
            showConfirmButton: true,
        });
        BtnGuardar.disabled = false;
        return;
    }

    const body = new FormData(FormularioEmpleados);
    let url = '/empresa_celulares/empleado/guardarAPI';
    
    if (empleadoEditando) {
        url = '/empresa_celulares/empleado/modificarAPI';
        body.append('empleado_id', empleadoEditando);
    }

    const config = {
        method: 'POST',
        body
    };

    try {
        const respuesta = await fetch(url, config);
        const datos = await respuesta.json();
        const { codigo, mensaje } = datos;

        if (codigo == 1) {
            await Swal.fire({
                position: "center",
                icon: "success",
                title: "¡Éxito!",
                text: mensaje,
                showConfirmButton: true,
            });

            limpiarTodo();
            await BuscarEmpleados();

        } else {
            await Swal.fire({
                position: "center",
                icon: "info",
                title: "Error",
                text: mensaje,
                showConfirmButton: true,
            });
        }

    } catch (error) {
        console.log(error);
        await Swal.fire({
            position: "center",
            icon: "error",
            title: "Error de conexión",
            text: "No se pudo completar la operación",
            showConfirmButton: true,
        });
    }
    BtnGuardar.disabled = false;
};

// Buscar Empleados - Mejorado
const BuscarEmpleados = async () => {
    const url = '/empresa_celulares/empleado/buscarAPI';
    const config = {
        method: 'GET'
    };

    try {
        const respuesta = await fetch(url, config);
        const datos = await respuesta.json();
        const { codigo, mensaje, data } = datos;

        if (codigo == 1) {
            await Swal.fire({
                position: "center",
                icon: "success",
                title: "¡Empleados cargados!",
                text: `Se cargaron ${data.length} empleado(s) correctamente`,
                showConfirmButton: true,
                timer: 2000
            });

            datatable.clear().draw();
            datatable.rows.add(data).draw();
        } else {
            await Swal.fire({
                position: "center",
                icon: "info",
                title: "Sin datos",
                text: mensaje,
                showConfirmButton: true,
            });
        }

    } catch (error) {
        console.log('Error en BuscarEmpleados:', error);
        await Swal.fire({
            position: "center",
            icon: "error",
            title: "Error de conexión",
            text: "No se pudieron cargar los empleados",
            showConfirmButton: true,
        });
    }
};

// Llenar formulario para editar - Mejorado
const llenarFormulario = (e) => {
    try {
        const datos = JSON.parse(e.currentTarget.dataset.json);
        
        document.getElementById('empleado_nom1').value = datos.empleado_nom1 || '';
        document.getElementById('empleado_nom2').value = datos.empleado_nom2 || '';
        document.getElementById('empleado_ape1').value = datos.empleado_ape1 || '';
        document.getElementById('empleado_ape2').value = datos.empleado_ape2 || '';
        document.getElementById('empleado_tel').value = datos.empleado_tel || '';
        document.getElementById('empleado_dpi').value = datos.empleado_dpi || '';
        document.getElementById('empleado_correo').value = datos.empleado_correo || '';
        document.getElementById('empleado_especialidad').value = datos.empleado_especialidad || '';
        document.getElementById('empleado_salario').value = datos.empleado_salario || '';

        empleadoEditando = datos.empleado_id;
        BtnGuardar.textContent = 'Actualizar Empleado';
        BtnGuardar.className = 'btn btn-warning px-4';
        BtnGuardar.classList.remove('d-none');
        BtnModificar.classList.add('d-none');
        
        window.scrollTo({ top: 0, behavior: 'smooth' });
    } catch (error) {
        console.error('Error parsing JSON:', error);
        Swal.fire({ icon: "error", title: "Error", text: "Error al cargar los datos del empleado" });
    }
};

// Eliminar empleado - Mejorado
const EliminarEmpleado = async (e) => {
    const idEmpleado = e.currentTarget.dataset.id;

    const AlertaConfirmarEliminar = await Swal.fire({
        position: "center",
        icon: "question",
        title: "¿Desea ejecutar esta acción?",
        text: 'Está completamente seguro que desea eliminar este empleado',
        showConfirmButton: true,
        confirmButtonText: 'Sí, Eliminar',
        confirmButtonColor: '#d33',
        cancelButtonText: 'No, Cancelar',
        showCancelButton: true
    });

    if (AlertaConfirmarEliminar.isConfirmed) {
        const url = `/empresa_celulares/empleado/eliminar?id=${idEmpleado}`;
        const config = {
            method: 'GET'
        };

        try {
            const consulta = await fetch(url, config);
            const respuesta = await consulta.json();
            const { codigo, mensaje } = respuesta;

            if (codigo == 1) {
                await Swal.fire({
                    position: "center",
                    icon: "success",
                    title: "¡Éxito!",
                    text: mensaje,
                    showConfirmButton: true,
                });
                
                await BuscarEmpleados();
            } else {
                await Swal.fire({
                    position: "center",
                    icon: "error",
                    title: "Error",
                    text: mensaje,
                    showConfirmButton: true,
                });
            }

        } catch (error) {
            console.log(error);
            await Swal.fire({
                position: "center",
                icon: "error",
                title: "Error de conexión",
                text: "No se pudo completar la eliminación",
                showConfirmButton: true,
            });
        }
    }
};

// Configurar DataTable - Mejorado
datatable = new DataTable('#TablaEmpleados', {
    dom: `
        <"row mt-3 justify-content-between" 
            <"col" l> 
            <"col" B> 
            <"col-3" f>
        >
        t
        <"row mt-3 justify-content-between" 
            <"col-md-3 d-flex align-items-center" i> 
            <"col-md-8 d-flex justify-content-end" p>
        >
    `,
    language: lenguaje,
    data: [],
    columns: [
        { 
            title: "No.", 
            data: null,
            render: (data, type, row, meta) => meta.row + 1,
            width: '5%'
        },
        { 
            title: "Nombre Completo", 
            data: null, 
            render: (data, type, row) => {
                const nom1 = row.empleado_nom1 || '';
                const nom2 = row.empleado_nom2 || '';
                const ape1 = row.empleado_ape1 || '';
                const ape2 = row.empleado_ape2 || '';
                return `${nom1} ${nom2} ${ape1} ${ape2}`.trim();
            },
            width: '15%'
        },
        { 
            title: "DPI", 
            data: "empleado_dpi",
            width: '10%'
        },
        { 
            title: "Teléfono", 
            data: "empleado_tel",
            width: '8%'
        },
        { 
            title: "Correo", 
            data: "empleado_correo",
            width: '15%'
        },
        { 
            title: "Especialidad", 
            data: "empleado_especialidad",
            width: '12%'
        },
        { 
            title: "Salario", 
            data: "empleado_salario",
            render: (data) => `Q. ${parseFloat(data || 0).toLocaleString('es-GT', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`,
            className: 'text-end',
            width: '10%'
        },
        { 
            title: "Fecha Contratación", 
            data: "empleado_fecha_contratacion",
            render: (data) => data ? new Date(data).toLocaleDateString('es-GT') : 'N/A',
            className: 'text-center',
            width: '10%'
        },
        {
            title: "Acciones", 
            data: "empleado_id",
            render: (id, type, row) => `
                <div class='d-flex justify-content-center'>
                    <button class='btn btn-warning modificar mx-1' 
                        data-id="${id}" 
                        data-json='${JSON.stringify(row)}'
                        title="Modificar">   
                        <i class='bi bi-pencil-square me-1'></i> Modificar
                    </button>
                    <button class='btn btn-danger eliminar mx-1' 
                        data-id="${id}"
                        title="Eliminar">
                       <i class="bi bi-trash3 me-1"></i>Eliminar
                    </button>
                </div>
            `,
            orderable: false,
            searchable: false,
            className: 'text-center',
            width: '12%'
        }
    ]
});

// Event Listeners
document.addEventListener('DOMContentLoaded', () => {
    // Cargar datos iniciales
    BuscarEmpleados();
    
    // Validaciones en tiempo real
    if (empleado_tel) empleado_tel.addEventListener('change', ValidarTelefono);
    if (empleado_dpi) empleado_dpi.addEventListener('change', ValidarDPI);
    
    // Eventos de formulario
    if (FormularioEmpleados) FormularioEmpleados.addEventListener('submit', GuardarEmpleado);
    if (BtnLimpiar) BtnLimpiar.addEventListener('click', limpiarTodo);
    
    // Eventos de DataTable
    datatable.on('click', '.modificar', llenarFormulario);
    datatable.on('click', '.eliminar', EliminarEmpleado);
    
    console.log('Aplicación de empleados inicializada correctamente');
});