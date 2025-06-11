import DataTable from "datatables.net-bs5";
import { validarFormulario } from '../funciones';
import Swal from "sweetalert2";
import { lenguaje } from "../lenguaje";

// Elementos del DOM
const formUsuario = document.getElementById('userForm');
const btnRegistrar = document.getElementById('btnRegistrar');
const btnLimpiar = document.getElementById('btnLimpiar');

let usuarioEditando = null;
let datatable = null;

// CARGAR ROLES PARA EL SELECT
const cargarRoles = async () => {
    try {
        const respuesta = await fetch('/empresa_celulares/rol/buscarAPI');
        const data = await respuesta.json();
        
        const selectRol = document.getElementById('rol_id');
        if (!selectRol) {
            console.error('No se encontró el elemento rol_id');
            return;
        }
        
        selectRol.innerHTML = '<option value="">Seleccione un rol</option>';
        
        if (data.codigo === 1 && data.data) {
            data.data.forEach(rol => {
                const option = document.createElement('option');
                option.value = rol.rol_id;
                option.textContent = rol.rol_nombre;
                selectRol.appendChild(option);
            });
            console.log('Roles cargados exitosamente:', data.data);
        } else {
            console.error('Error cargando roles:', data.mensaje);
            Swal.fire('Error', 'No se pudieron cargar los roles: ' + data.mensaje, 'error');
        }
    } catch (error) {
        console.error('Error en cargarRoles:', error);
        Swal.fire('Error', 'Error de conexión al cargar roles', 'error');
    }
};

// VALIDACIONES EN TIEMPO REAL
const validarContraseña = () => {
    const contra = document.getElementById('usuario_contra');
    const confirmar = document.getElementById('confirmar_contra');
    
    if (!contra || !confirmar) return;
    
    const contraValue = contra.value;
    const confirmarValue = confirmar.value;
    
    // Validar fortaleza de contraseña
    const tieneMayus = /[A-Z]/.test(contraValue);
    const tieneMinus = /[a-z]/.test(contraValue);
    const tieneNum = /[0-9]/.test(contraValue);
    const tieneEspecial = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(contraValue);
    
    if (contraValue.length >= 10 && tieneMayus && tieneMinus && tieneNum && tieneEspecial) {
        contra.classList.add('is-valid');
        contra.classList.remove('is-invalid');
    } else if (contraValue.length > 0) {
        contra.classList.add('is-invalid');
        contra.classList.remove('is-valid');
    } else {
        contra.classList.remove('is-valid', 'is-invalid');
    }
    
    // Validar confirmación
    if (confirmarValue && contraValue === confirmarValue && contraValue.length >= 10) {
        confirmar.classList.add('is-valid');
        confirmar.classList.remove('is-invalid');
    } else if (confirmarValue.length > 0) {
        confirmar.classList.add('is-invalid');
        confirmar.classList.remove('is-valid');
    } else {
        confirmar.classList.remove('is-valid', 'is-invalid');
    }
};

// MOSTRAR/OCULTAR CONTRASEÑA
const togglePassword = () => {
    const passwordInput = document.getElementById('usuario_contra');
    const toggleIcon = document.getElementById('toggleIcon');
    
    if (!passwordInput || !toggleIcon) return;
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.className = 'bi bi-eye-slash';
    } else {
        passwordInput.type = 'password';
        toggleIcon.className = 'bi bi-eye';
    }
};

// PREVIEW DE IMAGEN
const previsualizarImagen = (event) => {
    const file = event.target.files[0];
    const preview = document.getElementById('imagePreview');
    const previewImg = document.getElementById('previewImg');
    
    if (!preview || !previewImg) return;
    
    if (file) {
        if (file.size > 2 * 1024 * 1024) {
            Swal.fire('Error', 'La imagen debe ser menor a 2MB', 'error');
            event.target.value = '';
            preview.classList.add('d-none');
            return;
        }
        
        const reader = new FileReader();
        reader.onload = (e) => {
            previewImg.src = e.target.result;
            preview.classList.remove('d-none');
        };
        reader.readAsDataURL(file);
    } else {
        preview.classList.add('d-none');
    }
};

