import Swal from "sweetalert2";

// Elementos del DOM
const formularioLogin = document.getElementById('formularioLogin');
const usuCodigo = document.getElementById('usu_codigo');
const usuPassword = document.getElementById('usu_password');
const btnLogin = document.getElementById('btnLogin');

// Función para validar DPI guatemalteco
const validarDPI = (dpi) => {
    if (dpi.length !== 13) return false;
    if (!/^\d{13}$/.test(dpi)) return false;
    return true;
};

// Función para validar formulario
const validarFormulario = () => {
    let esValido = true;
    
    // Validar DPI
    if (!usuCodigo.value.trim()) {
        usuCodigo.classList.add('is-invalid');
        esValido = false;
    } else if (!validarDPI(usuCodigo.value.trim())) {
        usuCodigo.classList.add('is-invalid');
        Swal.fire({
            icon: 'error',
            title: 'DPI inválido',
            text: 'El DPI debe tener exactamente 13 dígitos'
        });
        esValido = false;
    } else {
        usuCodigo.classList.remove('is-invalid');
        usuCodigo.classList.add('is-valid');
    }
    
    // Validar contraseña
    if (!usuPassword.value.trim()) {
        usuPassword.classList.add('is-invalid');
        esValido = false;
    } else {
        usuPassword.classList.remove('is-invalid');
        usuPassword.classList.add('is-valid');
    }
    
    return esValido;
};

// Función para realizar login
const realizarLogin = async (event) => {
    event.preventDefault();
    
    if (!validarFormulario()) {
        return;
    }
    
    // Deshabilitar botón y mostrar loading
    btnLogin.disabled = true;
    btnLogin.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Iniciando sesión...';
    
    try {
        const formData = new FormData(formularioLogin);
        
        // RUTA CORREGIDA - Debe coincidir con las rutas definidas en tu Router
        const respuesta = await fetch('/empresa_celulares/login/loginAPI', {
            method: 'POST',
            body: formData
        });
        
        // Verificar si la respuesta es válida
        if (!respuesta.ok) {
            throw new Error(`HTTP error! status: ${respuesta.status}`);
        }
        
        const data = await respuesta.json();
        
        if (data.codigo === 1) {
            // Login exitoso
            Swal.fire({
                icon: 'success',
                title: '¡Bienvenido!',
                text: data.mensaje,
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                // Redirigir al dashboard principal
                window.location.href = '/empresa_celulares/';
            });
        } else {
            // Error en login
            Swal.fire({
                icon: 'error',
                title: 'Error de autenticación',
                text: data.mensaje
            });
        }
    } catch (error) {
        console.error('Error en login:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error de conexión',
            text: 'No se pudo conectar con el servidor. Verifique la URL o intente nuevamente.'
        });
    } finally {
        // Restaurar botón
        btnLogin.disabled = false;
        btnLogin.innerHTML = '<i class="bi bi-box-arrow-in-right me-2"></i>Iniciar Sesión';
    }
};

// Event Listeners
document.addEventListener('DOMContentLoaded', () => {
    // Submit del formulario
    if (formularioLogin) {
        formularioLogin.addEventListener('submit', realizarLogin);
    }
    
    // Validación en tiempo real del DPI
    if (usuCodigo) {
        usuCodigo.addEventListener('input', (e) => {
            // Solo permitir números
            e.target.value = e.target.value.replace(/\D/g, '');
            
            // Limitar a 13 caracteres
            if (e.target.value.length > 13) {
                e.target.value = e.target.value.slice(0, 13);
            }
            
            // Remover clases de validación mientras escribe
            e.target.classList.remove('is-valid', 'is-invalid');
        });
    }
    
    // Remover clases de validación en contraseña
    if (usuPassword) {
        usuPassword.addEventListener('input', (e) => {
            e.target.classList.remove('is-valid', 'is-invalid');
        });
    }
    
    // Enfocar el campo DPI al cargar
    if (usuCodigo) {
        usuCodigo.focus();
    }
});