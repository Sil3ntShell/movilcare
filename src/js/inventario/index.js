// inventario/index.js
import Swal from "sweetalert2";
import DataTable from "datatables.net-bs5";
import { validarFormulario } from "../funciones";
import { lenguaje } from "../lenguaje";


// Elementos del DOM
const FormularioInventario = document.getElementById('FormularioInventario');
const BtnGuardar = document.getElementById('BtnGuardar');
const BtnModificar = document.getElementById('BtnModificar');
const BtnLimpiar = document.getElementById('BtnLimpiar');
const modelo_id = document.getElementById('modelo_id');
const inventario_numero_serie = document.getElementById('inventario_numero_serie');
const inventario_imei = document.getElementById('inventario_imei');
const inventario_precio_compra = document.getElementById('inventario_precio_compra');
const inventario_precio_venta = document.getElementById('inventario_precio_venta');
const inventario_stock_disponible = document.getElementById('inventario_stock_disponible');

// Validar Número de Serie
const ValidarNumeroSerie = () => {
    const serie = inventario_numero_serie.value.trim();
    if (serie.length >= 5) {
        inventario_numero_serie.classList.add('is-valid');
        inventario_numero_serie.classList.remove('is-invalid');
    } else if (serie.length > 0) {
        inventario_numero_serie.classList.add('is-invalid');
        inventario_numero_serie.classList.remove('is-valid');
        Swal.fire({ 
            icon: "error", 
            title: "Número de serie inválido", 
            text: "Debe tener al menos 5 caracteres" 
        });
    } else {
        inventario_numero_serie.classList.remove('is-valid', 'is-invalid');
    }
};

// Validar IMEI
const ValidarIMEI = () => {
    const imei = inventario_imei.value.trim();
    // Validar que solo contenga números
    if (!/^\d*$/.test(imei)) {
        inventario_imei.classList.add('is-invalid');
        inventario_imei.classList.remove('is-valid');
        Swal.fire({ 
            icon: "error", 
            title: "IMEI inválido", 
            text: "El IMEI debe contener solo números" 
        });
        return;
    }
    
    if (imei.length === 15) {
        inventario_imei.classList.add('is-valid');
        inventario_imei.classList.remove('is-invalid');
    } else if (imei.length > 0) {
        inventario_imei.classList.add('is-invalid');
        inventario_imei.classList.remove('is-valid');
        if (imei.length < 15) {
            Swal.fire({ 
                icon: "error", 
                title: "IMEI incompleto", 
                text: `Debe tener exactamente 15 dígitos. Faltan ${15 - imei.length} dígitos.` 
            });
        } else {
            Swal.fire({ 
                icon: "error", 
                title: "IMEI demasiado largo", 
                text: `Debe tener exactamente 15 dígitos. Sobran ${imei.length - 15} dígitos.` 
            });
        }
    } else {
        inventario_imei.classList.remove('is-valid', 'is-invalid');
    }
};

// Validar Precios
const ValidarPrecio = (campo, nombre) => {
    const precio = parseFloat(campo.value);
    if (!isNaN(precio) && precio > 0) {
        campo.classList.add('is-valid');
        campo.classList.remove('is-invalid');
    } else if (campo.value !== '') {
        campo.classList.add('is-invalid');
        campo.classList.remove('is-valid');
        Swal.fire({ 
            icon: "error", 
            title: `${nombre} inválido`, 
            text: `El ${nombre.toLowerCase()} debe ser un número mayor a 0` 
        });
    } else {
        campo.classList.remove('is-valid', 'is-invalid');
    }
};

// Validar Stock
const ValidarStock = () => {
    const stock = parseInt(inventario_stock_disponible.value);
    if (!isNaN(stock) && stock >= 0) {
        inventario_stock_disponible.classList.add('is-valid');
        inventario_stock_disponible.classList.remove('is-invalid');
    } else if (inventario_stock_disponible.value !== '') {
        inventario_stock_disponible.classList.add('is-invalid');
        inventario_stock_disponible.classList.remove('is-valid');
        Swal.fire({ 
            icon: "error", 
            title: "Stock inválido", 
            text: "El stock debe ser un número entero mayor o igual a 0" 
        });
    } else {
        inventario_stock_disponible.classList.remove('is-valid', 'is-invalid');
    }
};

// Validar Modelo seleccionado
const ValidarModelo = () => {
    if (modelo_id.value && modelo_id.value !== '') {
        modelo_id.classList.add('is-valid');
        modelo_id.classList.remove('is-invalid');
    } else {
        modelo_id.classList.add('is-invalid');
        modelo_id.classList.remove('is-valid');
    }
};

