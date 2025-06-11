import DataTable from "datatables.net-bs5";
import { validarFormulario } from '../funciones';
import Swal from "sweetalert2";
import { lenguaje } from "../lenguaje";

const formUsuario = document.getElementById('userForm');
const btnRegistrar = document.querySelector('button[type="submit"]');
const btnLimpiar = document.getElementById('btnLimpiar');
const btnActualizarUsuarios = document.getElementById('btnActualizarUsuarios');
const tablaContainer = document.getElementById('tablaContainer');

let tablaUsuarios = null;
let usuarioEditando = null;

// GUARDAR USUARIO
const guardarUsuario = async (e) => {
    e.preventDefault();
    btnRegistrar.disabled = true;

    if (!validarFormulario(formUsuario, ['usuario_id'])) {
        Swal.fire('Error', 'Complete todos los campos obligatorios', 'warning');
        btnRegistrar.disabled = false;
        return;
    }

    const body = new FormData(formUsuario);
    const url = usuarioEditando ? '/empresa_celulares/usuario/actualizarAPI' : '/empresa_celulares/usuario/guardarAPI';
    
    if (usuarioEditando) {
        body.append('usuario_id', usuarioEditando);
    }

    try {
        const respuesta = await fetch(url, { method: 'POST', body });
        const data = await respuesta.json();
        
        if (data.codigo == 1) {
            Swal.fire('¡Éxito!', data.mensaje, 'success');
            formUsuario.reset();
            usuarioEditando = null;
            btnRegistrar.textContent = 'Registrar Usuario';
            cargarUsuarios();
        } else {
            Swal.fire('Error', data.mensaje, 'error');
        }
    } catch (error) {
        Swal.fire('Error', 'Error de conexión', 'error');
    }

    btnRegistrar.disabled = false;
}

// CARGAR USUARIOS
const cargarUsuarios = async () => {
    try {
        const respuesta = await fetch('/empresa_celulares/usuario/buscarAPI');
        const data = await respuesta.json();

        if (data.codigo === 1) {
            mostrarTablaUsuarios(data.data);
        } else {
            tablaContainer.innerHTML = `<div class="alert alert-info">${data.mensaje}</div>`;
        }
    } catch (error) {
        tablaContainer.innerHTML = `<div class="alert alert-danger">Error al cargar usuarios</div>`;
    }
}

// MOSTRAR TABLA
const mostrarTablaUsuarios = (usuarios) => {
    if (usuarios.length === 0) {
        tablaContainer.innerHTML = '<div class="alert alert-info">No hay usuarios registrados</div>';
        return;
    }

    tablaContainer.innerHTML = `
        <div class="table-responsive">
            <table id="tablaUsuarios" class="table table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Foto</th>
                        <th>Nombre</th>
                        <th>DPI</th>
                        <th>Teléfono</th>
                        <th>Correo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    `;

    if (tablaUsuarios) tablaUsuarios.destroy();

    const datosTabla = usuarios.map(usuario => {
        const nombreCompleto = `${usuario.usuario_nom1} ${usuario.usuario_nom2 || ''} ${usuario.usuario_ape1} ${usuario.usuario_ape2 || ''}`.trim();
        
        // Foto
        let fotoHtml;
        if (usuario.usuario_fotografia) {
            fotoHtml = `<img src="/empresa_celulares/storage/fotos_usuarios/${usuario.usuario_fotografia}" 
                           style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover;" 
                           onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNTAiIGhlaWdodD0iNTAiIHZpZXdCb3g9IjAgMCA1MCA1MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPGNpcmNsZSBjeD0iMjUiIGN5PSIyNSIgcj0iMjUiIGZpbGw9IiM2Yzc1N2QiLz4KPGNpcmNsZSBjeD0iMjUiIGN5PSIyMCIgcj0iNSIgZmlsbD0iI2ZmZmZmZiIvPgo8cGF0aCBkPSJNMzUgMzVBMTAgMTAgMCAwIDAgMTUgMzUiIGZpbGw9IiNmZmZmZmYiLz4KPC9zdmc+';"/>`;
        } else {
            fotoHtml = `<span class="badge bg-secondary rounded-circle" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; font-size: 10px;">Sin foto</span>`;
        }

        // Acciones
        const acciones = `
            <div class="btn-group btn-group-sm">
                <button class="btn btn-warning" onclick="editarUsuario(${usuario.usuario_id})" title="Editar">
                    <i class="bi bi-pencil"></i>
                </button>
                <button class="btn btn-danger" onclick="eliminarUsuario(${usuario.usuario_id})" title="Eliminar">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        `;

        return [fotoHtml, nombreCompleto, usuario.usuario_dpi, usuario.usuario_tel, usuario.usuario_correo, acciones];
    });

    tablaUsuarios = new DataTable('#tablaUsuarios', {
        data: datosTabla,
        language: lenguaje,
        pageLength: 10,
        order: [[1, 'asc']],
        columnDefs: [
            { targets: [0, 5], orderable: false, className: 'text-center' }
        ]
    });
}

// EDITAR USUARIO
window.editarUsuario = async (id) => {
    try {
        const respuesta = await fetch(`/empresa_celulares/usuario/obtenerPorIdAPI?id=${id}`);
        const data = await respuesta.json();

        if (data.codigo === 1) {
            const usuario = data.data;
            
            // Llenar formulario
            document.getElementById('usuario_nom1').value = usuario.usuario_nom1;
            document.getElementById('usuario_nom2').value = usuario.usuario_nom2 || '';
            document.getElementById('usuario_ape1').value = usuario.usuario_ape1;
            document.getElementById('usuario_ape2').value = usuario.usuario_ape2 || '';
            document.getElementById('usuario_tel').value = usuario.usuario_tel;
            document.getElementById('usuario_direc').value = usuario.usuario_direc;
            document.getElementById('usuario_correo').value = usuario.usuario_correo;

            usuarioEditando = id;
            btnRegistrar.textContent = 'Actualizar Usuario';
            
            // Scroll al formulario
            formUsuario.scrollIntoView({ behavior: 'smooth' });
        }
    } catch (error) {
        Swal.fire('Error', 'No se pudo cargar el usuario', 'error');
    }
}

// ELIMINAR USUARIO
window.eliminarUsuario = (id) => {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "Esta acción desactivará el usuario",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                const respuesta = await fetch('/empresa_celulares/usuario/eliminarAPI', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ usuario_id: id })
                });
                
                const data = await respuesta.json();
                
                if (data.codigo === 1) {
                    Swal.fire('¡Eliminado!', data.mensaje, 'success');
                    cargarUsuarios();
                } else {
                    Swal.fire('Error', data.mensaje, 'error');
                }
            } catch (error) {
                Swal.fire('Error', 'No se pudo eliminar el usuario', 'error');
            }
        }
    });
}

// LIMPIAR FORMULARIO
const limpiarFormulario = () => {
    formUsuario.reset();
    usuarioEditando = null;
    btnRegistrar.textContent = 'Registrar Usuario';
    
    // Limpiar validaciones
    formUsuario.querySelectorAll('.form-control').forEach(input => {
        input.classList.remove('is-valid', 'is-invalid');
    });
}

// EVENT LISTENERS
document.addEventListener('DOMContentLoaded', () => {
    if (formUsuario) formUsuario.addEventListener('submit', guardarUsuario);
    if (btnLimpiar) btnLimpiar.addEventListener('click', limpiarFormulario);
    if (btnActualizarUsuarios) btnActualizarUsuarios.addEventListener('click', cargarUsuarios);
    
    // Cargar usuarios al iniciar
    cargarUsuarios();
});