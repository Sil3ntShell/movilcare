// permiso/index.js
import Swal from "sweetalert2";
import DataTable from "datatables.net-bs5";
import { validarFormulario } from "../funciones";
import { lenguaje } from "../lenguaje";

// Elementos del DOM
const FormularioPermisos = document.getElementById('FormularioPermisos');
const BtnGuardar = document.getElementById('BtnGuardar');
const BtnModificar = document.getElementById('BtnModificar');
const BtnLimpiar = document.getElementById('BtnLimpiar');
const permiso_clave = document.getElementById('permiso_clave');
const filtro_app = document.getElementById('filtro_app');
const filtro_usuario = document.getElementById('filtro_usuario');
const filtro_tipo = document.getElementById('filtro_tipo');

// Validar Clave de Permiso (solo letras, números y guiones bajos)
const ValidarClavePermiso = () => {
    const clave = permiso_clave.value.trim().toUpperCase();
    
    if (clave.length < 1) {
        permiso_clave.classList.remove('is-valid', 'is-invalid');
        return true;
    }
    
    // Solo letras, números y guiones bajos
    if (!/^[A-Z0-9_]+$/.test(clave)) {
        permiso_clave.classList.add('is-invalid');
        permiso_clave.classList.remove('is-valid');
        Swal.fire({ 
            icon: "error", 
            title: "Clave inválida", 
            text: "Solo se permiten letras mayúsculas, números y guiones bajos (_)" 
        });
        return false;
    }
    
    if (clave.length < 2) {
        permiso_clave.classList.add('is-invalid');
        permiso_clave.classList.remove('is-valid');
        Swal.fire({ 
            icon: "error", 
            title: "Clave muy corta", 
            text: "Debe tener al menos 2 caracteres" 
        });
        return false;
    }
    
    permiso_clave.classList.remove('is-invalid');
    permiso_clave.classList.add('is-valid');
    permiso_clave.value = clave; // Convertir a mayúsculas automáticamente
    return true;
};

