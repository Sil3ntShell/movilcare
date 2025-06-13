// asignacion_permiso/index.js
import Swal from "sweetalert2";
import DataTable from "datatables.net-bs5";
import { validarFormulario } from "../funciones";
import { lenguaje } from "../lenguaje";

// Elementos del DOM
const FormularioAsignaciones = document.getElementById('FormularioAsignaciones');
const BtnGuardar = document.getElementById('BtnGuardar');
const BtnModificar = document.getElementById('BtnModificar');
const BtnLimpiar = document.getElementById('BtnLimpiar');
const filtro_aplicacion = document.getElementById('filtro_aplicacion');
const filtro_tipo_permiso = document.getElementById('filtro_tipo_permiso');
const filtro_tabla_usuario = document.getElementById('filtro_tabla_usuario');
const filtro_tabla_app = document.getElementById('filtro_tabla_app');
const filtro_tabla_tipo = document.getElementById('filtro_tabla_tipo');

// Variables globales
let datosPermisos = [];
let datosUsuarios = [];
let datosAplicaciones = [];

// Cargar Usuarios
const cargarUsuarios = async () => {
    try {
        const respuesta = await fetch('/empresa_celulares/usuario/buscarAPI');
        const data = await respuesta.json();
        
        const selectUsuario = document.getElementById('asignacion_usuario_id');
        const selectUsuarioAsigna = document.getElementById('asignacion_usuario_asigno');
        const filtroUsuario = document.getElementById('filtro_tabla_usuario');
        
        if (!selectUsuario || !selectUsuarioAsigna) return;
        
        // Limpiar selects
        selectUsuario.innerHTML = '<option value="">Seleccione el usuario</option>';
        selectUsuarioAsigna.innerHTML = '<option value="">Seleccione quien asigna</option>';
        filtroUsuario.innerHTML = '<option value="">Todos los usuarios</option>';
        
        if (data.codigo === 1 && data.data) {
            datosUsuarios = data.data;
            data.data.forEach(usuario => {
                const nombreCompleto = `${usuario.usuario_nom1} ${usuario.usuario_nom2 || ''} ${usuario.usuario_ape1} ${usuario.usuario_ape2 || ''}`.trim();
                
                // Select usuario que recibe
                const option1 = document.createElement('option');
                option1.value = usuario.usuario_id;
                option1.textContent = `${nombreCompleto} (${usuario.usuario_correo})`;
                selectUsuario.appendChild(option1);
                
                // Select usuario que asigna
                const option2 = document.createElement('option');
                option2.value = usuario.usuario_id;
                option2.textContent = nombreCompleto;
                selectUsuarioAsigna.appendChild(option2);
                
                // Filtro tabla
                const option3 = document.createElement('option');
                option3.value = usuario.usuario_id;
                option3.textContent = nombreCompleto;
                filtroUsuario.appendChild(option3);
            });
        }
    } catch (error) {
        console.error('Error cargando usuarios:', error);
    }
};

// Cargar Aplicaciones
const cargarAplicaciones = async () => {
    try {
        const respuesta = await fetch('/empresa_celulares/aplicacion/buscarAPI');
        const data = await respuesta.json();
        
        const filtroApp = document.getElementById('filtro_aplicacion');
        const filtroTablaApp = document.getElementById('filtro_tabla_app');
        
        filtroApp.innerHTML = '<option value="">Todas las aplicaciones</option>';
        filtroTablaApp.innerHTML = '<option value="">Todas las aplicaciones</option>';
        
        if (data.codigo === 1 && data.data) {
            datosAplicaciones = data.data;
            data.data.forEach(app => {
                // Filtro formulario
                const option1 = document.createElement('option');
                option1.value = app.app_id;
                option1.textContent = `${app.app_nombre_corto} - ${app.app_nombre_medium}`;
                filtroApp.appendChild(option1);
                
                // Filtro tabla
                const option2 = document.createElement('option');
                option2.value = app.app_nombre_corto;
                option2.textContent = app.app_nombre_corto;
                filtroTablaApp.appendChild(option2);
            });
        }
    } catch (error) {
        console.error('Error cargando aplicaciones:', error);
    }
};

