// usuario/index.js
import Swal from "sweetalert2";
import DataTable from "datatables.net-bs5";
import { validarFormulario } from "../funciones";
import { lenguaje } from "../lenguaje";

// Elementos del DOM - CORREGIDOS para coincidir con el HTML
const FormularioUsuarios = document.getElementById('userForm'); // Cambiado de FormularioUsuarios
const BtnGuardar = document.getElementById('btnRegistrar'); // Cambiado de BtnGuardar
const BtnModificar = document.getElementById('btnModificar'); // Cambiado de BtnModificar
const BtnLimpiar = document.getElementById('btnLimpiar'); // Cambiado de BtnLimpiar
const usuario_tel = document.getElementById('usuario_tel');
const usuario_dpi = document.getElementById('usuario_dpi');
const usuario_contra = document.getElementById('usuario_contra');
const confirmar_contra = document.getElementById('confirmar_contra');

// Validar Teléfono
const ValidarTelefono = () => {
    const numero = usuario_tel.value.trim();
    
    if (numero.length < 1) {
        usuario_tel.classList.remove('is-valid', 'is-invalid');
        return true;
    } else {
        if (numero.length !== 8) {
            usuario_tel.classList.add('is-invalid');
            usuario_tel.classList.remove('is-valid');
            Swal.fire({
                position: "center",
                icon: "warning",
                title: "Teléfono incorrecto",
                text: "Debe tener exactamente 8 dígitos",
                showConfirmButton: true,
            });
            return false;
        } else {
            usuario_tel.classList.remove('is-invalid');
            usuario_tel.classList.add('is-valid');
            return true;
        }
    }
};

// Validar DPI
const ValidarDPI = () => {
    const numeroDPI = usuario_dpi.value.trim();
    
    if (numeroDPI.length < 1) {
        usuario_dpi.classList.remove('is-valid', 'is-invalid');
        return true;
    } else {
        if (!/^\d+$/.test(numeroDPI)) {
            usuario_dpi.classList.add('is-invalid');
            usuario_dpi.classList.remove('is-valid');
            Swal.fire({ 
                icon: "error", 
                title: "DPI inválido", 
                text: "El DPI debe contener solo números" 
            });
            return false;
        }
        
        if (numeroDPI.length !== 13) {
            usuario_dpi.classList.add('is-invalid');
            usuario_dpi.classList.remove('is-valid');
            
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
            usuario_dpi.classList.remove('is-invalid');
            usuario_dpi.classList.add('is-valid');
            return true;
        }
    }
};

