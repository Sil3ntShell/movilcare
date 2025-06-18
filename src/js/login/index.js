import Swal from 'sweetalert2';

const FormLogin = document.getElementById('FormLogin');

const login = async (e) => {
    e.preventDefault();
    e.stopPropagation();

    // Obtener elementos
    const btnLogin = document.getElementById('BtnLogin');
    const correoInput = document.getElementById('usuario_correo');
    const passwordInput = document.getElementById('usuario_contra');

    if (!correoInput || !passwordInput) {
        return;
    }

    // Deshabilitar botón
    if (btnLogin) {
        btnLogin.disabled = true;
        btnLogin.innerHTML = '<i class="spinner-border spinner-border-sm me-2"></i>Iniciando...';
    }

    // Validación simple
    const correo = correoInput.value.trim();
    const password = passwordInput.value.trim();

    if (!correo || !password) {
        Swal.fire({
            title: "Campos vacíos",
            text: "Debe llenar todos los campos",
            icon: "info"
        });
        
        if (btnLogin) {
            btnLogin.disabled = false;
            btnLogin.innerHTML = '<i class="bi bi-box-arrow-in-right me-2"></i>Iniciar Sesión';
        }
        return;
    }

    try {
        const body = new FormData(FormLogin);
        const url = '/empresa_celulares/login/iniciar';

        const respuesta = await fetch(url, {
            method: 'POST',
            body
        });

        if (!respuesta.ok) {
            throw new Error(`HTTP error! status: ${respuesta.status}`);
        }

        const contentType = respuesta.headers.get("content-type");
        if (!contentType || !contentType.includes("application/json")) {
            const textResponse = await respuesta.text();
            throw new Error("La respuesta del servidor no es JSON válido");
        }

        const data = await respuesta.json();
        const { codigo, mensaje, redirect_url } = data;

        if (codigo == 1) {
            await Swal.fire({
                title: 'Bienvenido',
                text: mensaje,
                icon: 'success',
                timer: 1000,
                showConfirmButton: false
            });

            FormLogin.reset();
            const destino = redirect_url ?? '/empresa_celulares/';
            window.location.replace(destino);

        } else {
            Swal.fire({
                title: '¡Error!',
                text: mensaje,
                icon: 'warning',
                showConfirmButton: true
            });
        }

    } catch (error) {
        Swal.fire({
            title: '¡Error de conexión!',
            text: 'No se pudo conectar con el servidor: ' + error.message,
            icon: 'error',
            showConfirmButton: true
        });
    }

    // Rehabilitar botón
    if (btnLogin) {
        btnLogin.disabled = false;
        btnLogin.innerHTML = '<i class="bi bi-box-arrow-in-right me-2"></i>Iniciar Sesión';
    }
}

// Evento cuando el DOM está listo
document.addEventListener('DOMContentLoaded', () => {
    if (FormLogin) {
        FormLogin.addEventListener('submit', login);
    }
});

// Fallback si el FormLogin existe al cargar el script
if (FormLogin) {
    FormLogin.addEventListener('submit', login);
}