// Cargar Modelos en el select
const CargarModelos = async () => {
    const url = '/empresa_celulares/inventario/obtenerModelosAPI';
    try {
        const res = await fetch(url);
        const { codigo, data } = await res.json();
        if (codigo == 1) {
            modelo_id.innerHTML = '<option value="">Seleccione un modelo...</option>';
            data.forEach(modelo => {
                modelo_id.innerHTML += `<option value="${modelo.modelo_id}">${modelo.marca_nombre} - ${modelo.modelo_nombre}</option>`;
            });
        }
    } catch (error) {
        console.error(error);
        Swal.fire({ 
            icon: "error", 
            title: "Error", 
            text: "Error al cargar los modelos" 
        });
    }
};

// Guardar Inventario
const GuardarInventario = async (event) => {
    event.preventDefault();
    BtnGuardar.disabled = true;

    if (!validarFormulario(FormularioInventario, ['inventario_id'])) {
        Swal.fire({ 
            icon: "info", 
            title: "Formulario incompleto", 
            text: "Debe completar todos los campos requeridos" 
        });
        BtnGuardar.disabled = false;
        return;
    }

    // Validar campos específicos
    const camposInvalidos = FormularioInventario.querySelectorAll('.is-invalid');
    if (camposInvalidos.length > 0) {
        Swal.fire({ 
            icon: "error", 
            title: "Datos inválidos", 
            text: "Debe corregir los errores antes de continuar" 
        });
        BtnGuardar.disabled = false;
        return;
    }

    const body = new FormData(FormularioInventario);
    const url = '/empresa_celulares/inventario/guardarAPI';

    try {
        const respuesta = await fetch(url, { method: 'POST', body });
        const { codigo, mensaje } = await respuesta.json();
        if (codigo == 1) {
            Swal.fire({ 
                icon: "success", 
                title: "Producto agregado", 
                text: mensaje 
            });
            limpiarTodo();
            BuscarInventario();
        } else {
            Swal.fire({ 
                icon: "info", 
                title: "Error", 
                text: mensaje 
            });
        }
    } catch (error) {
        console.error(error);
        Swal.fire({ 
            icon: "error", 
            title: "Error", 
            text: "Ocurrió un error al procesar la solicitud" 
        });
    }
    BtnGuardar.disabled = false;
};

// Buscar Inventario
const BuscarInventario = async () => {
    const url = '/empresa_celulares/inventario/buscarAPI';
    try {
        const res = await fetch(url);
        const { codigo, mensaje, data } = await res.json();
        if (codigo == 1) {
            datatable.clear().draw();
            datatable.rows.add(data).draw();
        } else {
            Swal.fire({ 
                icon: "info", 
                title: "Error", 
                text: mensaje 
            });
        }
    } catch (error) {
        console.error(error);
        Swal.fire({ 
            icon: "error", 
            title: "Error", 
            text: "Error al cargar el inventario" 
        });
    }
};

// DataTable Configuración
const datatable = new DataTable('#TableInventario', {
    language: lenguaje,
    data: [],
    columns: [
        { 
            title: "No.", 
            data: "inventario_id", 
            render: (data, type, row, meta) => meta.row + 1 
        },
        { 
            title: "Marca", 
            data: "marca_nombre" 
        },
        { 
            title: "Modelo", 
            data: "modelo_nombre" 
        },
        { 
            title: "No. Serie", 
            data: "inventario_numero_serie" 
        },
        { 
            title: "IMEI", 
            data: "inventario_imei" 
        },
        { 
            title: "Estado", 
            data: "inventario_estado",
            render: (data) => {
                const colores = {
                    'Disponible': 'success',
                    'Vendido': 'info',
                    'Dañado': 'danger',
                    'En reparación': 'warning'
                };
                const color = colores[data] || 'secondary';
                return `<span class="badge bg-${color}">${data}</span>`;
            }
        },
        { 
            title: "Precio Compra", 
            data: "inventario_precio_compra",
            render: (data) => `Q${parseFloat(data).toFixed(2)}`
        },
        { 
            title: "Precio Venta", 
            data: "inventario_precio_venta",
            render: (data) => `Q${parseFloat(data).toFixed(2)}`
        },
        { 
            title: "Stock", 
            data: "inventario_stock_disponible",
            render: (data) => {
                const color = data > 0 ? 'success' : 'danger';
                return `<span class="badge bg-${color}">${data}</span>`;
            }
        },
        { 
            title: "Ubicación", 
            data: "inventario_ubicacion" 
        },
        { 
            title: "Fecha Ingreso", 
            data: "inventario_fecha_ingreso",
            render: (data) => {
                if (data) {
                    const fecha = new Date(data);
                    return fecha.toLocaleDateString('es-GT');
                }
                return '';
            }
        },
        {
            title: "Acciones", 
            data: "inventario_id",
            render: (id, type, row) => `
                <div class="d-flex justify-content-center gap-1">
                    <button class="btn btn-warning btn-sm modificar" 
                            data-id="${id}" 
                            data-json='${JSON.stringify(row)}'
                            title="Modificar">
                        ✏️
                    </button>
                    <button class="btn btn-danger btn-sm eliminar" 
                            data-id="${id}"
                            title="Eliminar">
                        🗑️
                    </button>
                </div>
            `
        }
    ],
    scrollX: true
});