// GUARDAR USUARIO
const guardarUsuario = async (event) => {
    event.preventDefault();
    
    if (!btnRegistrar) return;
    btnRegistrar.disabled = true;

    console.log('Guardando usuario. Modo edición:', !!usuarioEditando);

    if (!validarFormulario(formUsuario, ['usuario_id'])) {
        Swal.fire({ icon: "info", title: "Formulario incompleto", text: "Debe completar todos los campos requeridos" });
        btnRegistrar.disabled = false;
        return;
    }

    // Validar contraseñas solo si no estamos editando O si se proporcionaron nuevas contraseñas
    const contra = document.getElementById('usuario_contra');
    const confirmar = document.getElementById('confirmar_contra');
    
    if (!usuarioEditando || (contra && contra.value.trim() !== '')) {
        if (!contra || !confirmar) {
            btnRegistrar.disabled = false;
            return;
        }
        
        if (contra.value !== confirmar.value) {
            Swal.fire({ icon: "error", title: "Error", text: "Las contraseñas no coinciden" });
            btnRegistrar.disabled = false;
            return;
        }

        // Solo validar complejidad si se proporciona contraseña
        if (contra.value.trim() !== '' && contra.classList.contains('is-invalid')) {
            Swal.fire({ icon: "error", title: "Contraseña inválida", text: "Debe corregir la contraseña antes de continuar" });
            btnRegistrar.disabled = false;
            return;
        }
    }

    const body = new FormData(formUsuario);
    const url = usuarioEditando ? '/empresa_celulares/usuario/actualizarAPI' : '/empresa_celulares/usuario/guardarAPI';
    
    if (usuarioEditando) {
        body.append('usuario_id', usuarioEditando);
    }

    console.log('Enviando a URL:', url);
    console.log('Usuario ID:', usuarioEditando);

    try {
        const respuesta = await fetch(url, { method: 'POST', body });
        const data = await respuesta.json();
        
        console.log('Respuesta del servidor:', data);
        
        if (data.codigo == 1) {
            Swal.fire({ icon: "success", title: usuarioEditando ? "Usuario actualizado" : "Usuario registrado", text: data.mensaje });
            limpiarTodo();
            buscarUsuarios();
        } else {
            Swal.fire({ icon: "error", title: "Error", text: data.mensaje });
        }
    } catch (error) {
        console.error('Error en guardarUsuario:', error);
        Swal.fire({ icon: "error", title: "Error", text: "Error de conexión" });
    }
    
    btnRegistrar.disabled = false;
};

// BUSCAR USUARIOS
const buscarUsuarios = async () => {
    try {
        const res = await fetch('/empresa_celulares/usuario/buscarAPI');
        const data = await res.json();
        
        if (data.codigo == 1) {
            if (datatable) {
                datatable.clear().draw();
                datatable.rows.add(data.data).draw();
            }
            console.log('Usuarios cargados:', data.data);
        } else {
            console.error('Error buscando usuarios:', data.mensaje);
            Swal.fire({ icon: "info", title: "Error", text: data.mensaje });
        }
    } catch (error) {
        console.error('Error en buscarUsuarios:', error);
        Swal.fire({ icon: "error", title: "Error", text: "Error al cargar usuarios" });
    }
};