// Validar Contraseña Segura
const validarContrasenaSegura = () => {
    const password = usuario_contra.value;
    let errores = [];
    
    if (password.length < 1) {
        usuario_contra.classList.remove('is-valid', 'is-invalid');
        usuario_contra.title = '';
        return true;
    }
    
    if (password.length < 10) errores.push("Mínimo 10 caracteres");
    if (!/[A-Z]/.test(password)) errores.push("Al menos una mayúscula");
    if (!/[a-z]/.test(password)) errores.push("Al menos una minúscula");
    if (!/[0-9]/.test(password)) errores.push("Al menos un número");
    if (!/[!@#$%^&*()_+\-=\[\]{};':\"\\|,.<>\/?]/.test(password)) errores.push("Al menos un carácter especial");
    
    if (errores.length > 0) {
        usuario_contra.classList.add('is-invalid');
        usuario_contra.classList.remove('is-valid');
        usuario_contra.title = "Falta: " + errores.join(", ");
        return false;
    } else {
        usuario_contra.classList.remove('is-invalid');
        usuario_contra.classList.add('is-valid');
        usuario_contra.title = "Contraseña segura ✓";
        return true;
    }
};

// Validar Confirmación de Contraseña
const validarConfirmarContrasena = () => {
    if (confirmar_contra.value.length < 1) {
        confirmar_contra.classList.remove('is-valid', 'is-invalid');
        return true;
    }

    if (usuario_contra.value !== confirmar_contra.value) {
        confirmar_contra.classList.add('is-invalid');
        confirmar_contra.classList.remove('is-valid');
        Swal.fire({
            position: "center",
            icon: "error",
            title: "Error",
            text: "Las contraseñas no coinciden",
            showConfirmButton: true,
        });
        return false;
    } else {
        confirmar_contra.classList.remove('is-invalid');
        confirmar_contra.classList.add('is-valid');
        return true;
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

// Función para limpiar formulario
const limpiarTodo = () => {
    FormularioUsuarios.reset();
    BtnGuardar.classList.remove('d-none');
    BtnModificar.classList.add('d-none');
    FormularioUsuarios.querySelectorAll('.form-control, .form-select').forEach(element => {
        element.classList.remove('is-valid', 'is-invalid');
        element.title = '';
    });
    
    const imagePreview = document.getElementById('imagePreview');
    if (imagePreview) {
        imagePreview.classList.add('d-none');
    }
    
    if (usuario_contra && confirmar_contra) {
        usuario_contra.required = true;
        confirmar_contra.required = true;
        usuario_contra.placeholder = 'Mínimo 10 caracteres';
        confirmar_contra.placeholder = 'Confirme su contraseña';
    }
};

// Guardar Usuario
const GuardarUsuario = async (event) => {
    event.preventDefault();
    BtnGuardar.disabled = true;

    const telefonoValido = ValidarTelefono();
    const dpiValido = ValidarDPI();
    const contrasenaValida = validarContrasenaSegura();
    const confirmarContrasenaValida = validarConfirmarContrasena();

    if (!telefonoValido || !dpiValido || !contrasenaValida || !confirmarContrasenaValida) {
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

    const body = new FormData(FormularioUsuarios);
    const url = '/empresa_celulares/usuario/guardarAPI';
    const config = {
        method: 'POST',
        body
    }

    try {
        const respuesta = await fetch(url, config);
        const datos = await respuesta.json();
        const { codigo, mensaje } = datos

        if (codigo == 1) {
            await Swal.fire({
                position: "center",
                icon: "success",
                title: "¡Éxito!",
                text: mensaje,
                showConfirmButton: true,
            });

            limpiarTodo();
            await BuscarUsuarios();

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
        console.log(error)
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

// Buscar Usuarios
const BuscarUsuarios = async () => {
    const url = '/empresa_celulares/usuario/buscarAPI';
    const config = {
        method: 'GET'
    }

    try {
        const respuesta = await fetch(url, config);
        const datos = await respuesta.json();
        const { codigo, mensaje, data } = datos

        if (codigo == 1) {
            await Swal.fire({
                position: "center",
                icon: "success",
                title: "¡Usuarios cargados!",
                text: `Se cargaron ${data.length} usuario(s) correctamente`,
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
        console.log('Error en BuscarUsuarios:', error)
        await Swal.fire({
            position: "center",
            icon: "error",
            title: "Error de conexión",
            text: "No se pudieron cargar los usuarios",
            showConfirmButton: true,
        });
    }
};

// Llenar formulario para modificar
const llenarFormulario = async (event) => {
    const datos = event.currentTarget.dataset;
    const usuarioId = datos.id;

    const url = `/empresa_celulares/usuario/buscarAPI?id=${usuarioId}`;
    const config = {
        method: 'GET'
    }

    try {
        const respuesta = await fetch(url, config);
        const resultado = await respuesta.json();
        const { codigo, mensaje, data } = resultado;

        if (codigo == 1 && data.length > 0) {
            const usuario = data[0];
            
            document.getElementById('usuario_id').value = usuarioId;
            document.getElementById('usuario_nom1').value = usuario.usuario_nom1;
            document.getElementById('usuario_nom2').value = usuario.usuario_nom2;
            document.getElementById('usuario_ape1').value = usuario.usuario_ape1;
            document.getElementById('usuario_ape2').value = usuario.usuario_ape2;
            document.getElementById('usuario_tel').value = usuario.usuario_tel;
            document.getElementById('usuario_direc').value = usuario.usuario_direc;
            document.getElementById('usuario_dpi').value = usuario.usuario_dpi;
            document.getElementById('usuario_correo').value = usuario.usuario_correo;
            
            document.getElementById('usuario_contra').value = '';
            document.getElementById('confirmar_contra').value = '';
            
            if (usuario_contra && confirmar_contra) {
                usuario_contra.required = false;
                confirmar_contra.required = false;
                usuario_contra.placeholder = 'Dejar vacío para mantener contraseña actual';
                confirmar_contra.placeholder = 'Dejar vacío para mantener contraseña actual';
            }
            
            BtnGuardar.classList.add('d-none');
            BtnModificar.classList.remove('d-none');
            
            window.scrollTo({
                top: 0
            });

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
        console.log('Error completo:', error);
        
        await Swal.fire({
            position: "center",
            icon: "error",
            title: "Error de conexión",
            text: "No se pudo cargar el usuario",
            showConfirmButton: true,
        });
    }
};

// Modificar Usuario
const ModificarUsuario = async (event) => {
    event.preventDefault();
    BtnModificar.disabled = true;

    const body = new FormData(FormularioUsuarios);
    const url = '/empresa_celulares/usuario/modificarAPI';
    const config = {
        method: 'POST',
        body
    }

    try {
        const respuesta = await fetch(url, config);
        const datos = await respuesta.json();
        const { codigo, mensaje } = datos

        if (codigo == 1) {
            await Swal.fire({
                position: "center",
                icon: "success",
                title: "¡Éxito!",
                text: mensaje,
                showConfirmButton: true,
            });

            limpiarTodo();
            await BuscarUsuarios();

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
        console.log(error)
        await Swal.fire({
            position: "center",
            icon: "error",
            title: "Error de conexión",
            text: "No se pudo completar la modificación",
            showConfirmButton: true,
        });
    }
    BtnModificar.disabled = false;
};

// Eliminar Usuario
const EliminarUsuarios = async (e) => {
    const idUsuario = e.currentTarget.dataset.id

    const AlertaConfirmarEliminar = await Swal.fire({
        position: "center",
        icon: "question",
        title: "¿Desea ejecutar esta acción?",
        text: 'Está completamente seguro que desea eliminar este registro',
        showConfirmButton: true,
        confirmButtonText: 'Sí, Eliminar',
        confirmButtonColor: '#d33',
        cancelButtonText: 'No, Cancelar',
        showCancelButton: true
    });

    if (AlertaConfirmarEliminar.isConfirmed) {
        const url = `/empresa_celulares/usuario/eliminar?id=${idUsuario}`;
        const config = {
            method: 'GET'
        }

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
                
                await BuscarUsuarios();
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
            console.log(error)
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

// Configurar DataTable
const datatable = new DataTable('#TableUsuarios', {
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
            title: 'No.',
            data: 'usuario_id',
            width: '%',
            render: (data, type, row, meta) => meta.row + 1
        },
        { 
            title: 'Nombres', 
            data: 'usuario_nom1'
        },
        { 
            title: 'Apellidos', 
            data: 'usuario_ape1'
        },
        { 
            title: 'Teléfono', 
            data: 'usuario_tel' 
        },
        { 
            title: 'DPI', 
            data: 'usuario_dpi' 
        },
        { 
            title: 'Correo', 
            data: 'usuario_correo' 
        },
        { 
            title: 'Foto', 
            data: 'usuario_fotografia',
            searchable: false,
            orderable: false,
            render: (data, type, row) => {
                if (data && data.trim() !== '') {
                    // Crear la URL completa de la imagen
                    const imageUrl = `/empresa_celulares/storage/fotos_usuarios/${data}`;
                    return `<img src="${imageUrl}" alt="Foto de usuario" style="height: 50px; width: 50px; border-radius: 50%; object-fit: cover;" 
                            onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNTAiIGhlaWdodD0iNTAiIHZpZXdCb3g9IjAgMCA1MCA1MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPGNpcmNsZSBjeD0iMjUiIGN5PSIyNSIgcj0iMjUiIGZpbGw9IiM2Yzc1N2QiLz4KPGNpcmNsZSBjeD0iMjUiIGN5PSIyMCIgcj0iNSIgZmlsbD0iI2ZmZmZmZiIvPgo8cGF0aCBkPSJNMzUgMzVBMTAgMTAgMCAwIDAgMTUgMzUiIGZpbGw9IiNmZmZmZmYiLz4KPC9zdmc+Cg==';">`;
                } else {
                    return `<i class="bi bi-person-fill text-muted" style="font-size: 30px;"></i>`;
                }
            }
        },
        {
            title: 'Acciones',
            data: 'usuario_id',
            searchable: false,
            orderable: false,
            render: (data, type, row, meta) => {
                return `
                 <div class='d-flex justify-content-center'>
                     <button class='btn btn-warning modificar mx-1' 
                         data-id="${data}">   
                         <i class='bi bi-pencil-square me-1'></i> Modificar
                     </button>
                     <button class='btn btn-danger eliminar mx-1' 
                         data-id="${data}">
                        <i class="bi bi-trash3 me-1"></i>Eliminar
                     </button>
                 </div>`;
            }
        }
    ]
});

// Eventos
document.addEventListener('DOMContentLoaded', () => {

    BuscarUsuarios();
    
    // Validaciones
    if (usuario_tel) usuario_tel.addEventListener('change', ValidarTelefono);
    if (usuario_dpi) usuario_dpi.addEventListener('change', ValidarDPI);
    if (usuario_contra) usuario_contra.addEventListener('input', validarContrasenaSegura);
    if (confirmar_contra) confirmar_contra.addEventListener('change', validarConfirmarContrasena);
    
    // Preview de imagen
    const fotoInput = document.getElementById('usuario_fotografia');
    if (fotoInput) fotoInput.addEventListener('change', previsualizarImagen);
    
    // Eventos de formulario
    if (FormularioUsuarios) FormularioUsuarios.addEventListener('submit', GuardarUsuario);
    if (BtnModificar) BtnModificar.addEventListener('click', ModificarUsuario);
    if (BtnLimpiar) BtnLimpiar.addEventListener('click', limpiarTodo);
    
    // Eventos de DataTable
    datatable.on('click', '.modificar', llenarFormulario);
    datatable.on('click', '.eliminar', EliminarUsuarios);
    
    console.log('Aplicación de usuarios inicializada correctamente');
});