// Llenar formulario para modificar
const llenarFormulario = (e) => {
    const datos = JSON.parse(e.currentTarget.dataset.json);
    for (let key in datos) {
        const input = document.getElementById(key);
        if (input) input.value = datos[key];
    }
    BtnGuardar.classList.add('d-none');
    BtnModificar.classList.remove('d-none');
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

// Limpiar todo el formulario
const limpiarTodo = () => {
    FormularioInventario.reset();
    BtnGuardar.classList.remove('d-none');
    BtnModificar.classList.add('d-none');
    FormularioInventario.querySelectorAll('.is-valid, .is-invalid').forEach(el => {
        el.classList.remove('is-valid', 'is-invalid');
    });
};

// Modificar Inventario
const ModificarInventario = async (event) => {
    event.preventDefault();
    BtnModificar.disabled = true;

    if (!validarFormulario(FormularioInventario, ['inventario_id'])) {
        Swal.fire({ 
            icon: "info", 
            title: "Formulario incompleto", 
            text: "Complete todos los campos" 
        });
        BtnModificar.disabled = false;
        return;
    }

    // Validar campos específicos
    const camposInvalidos = FormularioInventario.querySelectorAll('.is-invalid');
    if (camposInvalidos.length > 0) {
        Swal.fire({ 
            icon: "error", 
            title: "Datos inválidos", 
            text: "Debe corregir los errores antes de continuar" 
        });
        BtnModificar.disabled = false;
        return;
    }

    const body = new FormData(FormularioInventario);
    const url = '/empresa_celulares/inventario/modificarAPI';

    try {
        const respuesta = await fetch(url, { method: 'POST', body });
        const { codigo, mensaje } = await respuesta.json();
        if (codigo == 1) {
            Swal.fire({ 
                icon: "success", 
                title: "Producto modificado", 
                text: mensaje 
            });
            limpiarTodo();
            BuscarInventario();
        } else {
            Swal.fire({ 
                icon: "error", 
                title: "Error", 
                text: mensaje 
            });
        }
    } catch (error) {
        console.error(error);
        Swal.fire({ 
            icon: "error", 
            title: "Error", 
            text: "Ocurrió un error al procesar la solicitud" 
        });
    }
    BtnModificar.disabled = false;
};

// Eliminar Inventario
const EliminarInventario = async (e) => {
    const id = e.currentTarget.dataset.id;
    const confirmar = await Swal.fire({
        icon: "warning", 
        title: "¿Eliminar producto del inventario?", 
        text: "Esta acción no se puede deshacer.",
        showCancelButton: true, 
        confirmButtonText: "Sí, eliminar", 
        cancelButtonText: "Cancelar",
        confirmButtonColor: "#d33",
        cancelButtonColor: "#6c757d"
    });

    if (confirmar.isConfirmed) {
        const url = `/empresa_celulares/inventario/eliminar?id=${id}`;
        try {
            const res = await fetch(url);
            const { codigo, mensaje } = await res.json();
            if (codigo == 1) {
                Swal.fire({ 
                    icon: "success", 
                    title: "Eliminado", 
                    text: mensaje 
                });
                BuscarInventario();
            } else {
                Swal.fire({ 
                    icon: "error", 
                    title: "Error", 
                    text: mensaje 
                });
            }
        } catch (error) {
            console.error(error);
            Swal.fire({ 
                icon: "error", 
                title: "Error", 
                text: "Error al eliminar el producto" 
            });
        }
    }
};

// Eventos del DOM
document.addEventListener('DOMContentLoaded', () => {
    CargarModelos();
    BuscarInventario();
    
    // Validaciones en tiempo real
    inventario_numero_serie.addEventListener('change', ValidarNumeroSerie);
    inventario_imei.addEventListener('change', ValidarIMEI);
    inventario_precio_compra.addEventListener('change', () => ValidarPrecio(inventario_precio_compra, 'Precio de compra'));
    inventario_precio_venta.addEventListener('change', () => ValidarPrecio(inventario_precio_venta, 'Precio de venta'));
    inventario_stock_disponible.addEventListener('change', ValidarStock);
    modelo_id.addEventListener('change', ValidarModelo);
    
    // Eventos de formulario
    FormularioInventario.addEventListener('submit', GuardarInventario);
    BtnModificar.addEventListener('click', ModificarInventario);
    BtnLimpiar.addEventListener('click', limpiarTodo);
    
    // Eventos de tabla
    datatable.on('click', '.modificar', llenarFormulario);
    datatable.on('click', '.eliminar', EliminarInventario);
});