// Cargar Usuarios para los selects
const cargarUsuarios = async () => {
    try {
        const respuesta = await fetch('/empresa_celulares/usuario/buscarAPI');
        const data = await respuesta.json();
        
        const selectUsuario = document.getElementById('usuario_id');
        const selectUsuarioAsigna = document.getElementById('permiso_usuario_asigno');
        const filtroUsuario = document.getElementById('filtro_usuario');
        
        if (!selectUsuario || !selectUsuarioAsigna) return;
        
        // Limpiar selects
        selectUsuario.innerHTML = '<option value="">Seleccione un usuario</option>';
        selectUsuarioAsigna.innerHTML = '<option value="">Seleccione usuario que asigna</option>';
        filtroUsuario.innerHTML = '<option value="">Todos los usuarios</option>';
        
        if (data.codigo === 1 && data.data) {
            data.data.forEach(usuario => {
                const nombreCompleto = `${usuario.usuario_nom1} ${usuario.usuario_nom2 || ''} ${usuario.usuario_ape1} ${usuario.usuario_ape2 || ''}`.trim();
                
                // Select principal
                const option1 = document.createElement('option');
                option1.value = usuario.usuario_id;
                option1.textContent = `${nombreCompleto} (${usuario.usuario_correo})`;
                selectUsuario.appendChild(option1);
                
                // Select usuario que asigna
                const option2 = document.createElement('option');
                option2.value = usuario.usuario_id;
                option2.textContent = nombreCompleto;
                selectUsuarioAsigna.appendChild(option2);
                
                // Filtro
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

// Cargar Aplicaciones para los selects
const cargarAplicaciones = async () => {
    try {
        const respuesta = await fetch('/empresa_celulares/aplicacion/buscarAPI');
        const data = await respuesta.json();
        
        const selectApp = document.getElementById('app_id');
        const filtroApp = document.getElementById('filtro_app');
        
        if (!selectApp) return;
        
        selectApp.innerHTML = '<option value="">Seleccione una aplicación</option>';
        filtroApp.innerHTML = '<option value="">Todas las aplicaciones</option>';
        
        if (data.codigo === 1 && data.data) {
            data.data.forEach(app => {
                // Select principal
                const option1 = document.createElement('option');
                option1.value = app.app_id;
                option1.textContent = `${app.app_nombre_corto} - ${app.app_nombre_medium}`;
                selectApp.appendChild(option1);
                
                // Filtro
                const option2 = document.createElement('option');
                option2.value = app.app_id;
                option2.textContent = app.app_nombre_corto;
                filtroApp.appendChild(option2);
            });
        }
    } catch (error) {
        console.error('Error cargando aplicaciones:', error);
    }
};

// Guardar Permiso
const GuardarPermiso = async (event) => {
    event.preventDefault();
    BtnGuardar.disabled = true;

    if (!validarFormulario(FormularioPermisos, ['permiso_id'])) {
        Swal.fire({ icon: "info", title: "Formulario incompleto", text: "Debe completar todos los campos requeridos" });
        BtnGuardar.disabled = false;
        return;
    }

    if (!ValidarClavePermiso()) {
        Swal.fire({ icon: "error", title: "Clave inválida", text: "Debe corregir la clave antes de continuar" });
        BtnGuardar.disabled = false;
        return;
    }

    const body = new FormData(FormularioPermisos);
    const url = '/empresa_celulares/permiso/guardarAPI';

    try {
        const respuesta = await fetch(url, { method: 'POST', body });
        const { codigo, mensaje } = await respuesta.json();
        if (codigo == 1) {
            Swal.fire({ icon: "success", title: "Permiso registrado", text: mensaje });
            limpiarTodo();
            BuscarPermisos();
        } else {
            Swal.fire({ icon: "info", title: "Error", text: mensaje });
        }
    } catch (error) {
        console.error(error);
        Swal.fire({ icon: "error", title: "Error de conexión", text: "No se pudo completar la operación" });
    }
    BtnGuardar.disabled = false;
};

// Buscar Permisos
const BuscarPermisos = async () => {
    const url = '/empresa_celulares/permiso/buscarAPI';
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
        Swal.fire({ icon: "error", title: "Error de conexión", text: "No se pudieron cargar los permisos" });
    }
};

// Aplicar filtros
const aplicarFiltros = () => {
    const appId = filtro_app.value;
    const usuarioId = filtro_usuario.value;
    const tipo = filtro_tipo.value;
    
    let url = '/empresa_celulares/permiso/buscarAPI?';
    const params = [];
    
    if (appId) params.push(`app_id=${appId}`);
    if (usuarioId) params.push(`usuario_id=${usuarioId}`);
    if (tipo) params.push(`tipo=${tipo}`);
    
    url += params.join('&');
    
    fetch(url)
        .then(res => res.json())
        .then(({ codigo, data }) => {
            if (codigo == 1) {
                datatable.clear().draw();
                datatable.rows.add(data).draw();
            }
        })
        .catch(error => console.error('Error aplicando filtros:', error));
};

// DataTable Configuración
const datatable = new DataTable('#TablePermisos', {
    language: lenguaje,
    data: [],
    columns: [
        { 
            title: "ID", 
            data: "permiso_id", 
            render: (data, type, row, meta) => meta.row + 1,
            width: '5%'
        },
        { 
            title: "Usuario", 
            data: null,
            render: (data, type, row) => {
                const nombre = `${row.usuario_nom1 || ''} ${row.usuario_ape1 || ''}`.trim();
                return nombre || 'N/A';
            },
            width: '15%'
        },
        { 
            title: "Aplicación", 
            data: "app_nombre_corto",
            render: (data) => `<span class="badge bg-info">${data}</span>`,
            width: '10%'
        },
        { 
            title: "Nombre", 
            data: "permiso_nombre",
            width: '20%'
        },
        { 
            title: "Clave", 
            data: "permiso_clave",
            render: (data) => `<code class="text-primary">${data}</code>`,
            width: '15%'
        },
        { 
            title: "Tipo", 
            data: "permiso_tipo",
            render: (data) => {
                const colores = {
                    'FUNCIONAL': 'success',
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
            title: "Fecha", 
            data: "permiso_fecha",
            render: (data) => {
                if (!data) return 'N/A';
                const fecha = new Date(data);
                return fecha.toLocaleDateString('es-GT');
            },
            className: 'text-center',
            width: '10%'
        },
        { 
            title: "Descripción", 
            data: "permiso_desc",
            render: (data) => {
                if (!data || data.length === 0) return 'Sin descripción';
                return data.length > 50 ? data.substring(0, 50) + '...' : data;
            },
            width: '15%'
        },
        {
            title: "Acciones", 
            data: "permiso_id",
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
    
    document.getElementById('permiso_id').value = datos.permiso_id;
    document.getElementById('usuario_id').value = datos.usuario_id;
    document.getElementById('app_id').value = datos.app_id;
    document.getElementById('permiso_nombre').value = datos.permiso_nombre;
    document.getElementById('permiso_clave').value = datos.permiso_clave;
    document.getElementById('permiso_desc').value = datos.permiso_desc || '';
    document.getElementById('permiso_tipo').value = datos.permiso_tipo;
    document.getElementById('permiso_usuario_asigno').value = datos.permiso_usuario_asigno || '';
    document.getElementById('permiso_motivo').value = datos.permiso_motivo || '';
    
    BtnGuardar.classList.add('d-none');
    BtnModificar.classList.remove('d-none');
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

// Limpiar formulario
const limpiarTodo = () => {
    FormularioPermisos.reset();
    BtnGuardar.classList.remove('d-none');
    BtnModificar.classList.add('d-none');
    FormularioPermisos.querySelectorAll('.is-valid, .is-invalid').forEach(el => {
        el.classList.remove('is-valid', 'is-invalid');
    });
};

// Modificar Permiso
const ModificarPermiso = async (event) => {
    event.preventDefault();
    BtnModificar.disabled = true;

    if (!validarFormulario(FormularioPermisos, ['permiso_id'])) {
        Swal.fire({ icon: "info", title: "Formulario incompleto", text: "Complete todos los campos" });
        BtnModificar.disabled = false;
        return;
    }

    if (!ValidarClavePermiso()) {
        Swal.fire({ icon: "error", title: "Clave inválida", text: "Debe corregir la clave antes de continuar" });
        BtnModificar.disabled = false;
        return;
    }

    const body = new FormData(FormularioPermisos);
    const url = '/empresa_celulares/permiso/modificarAPI';

    try {
        const respuesta = await fetch(url, { method: 'POST', body });
        const { codigo, mensaje } = await respuesta.json();
        if (codigo == 1) {
            Swal.fire({ icon: "success", title: "Permiso modificado", text: mensaje });
            limpiarTodo();
            BuscarPermisos();
        } else {
            Swal.fire({ icon: "error", title: "Error", text: mensaje });
        }
    } catch (error) {
        console.error(error);
        Swal.fire({ icon: "error", title: "Error de conexión", text: "No se pudo completar la modificación" });
    }
    BtnModificar.disabled = false;
};

// Eliminar Permiso
const EliminarPermiso = async (e) => {
    const id = e.currentTarget.dataset.id;
    const confirmar = await Swal.fire({
        icon: "warning", 
        title: "¿Eliminar permiso?", 
        text: "Esta acción no se puede deshacer.",
        showCancelButton: true, 
        confirmButtonText: "Sí, eliminar", 
        cancelButtonText: "Cancelar",
        confirmButtonColor: '#d33'
    });

    if (confirmar.isConfirmed) {
        const url = `/empresa_celulares/permiso/eliminar?id=${id}`;
        try {
            const res = await fetch(url);
            const { codigo, mensaje } = await res.json();
            if (codigo == 1) {
                Swal.fire({ icon: "success", title: "Eliminado", text: mensaje });
                BuscarPermisos();
            } else {
                Swal.fire({ icon: "error", title: "Error", text: mensaje });
            }
        } catch (error) {
            console.error(error);
            Swal.fire({ icon: "error", title: "Error de conexión", text: "No se pudo eliminar el permiso" });
        }
    }
};

document.addEventListener('DOMContentLoaded', () => {
    // Cargar datos iniciales
    cargarUsuarios();
    cargarAplicaciones();
    BuscarPermisos();
    
    // Validaciones en tiempo real
    if (permiso_clave) {
        permiso_clave.addEventListener('input', (e) => {
            e.target.value = e.target.value.toUpperCase().replace(/[^A-Z0-9_]/g, '');
        });
        permiso_clave.addEventListener('change', ValidarClavePermiso);
    }
    
    // Eventos de filtros
    filtro_app.addEventListener('change', aplicarFiltros);
    filtro_usuario.addEventListener('change', aplicarFiltros);
    filtro_tipo.addEventListener('change', aplicarFiltros);
    
    // Eventos de formulario
    FormularioPermisos.addEventListener('submit', GuardarPermiso);
    BtnModificar.addEventListener('click', ModificarPermiso);
    BtnLimpiar.addEventListener('click', limpiarTodo);
    
    // Eventos de DataTable
    datatable.on('click', '.modificar', llenarFormulario);
    datatable.on('click', '.eliminar', EliminarPermiso);
    
    console.log('Aplicación de permisos inicializada correctamente');
});