// Cargar Permisos con filtros
const cargarPermisos = async () => {
    try {
        const appId = filtro_aplicacion.value;
        const tipo = filtro_tipo_permiso.value;
        
        let url = '/empresa_celulares/permiso/buscarAPI?';
        const params = [];
        
        if (appId) params.push(`app_id=${appId}`);
        if (tipo) params.push(`tipo=${tipo}`);
        
        url += params.join('&');
        
        const respuesta = await fetch(url);
        const data = await respuesta.json();
        
        const selectPermiso = document.getElementById('asignacion_permiso_id');
        selectPermiso.innerHTML = '<option value="">Seleccione el permiso</option>';
        
        if (data.codigo === 1 && data.data) {
            datosPermisos = data.data;
            data.data.forEach(permiso => {
                const option = document.createElement('option');
                option.value = permiso.permiso_id;
                option.textContent = `${permiso.app_nombre_corto} - ${permiso.permiso_nombre} (${permiso.permiso_clave})`;
                option.setAttribute('data-app', permiso.app_nombre_corto);
                option.setAttribute('data-tipo', permiso.permiso_tipo);
                option.setAttribute('data-desc', permiso.permiso_desc || 'Sin descripción');
                selectPermiso.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Error cargando permisos:', error);
    }
};

// Validar que no sea auto-asignación
const validarAutoAsignacion = () => {
    const usuarioRecibe = document.getElementById('asignacion_usuario_id').value;
    const usuarioAsigna = document.getElementById('asignacion_usuario_asigno').value;
    
    if (usuarioRecibe && usuarioAsigna && usuarioRecibe === usuarioAsigna) {
        Swal.fire({
            icon: "warning",
            title: "Auto-asignación no permitida",
            text: "Un usuario no puede asignarse permisos a sí mismo"
        });
        return false;
    }
    return true;
};

// Guardar Asignación
const GuardarAsignacion = async (event) => {
    event.preventDefault();
    BtnGuardar.disabled = true;

    if (!validarFormulario(FormularioAsignaciones, ['asignacion_id'])) {
        Swal.fire({ icon: "info", title: "Formulario incompleto", text: "Debe completar todos los campos requeridos" });
        BtnGuardar.disabled = false;
        return;
    }

    if (!validarAutoAsignacion()) {
        BtnGuardar.disabled = false;
        return;
    }

    const body = new FormData(FormularioAsignaciones);
    const url = '/empresa_celulares/asignacion_permiso/guardarAPI';

    try {
        const respuesta = await fetch(url, { method: 'POST', body });
        const { codigo, mensaje } = await respuesta.json();
        if (codigo == 1) {
            Swal.fire({ icon: "success", title: "Permiso asignado", text: mensaje });
            limpiarTodo();
            BuscarAsignaciones();
        } else {
            Swal.fire({ icon: "info", title: "Error", text: mensaje });
        }
    } catch (error) {
        console.error(error);
        Swal.fire({ icon: "error", title: "Error de conexión", text: "No se pudo completar la operación" });
    }
    BtnGuardar.disabled = false;
};

// Buscar Asignaciones
const BuscarAsignaciones = async () => {
    const url = '/empresa_celulares/asignacion_permiso/buscarAPI';
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
        Swal.fire({ icon: "error", title: "Error de conexión", text: "No se pudieron cargar las asignaciones" });
    }
};

// Aplicar filtros de tabla
const aplicarFiltrosTabla = () => {
    const usuarioId = filtro_tabla_usuario.value;
    const app = filtro_tabla_app.value;
    const tipo = filtro_tabla_tipo.value;
    
    let url = '/empresa_celulares/asignacion_permiso/buscarAPI?';
    const params = [];
    
    if (usuarioId) params.push(`usuario_id=${usuarioId}`);
    
    url += params.join('&');
    
    fetch(url)
        .then(res => res.json())
        .then(({ codigo, data }) => {
            if (codigo == 1) {
                let datosFiltrados = data;
                
                // Filtrar por aplicación si está seleccionada
                if (app) {
                    datosFiltrados = datosFiltrados.filter(item => item.app_nombre_corto === app);
                }
                
                // Filtrar por tipo si está seleccionado
                if (tipo) {
                    datosFiltrados = datosFiltrados.filter(item => item.permiso_tipo === tipo);
                }
                
                datatable.clear().draw();
                datatable.rows.add(datosFiltrados).draw();
            }
        })
        .catch(error => console.error('Error aplicando filtros:', error));
};

// Mostrar detalles de asignación
const mostrarDetalles = (datos) => {
    const modalDetalles = new bootstrap.Modal(document.getElementById('modalDetalles'));
    const content = document.getElementById('detallesContent');
    
    content.innerHTML = `
        <div class="row">
            <div class="col-md-6">
                <h6 class="text-primary">Información del Usuario</h6>
                <p><strong>Nombre:</strong> ${datos.usuario_nombre_completo || 'N/A'}</p>
                <p><strong>Fecha Asignación:</strong> ${datos.fecha_formateada || 'N/A'}</p>
                ${datos.fecha_quito_formateada ? `<p><strong>Fecha Retiro:</strong> ${datos.fecha_quito_formateada}</p>` : ''}
            </div>
            <div class="col-md-6">
                <h6 class="text-primary">Información del Permiso</h6>
                <p><strong>Aplicación:</strong> <span class="badge bg-info">${datos.app_nombre_corto}</span></p>
                <p><strong>Permiso:</strong> ${datos.permiso_nombre}</p>
                <p><strong>Tipo:</strong> <span class="badge bg-secondary">${datos.permiso_tipo}</span></p>
                <p><strong>Descripción:</strong> ${datos.permiso_desc || 'Sin descripción'}</p>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-12">
                <h6 class="text-primary">Información de Asignación</h6>
                <p><strong>Asignado por:</strong> ${datos.asigno_nombre_completo || 'N/A'}</p>
                <p><strong>Motivo:</strong> ${datos.asignacion_motivo || 'Sin motivo especificado'}</p>
            </div>
        </div>
    `;
    
    modalDetalles.show();
};

// DataTable Configuración
const datatable = new DataTable('#TableAsignaciones', {
    language: lenguaje,
    data: [],
    columns: [
        { 
            title: "ID", 
            data: "asignacion_id", 
            render: (data, type, row, meta) => meta.row + 1,
            width: '5%'
        },
        { 
            title: "Usuario", 
            data: "usuario_nombre_completo",
            render: (data) => data || 'N/A',
            width: '20%'
        },
        { 
            title: "Aplicación", 
            data: "app_nombre_corto",
            render: (data) => `<span class="badge bg-info">${data}</span>`,
            width: '10%'
        },
        { 
            title: "Permiso", 
            data: "permiso_nombre",
            width: '25%'
        },
        { 
            title: "Tipo", 
            data: "permiso_tipo",
            render: (data) => {
                const colores = {
                    'FUNCIONAL': 'Success',
                    'MENU': 'primary',
                    'REPORTE': 'warning',
                    'ADMIN': 'danger',
                    'ESPECIAL': 'dark'
                };
                const color = colores[data] || 'secondary';
                return `<span class="badge bg-${color}">${data}</span>`;
            },
            width: '10%'
        },
        { 
            title: "Fecha Asignación", 
            data: "fecha_formateada",
            render: (data) => data || 'N/A',
            className: 'text-center',
            width: '12%'
        },
        { 
            title: "Asignado por", 
            data: "asigno_nombre_completo",
            render: (data) => data || 'N/A',
            width: '15%'
        },
        {
            title: "Acciones", 
            data: "asignacion_id",
            render: (id, t, row) => `
                <div class="d-flex justify-content-center gap-1">
                    <button class="btn btn-info btn-sm detalles" data-json='${JSON.stringify(row)}' title="Ver Detalles">
                        <i class="bi bi-eye"></i>
                    </button>
                    <button class="btn btn-warning btn-sm modificar" data-id="${id}" data-json='${JSON.stringify(row)}' title="Modificar">
                        <i class="bi bi-pencil-square"></i>
                    </button>
                    <button class="btn btn-danger btn-sm eliminar" data-id="${id}" title="Quitar Permiso">
                        <i class="bi bi-x-circle"></i>
                    </button>
                </div>
            `,
            orderable: false,
            searchable: false,
            width: '13%'
        }
    ]
});

// Llenar formulario para modificar
const llenarFormulario = (e) => {
    const datos = JSON.parse(e.currentTarget.dataset.json);
    
    document.getElementById('asignacion_id').value = datos.asignacion_id;
    document.getElementById('asignacion_usuario_id').value = datos.asignacion_usuario_id;
    document.getElementById('asignacion_permiso_id').value = datos.asignacion_permiso_id;
    document.getElementById('asignacion_usuario_asigno').value = datos.asignacion_usuario_asigno;
    document.getElementById('asignacion_motivo').value = datos.asignacion_motivo || '';
    
    BtnGuardar.classList.add('d-none');
    BtnModificar.classList.remove('d-none');
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

// Limpiar formulario
const limpiarTodo = () => {
    FormularioAsignaciones.reset();
    BtnGuardar.classList.remove('d-none');
    BtnModificar.classList.add('d-none');
    FormularioAsignaciones.querySelectorAll('.is-valid, .is-invalid').forEach(el => {
        el.classList.remove('is-valid', 'is-invalid');
    });
    cargarPermisos(); // Recargar permisos sin filtros
};

// Modificar Asignación
const ModificarAsignacion = async (event) => {
    event.preventDefault();
    BtnModificar.disabled = true;

    if (!validarFormulario(FormularioAsignaciones, ['asignacion_id'])) {
        Swal.fire({ icon: "info", title: "Formulario incompleto", text: "Complete todos los campos" });
        BtnModificar.disabled = false;
        return;
    }

    if (!validarAutoAsignacion()) {
        BtnModificar.disabled = false;
        return;
    }

    const body = new FormData(FormularioAsignaciones);
    const url = '/empresa_celulares/asignacion_permiso/modificarAPI';

    try {
        const respuesta = await fetch(url, { method: 'POST', body });
        const { codigo, mensaje } = await respuesta.json();
        if (codigo == 1) {
            Swal.fire({ icon: "success", title: "Asignación modificada", text: mensaje });
            limpiarTodo();
            BuscarAsignaciones();
        } else {
            Swal.fire({ icon: "error", title: "Error", text: mensaje });
        }
    } catch (error) {
        console.error(error);
        Swal.fire({ icon: "error", title: "Error de conexión", text: "No se pudo completar la modificación" });
    }
    BtnModificar.disabled = false;
};

// Eliminar Asignación
const EliminarAsignacion = async (e) => {
    const id = e.currentTarget.dataset.id;
    const confirmar = await Swal.fire({
        icon: "warning", 
        title: "¿Quitar este permiso?", 
        text: "Se registrará la fecha de retiro del permiso.",
        showCancelButton: true, 
        confirmButtonText: "Sí, quitar permiso", 
        cancelButtonText: "Cancelar",
        confirmButtonColor: '#d33'
    });

    if (confirmar.isConfirmed) {
        const url = `/empresa_celulares/asignacion_permiso/eliminar?id=${id}`;
        try {
            const res = await fetch(url);
            const { codigo, mensaje } = await res.json();
            if (codigo == 1) {
                Swal.fire({ icon: "success", title: "Permiso retirado", text: mensaje });
                BuscarAsignaciones();
            } else {
                Swal.fire({ icon: "error", title: "Error", text: mensaje });
            }
        } catch (error) {
            console.error(error);
            Swal.fire({ icon: "error", title: "Error de conexión", text: "No se pudo quitar el permiso" });
        }
    }
};

document.addEventListener('DOMContentLoaded', () => {
    // Cargar datos iniciales
    cargarUsuarios();
    cargarAplicaciones();
    cargarPermisos();
    BuscarAsignaciones();
    
    // Eventos de filtros de formulario
    filtro_aplicacion.addEventListener('change', cargarPermisos);
    filtro_tipo_permiso.addEventListener('change', cargarPermisos);
    
    // Eventos de filtros de tabla
    filtro_tabla_usuario.addEventListener('change', aplicarFiltrosTabla);
    filtro_tabla_app.addEventListener('change', aplicarFiltrosTabla);
    filtro_tabla_tipo.addEventListener('change', aplicarFiltrosTabla);
    
    // Validación en tiempo real
    document.getElementById('asignacion_usuario_id').addEventListener('change', validarAutoAsignacion);
    document.getElementById('asignacion_usuario_asigno').addEventListener('change', validarAutoAsignacion);
    
    // Eventos de formulario
    FormularioAsignaciones.addEventListener('submit', GuardarAsignacion);
    BtnModificar.addEventListener('click', ModificarAsignacion);
    BtnLimpiar.addEventListener('click', limpiarTodo);
    
    // Eventos de DataTable
    datatable.on('click', '.detalles', (e) => {
        const datos = JSON.parse(e.currentTarget.dataset.json);
        mostrarDetalles(datos);
    });
    datatable.on('click', '.modificar', llenarFormulario);
    datatable.on('click', '.eliminar', EliminarAsignacion);
    
    console.log('Aplicación de asignación de permisos inicializada correctamente');
});