// INICIALIZAR DATATABLE
const inicializarTabla = () => {
    const tablaElement = document.getElementById('tablaUsuarios');
    if (!tablaElement) {
        console.error('No se encontró el elemento tablaUsuarios');
        return;
    }

    try {
        datatable = new DataTable('#tablaUsuarios', {
            language: lenguaje,
            data: [],
            columns: [
                { title: "ID", data: "usuario_id", render: (data, type, row, meta) => meta.row + 1 },
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
                    render: (data, type, row) => `${row.usuario_nom1} ${row.usuario_nom2 || ''} ${row.usuario_ape1} ${row.usuario_ape2 || ''}`.trim()
                },
                { title: "DPI", data: "usuario_dpi" },
                { title: "Teléfono", data: "usuario_tel" },
                { title: "Correo", data: "usuario_correo" },
                { 
                    title: "Rol", 
                    data: "rol_nombre",
                    render: (data) => data || 'Sin rol'
                },
                {
                    title: "Acciones", 
                    data: "usuario_id",
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

        // Event listeners para los botones de tabla
        datatable.on('click', '.modificar', llenarFormulario);
        datatable.on('click', '.eliminar', eliminarUsuario);
        
        console.log('DataTable inicializada correctamente');
    } catch (error) {
        console.error('Error inicializando DataTable:', error);
    }
};

// LLENAR FORMULARIO PARA EDITAR
const llenarFormulario = (e) => {
    try {
        const datos = JSON.parse(e.currentTarget.dataset.json);
        
        document.getElementById('usuario_nom1').value = datos.usuario_nom1 || '';
        document.getElementById('usuario_nom2').value = datos.usuario_nom2 || '';
        document.getElementById('usuario_ape1').value = datos.usuario_ape1 || '';
        document.getElementById('usuario_ape2').value = datos.usuario_ape2 || '';
        document.getElementById('usuario_tel').value = datos.usuario_tel || '';
        document.getElementById('usuario_direc').value = datos.usuario_direc || '';
        document.getElementById('usuario_correo').value = datos.usuario_correo || '';
        document.getElementById('usuario_dpi').value = datos.usuario_dpi || '';
        document.getElementById('rol_id').value = datos.rol_id || '';

        // En modo edición, hacer los campos de contraseña opcionales
        const passwordSection = document.getElementById('password-section');
        const contraInput = document.getElementById('usuario_contra');
        const confirmarInput = document.getElementById('confirmar_contra');
        
        if (passwordSection && contraInput && confirmarInput) {
            // Mostrar sección pero hacer campos opcionales
            passwordSection.style.display = 'block';
            contraInput.required = false;
            confirmarInput.required = false;
            contraInput.placeholder = 'Dejar vacío para mantener contraseña actual';
            confirmarInput.placeholder = 'Dejar vacío para mantener contraseña actual';
        }
        
        usuarioEditando = datos.usuario_id;
        
        if (btnRegistrar) {
            btnRegistrar.textContent = 'Actualizar Usuario';
            btnRegistrar.className = 'btn btn-warning px-4'; // Cambiar color a warning
        }
        
        window.scrollTo({ top: 0, behavior: 'smooth' });
        
        console.log('Formulario llenado para editar usuario ID:', datos.usuario_id);
    } catch (error) {
        console.error('Error llenando formulario:', error);
        Swal.fire('Error', 'Error al cargar datos del usuario', 'error');
    }
};

// LIMPIAR TODO
const limpiarTodo = () => {
    if (formUsuario) {
        formUsuario.reset();
    }
    
    usuarioEditando = null;
    
    if (btnRegistrar) {
        btnRegistrar.textContent = 'Registrar Usuario';
        btnRegistrar.className = 'btn btn-success px-4'; // Volver al color verde
    }
    
    // Mostrar sección de contraseñas y hacerlas requeridas
    const passwordSection = document.getElementById('password-section');
    const contraInput = document.getElementById('usuario_contra');
    const confirmarInput = document.getElementById('confirmar_contra');
    
    if (passwordSection && contraInput && confirmarInput) {
        passwordSection.style.display = 'block';
        contraInput.required = true;
        confirmarInput.required = true;
        contraInput.placeholder = 'Mínimo 10 caracteres';
        confirmarInput.placeholder = 'Confirme su contraseña';
    }
    
    // Limpiar validaciones
    if (formUsuario) {
        formUsuario.querySelectorAll('.is-valid, .is-invalid').forEach(el => {
            el.classList.remove('is-valid', 'is-invalid');
        });
    }
    
    // Ocultar preview de imagen
    const imagePreview = document.getElementById('imagePreview');
    if (imagePreview) {
        imagePreview.classList.add('d-none');
    }
    
    console.log('Formulario limpiado');
};

// ELIMINAR USUARIO
const eliminarUsuario = async (e) => {
    const id = e.currentTarget.dataset.id;
    const confirmar = await Swal.fire({
        icon: "warning", 
        title: "¿Eliminar usuario?", 
        text: "Esta acción desactivará el usuario.",
        showCancelButton: true, 
        confirmButtonText: "Sí, eliminar", 
        cancelButtonText: "Cancelar",
    });

    if (confirmar.isConfirmed) {
        try {
            const res = await fetch('/empresa_celulares/usuario/eliminarAPI', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ usuario_id: id })
            });
            const data = await res.json();
            if (data.codigo == 1) {
                Swal.fire({ icon: "success", title: "Eliminado", text: data.mensaje });
                buscarUsuarios();
            } else {
                Swal.fire({ icon: "error", title: "Error", text: data.mensaje });
            }
        } catch (error) {
            console.error('Error eliminando usuario:', error);
            Swal.fire('Error', 'Error al eliminar usuario', 'error');
        }
    }
};

// EVENT LISTENERS PRINCIPALES
document.addEventListener('DOMContentLoaded', () => {
    console.log('DOM cargado, inicializando módulo usuario...');
    
    // Inicializar componentes
    inicializarTabla();
    cargarRoles();
    buscarUsuarios();
    
    // Event listeners del formulario
    if (formUsuario) {
        formUsuario.addEventListener('submit', guardarUsuario);
        console.log('Event listener del formulario agregado');
    } else {
        console.error('Formulario userForm no encontrado');
    }
    
    if (btnLimpiar) {
        btnLimpiar.addEventListener('click', limpiarTodo);
    }
    
    // Event listener para toggle password
    const toggleBtn = document.getElementById('togglePassword');
    if (toggleBtn) {
        toggleBtn.addEventListener('click', togglePassword);
    }
    
    // Event listeners para validación de contraseña
    const contraInput = document.getElementById('usuario_contra');
    const confirmarInput = document.getElementById('confirmar_contra');
    if (contraInput) contraInput.addEventListener('input', validarContraseña);
    if (confirmarInput) confirmarInput.addEventListener('input', validarContraseña);
    
    // Event listener para preview de imagen
    const fotoInput = document.getElementById('usuario_fotografia');
    if (fotoInput) fotoInput.addEventListener('change', previsualizarImagen);
    
    console.log('Módulo usuario inicializado correctamente');
});