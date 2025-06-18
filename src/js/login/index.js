import Swal from 'sweetalert2';
import { validarFormulario } from '../funciones';

const FormLogin = document.getElementById('FormLogin');
const BtnIniciar = document.getElementById('BtnIniciar');

const login = async (e) => {
    e.preventDefault();
    BtnIniciar.disabled = true;

    if (!validarFormulario(FormLogin, [])) {
        Swal.fire({
            title: "Campos vacíos",
            text: "Debe llenar todos los campos",
            icon: "info"
        });
        BtnIniciar.disabled = false;
        return;
    }

    try {
        const body = new FormData(FormLogin);
        const url = '/empresa_celulares/login/iniciar';

        const config = {
            method: 'POST',
            body
        };

        console.log('Enviando petición a:', url);

        const respuesta = await fetch(url, config);

        const contentType = respuesta.headers.get("content-type");
        if (!contentType || !contentType.includes("application/json")) {
            throw new Error("La respuesta del servidor no es JSON válido");
        }

        const data = await respuesta.json();
        const { codigo, mensaje, datos } = data;

        if (codigo == 1) {
            await Swal.fire({
                title: 'Bienvenido',
                text: mensaje,
                icon: 'success',
                timer: 1500,
                showConfirmButton: false
            });

            FormLogin.reset();

            // Redirigir a la URL que envía el controlador
            const destino = datos?.redirect_url ?? '/empresa_celulares/';
            location.href = destino;

        } else {
            Swal.fire({
                title: '¡Error!',
                text: mensaje,
                icon: 'warning',
                showConfirmButton: true
            });
        }

    } catch (error) {
        console.log(error);

        Swal.fire({
            title: '¡Error de conexión!',
            text: 'No se pudo conectar con el servidor',
            icon: 'error',
            showConfirmButton: true
        });
    }

    BtnIniciar.disabled = false;
}

if (FormLogin) {
    FormLogin.addEventListener('submit', login);
}
