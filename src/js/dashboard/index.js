// dashboard/index.js - Empresa de Celulares
import Swal from "sweetalert2";
import Chart from "chart.js/auto";
import L from 'leaflet';



// Variables globales para gráficos y mapa
let chartVentas = null;
let chartServicios = null;
let chartEstados = null;
let chartMarcas = null;
let mapaClientes = null;

// Elementos del DOM
const BtnActualizarDashboard = document.getElementById('BtnActualizarDashboard');
const BtnActualizarVentas = document.getElementById('BtnActualizarVentas');
const BtnActualizarServicios = document.getElementById('BtnActualizarServicios');
const BtnActualizarMapa = document.getElementById('BtnActualizarMapa');
const BtnActualizarEstados = document.getElementById('BtnActualizarEstados');
const BtnActualizarMarcas = document.getElementById('BtnActualizarMarcas');
const BtnActualizarActividad = document.getElementById('BtnActualizarActividad');

// Función para mostrar errores
const mostrarError = (mensaje) => {
    console.error('Error:', mensaje);
    Swal.fire({
        icon: 'error',
        title: 'Error',
        text: mensaje,
        confirmButtonText: 'Entendido'
    });
};

// Función para formatear números como moneda guatemalteca
const formatearMoneda = (valor) => {
    return `Q ${parseFloat(valor).toLocaleString('es-GT', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
};

// Función para formatear números enteros
const formatearNumero = (valor) => {
    return parseInt(valor).toLocaleString('es-GT');
};

// Cargar estadísticas principales
const cargarEstadisticas = async () => {
    const url = '/empresa_celulares/dashboard/estadisticasAPI';
    try {
        const respuesta = await fetch(url);
        const { codigo, mensaje, data } = await respuesta.json();
        
        if (codigo === 1) {
            document.getElementById('ventasMes').textContent = formatearNumero(data.ventas_mes);
            document.getElementById('ingresosMes').textContent = formatearMoneda(data.ingresos_mes);
            document.getElementById('clientesActivos').textContent = formatearNumero(data.clientes_activos);
            document.getElementById('serviciosCompletados').textContent = formatearNumero(data.servicios_completados);
            document.getElementById('ordenesPendientes').textContent = formatearNumero(data.ordenes_pendientes);
            document.getElementById('inventarioDisponible').textContent = formatearNumero(data.inventario_disponible);
        } else {
            mostrarError(`Error al cargar estadísticas: ${mensaje}`);
        }
    } catch (error) {
        mostrarError('Error de conexión al cargar estadísticas');
        console.error(error);
    }
};

// Cargar y crear gráfico de ventas mensuales
const cargarGraficoVentas = async () => {
    const url = '/empresa_celulares/dashboard/ventasPorMesAPI';
    try {
        const respuesta = await fetch(url);
        const { codigo, mensaje, data } = await respuesta.json();
        
        if (codigo === 1) {
            const ctx = document.getElementById('ChartVentasMensuales').getContext('2d');
            
            if (chartVentas) {
                chartVentas.destroy();
            }
            
            chartVentas = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.map(item => item.mes),
                    datasets: [
                        {
                            label: 'Ingresos (Q)',
                            data: data.map(item => item.ventas),
                            borderColor: '#007bff',
                            backgroundColor: 'rgba(0, 123, 255, 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                            yAxisID: 'y'
                        },
                        {
                            label: 'Cantidad de Ventas',
                            data: data.map(item => item.cantidad),
                            borderColor: '#28a745',
                            backgroundColor: 'rgba(40, 167, 69, 0.1)',
                            borderWidth: 3,
                            fill: false,
                            tension: 0.4,
                            yAxisID: 'y1'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.datasetIndex === 0) {
                                        label += formatearMoneda(context.parsed.y);
                                    } else {
                                        label += context.parsed.y + ' ventas';
                                    }
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            display: true,
                            title: {
                                display: true,
                                text: 'Período'
                            }
                        },
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            title: {
                                display: true,
                                text: 'Ingresos (Q)'
                            },
                            ticks: {
                                callback: function(value) {
                                    return 'Q' + value.toLocaleString();
                                }
                            }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            title: {
                                display: true,
                                text: 'Cantidad de Ventas'
                            },
                            grid: {
                                drawOnChartArea: false,
                            },
                        }
                    }
                }
            });
        } else {
            mostrarError(`Error al cargar gráfico de ventas: ${mensaje}`);
        }
    } catch (error) {
        mostrarError('Error de conexión al cargar gráfico de ventas');
        console.error(error);
    }
};

// Cargar y crear gráfico de servicios más solicitados
const cargarGraficoServicios = async () => {
    const url = '/empresa_celulares/dashboard/serviciosTopAPI';
    try {
        const respuesta = await fetch(url);
        const { codigo, mensaje, data } = await respuesta.json();
        
        if (codigo === 1) {
            const ctx = document.getElementById('ChartServiciosTop').getContext('2d');
            
            if (chartServicios) {
                chartServicios.destroy();
            }
            
            const colores = [
                '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', 
                '#9966FF', '#FF9F40', '#FF6384', '#C9CBCF'
            ];
            
            chartServicios = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: data.map(item => item.servicio.length > 15 ? item.servicio.substring(0, 15) + '...' : item.servicio),
                    datasets: [{
                        data: data.map(item => item.cantidad_ordenes),
                        backgroundColor: colores.slice(0, data.length),
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 10,
                                usePointStyle: true,
                                font: {
                                    size: 10
                                }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = ((context.parsed * 100) / total).toFixed(1);
                                    return `${context.label}: ${context.parsed} órdenes (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
        } else {
            mostrarError(`Error al cargar servicios top: ${mensaje}`);
        }
    } catch (error) {
        mostrarError('Error de conexión al cargar servicios top');
        console.error(error);
    }
};

// Inicializar mapa de clientes
const inicializarMapa = () => {
    if (mapaClientes) {
        mapaClientes.remove();
    }
    
    // Crear mapa centrado en Ciudad de Guatemala
    mapaClientes = L.map('mapaClientes').setView([14.6349, -90.5069], 12);
    
    // Agregar capa de mapa
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(mapaClientes);
    
    cargarUbicacionesClientes();
};

// Cargar ubicaciones de clientes en el mapa
const cargarUbicacionesClientes = async () => {
    const url = '/empresa_celulares/dashboard/ubicacionesClientesAPI';
    try {
        const respuesta = await fetch(url);
        const { codigo, mensaje, data } = await respuesta.json();
        
        if (codigo === 1) {
            data.forEach(ubicacion => {
                // Crear marcador personalizado
                const icon = L.divIcon({
                    className: 'custom-marker',
                    html: `<div style="background-color: #007bff; color: white; border-radius: 50%; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; font-weight: bold; border: 2px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.3);">${ubicacion.clientes}</div>`,
                    iconSize: [30, 30],
                    iconAnchor: [15, 15]
                });
                
                const marker = L.marker([ubicacion.lat, ubicacion.lng], { icon: icon }).addTo(mapaClientes);
                
                // Popup con información
                marker.bindPopup(`
                    <div class="text-center">
                        <h6 class="mb-2 text-primary"><i class="bi bi-geo-alt"></i> ${ubicacion.zona}</h6>
                        <p class="mb-1"><strong>Clientes:</strong> ${ubicacion.clientes}</p>
                        <p class="mb-0"><strong>Ventas del mes:</strong> ${ubicacion.ventas_mes}</p>
                    </div>
                `);
            });
        } else {
            mostrarError(`Error al cargar ubicaciones: ${mensaje}`);
        }
    } catch (error) {
        mostrarError('Error de conexión al cargar ubicaciones');
        console.error(error);
    }
};

// Cargar y crear gráfico de estados de dispositivos
const cargarGraficoEstados = async () => {
    const url = '/empresa_celulares/dashboard/estadosInventarioAPI';
    try {
        const respuesta = await fetch(url);
        const { codigo, mensaje, data } = await respuesta.json();
        
        if (codigo === 1) {
            const ctx = document.getElementById('ChartEstadosDispositivos').getContext('2d');
            
            if (chartEstados) {
                chartEstados.destroy();
            }
            
            const coloresEstados = {
                'NUEVO': '#28a745',
                'USADO': '#ffc107',
                'REPARADO': '#17a2b8',
                'DAÑADO': '#dc3545'
            };
            
            chartEstados = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: data.map(item => item.estado_dispositivo),
                    datasets: [{
                        data: data.map(item => item.cantidad),
                        backgroundColor: data.map(item => coloresEstados[item.estado_dispositivo] || '#6c757d'),
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 15,
                                usePointStyle: true,
                                font: {
                                    size: 11
                                }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = ((context.parsed * 100) / total).toFixed(1);
                                    return `${context.label}: ${context.parsed} dispositivos (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
        } else {
            mostrarError(`Error al cargar estados de dispositivos: ${mensaje}`);
        }
    } catch (error) {
        mostrarError('Error de conexión al cargar estados');
        console.error(error);
    }
};

// Cargar y crear gráfico de dispositivos por marca
const cargarGraficoMarcas = async () => {
    const url = '/empresa_celulares/dashboard/dispositivosPorMarcaAPI';
    try {
        const respuesta = await fetch(url);
        const { codigo, mensaje, data } = await respuesta.json();
        
        if (codigo === 1) {
            const ctx = document.getElementById('ChartDispositivosMarca').getContext('2d');
            
            if (chartMarcas) {
                chartMarcas.destroy();
            }
            
            chartMarcas = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.map(item => item.marca),
                    datasets: [
                        {
                            label: 'Disponibles',
                            data: data.map(item => item.disponibles),
                            backgroundColor: 'rgba(40, 167, 69, 0.8)',
                            borderColor: '#28a745',
                            borderWidth: 1
                        },
                        {
                            label: 'Vendidos',
                            data: data.map(item => item.vendidos),
                            backgroundColor: 'rgba(0, 123, 255, 0.8)',
                            borderColor: '#007bff',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                        }
                    },
                    scales: {
                        x: {
                            display: true,
                            title: {
                                display: true,
                                text: 'Marcas'
                            }
                        },
                        y: {
                            display: true,
                            title: {
                                display: true,
                                text: 'Cantidad de Dispositivos'
                            },
                            beginAtZero: true
                        }
                    }
                }
            });
        } else {
            mostrarError(`Error al cargar dispositivos por marca: ${mensaje}`);
        }
    } catch (error) {
        mostrarError('Error de conexión al cargar marcas');
        console.error(error);
    }
};

// Cargar actividad reciente
const cargarActividad = async () => {
    const url = '/empresa_celulares/dashboard/actividadRecienteAPI';
    try {
        const respuesta = await fetch(url);
        const { codigo, mensaje, data } = await respuesta.json();
        
        if (codigo === 1) {
            const listaActividad = document.getElementById('listaActividad');
            
            if (data.length === 0) {
                listaActividad.innerHTML = `
                    <div class="text-center text-muted">
                        <i class="bi bi-clock-history" style="font-size: 2rem;"></i>
                        <p class="mt-2 mb-0">No hay actividad reciente</p>
                    </div>
                `;
            } else {
                let html = '';
                data.forEach(actividad => {
                    let icono = '';
                    let colorIcono = '';
                    
                    switch (actividad.tipo) {
                        case 'venta':
                            icono = 'bi-cart-check';
                            colorIcono = 'text-success';
                            break;
                        case 'cliente':
                            icono = 'bi-person-plus';
                            colorIcono = 'text-primary';
                            break;
                        case 'orden':
                            icono = 'bi-tools';
                            colorIcono = 'text-warning';
                            break;
                        default:
                            icono = 'bi-activity';
                            colorIcono = 'text-info';
                    }
                    
                    // Formatear fecha
                    const fecha = new Date(actividad.fecha);
                    const fechaFormateada = fecha.toLocaleDateString('es-GT');
                    const horaFormateada = fecha.toLocaleTimeString('es-GT', { 
                        hour: '2-digit', 
                        minute: '2-digit' 
                    });
                    
                    html += `
                        <div class="d-flex align-items-start border-bottom py-3">
                            <div class="flex-shrink-0">
                                <i class="bi ${icono} ${colorIcono}" style="font-size: 1.2rem;"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="fw-semibold">${actividad.descripcion}</div>
                                <small class="text-muted">
                                    <i class="bi bi-calendar-event me-1"></i>
                                    ${fechaFormateada} - ${horaFormateada}
                                </small>
                            </div>
                        </div>
                    `;
                });
                listaActividad.innerHTML = html;
            }
        } else {
            mostrarError(`Error al cargar actividad: ${mensaje}`);
        }
    } catch (error) {
        mostrarError('Error de conexión al cargar actividad');
        console.error(error);
    }
};

// Función para cargar todo el dashboard
const cargarDashboardCompleto = async () => {
    BtnActualizarDashboard.disabled = true;
    BtnActualizarDashboard.innerHTML = '<i class="bi bi-arrow-clockwise spin me-2"></i>Actualizando...';
    
    try {
        await Promise.all([
            cargarEstadisticas(),
            cargarGraficoVentas(),
            cargarGraficoServicios(),
            cargarGraficoEstados(),
            cargarGraficoMarcas(),
            cargarActividad()
        ]);
        
        // Inicializar mapa después de que se cargue todo
        setTimeout(() => {
            inicializarMapa();
        }, 500);
        
        Swal.fire({
            icon: 'success',
            title: '📱 Dashboard Actualizado',
            text: 'Todos los datos de la empresa de celulares han sido actualizados',
            timer: 2000,
            showConfirmButton: false
        });
        
    } catch (error) {
        mostrarError('Error al actualizar el dashboard completo');
        console.error(error);
    } finally {
        BtnActualizarDashboard.disabled = false;
        BtnActualizarDashboard.innerHTML = '<i class="bi bi-arrow-clockwise me-2"></i>🔄 Actualizar Dashboard Completo';
    }
};

// Función para deshabilitar botón temporalmente
const deshabilitarBotonTemp = (boton, textoOriginal, duracion = 2000) => {
    boton.disabled = true;
    boton.innerHTML = '<i class="bi bi-arrow-clockwise spin"></i>';
    
    setTimeout(() => {
        boton.disabled = false;
        boton.innerHTML = textoOriginal;
    }, duracion);
};

// Event Listeners
document.addEventListener('DOMContentLoaded', () => {
    // Cargar dashboard inicial
    cargarDashboardCompleto();
    
    // Botón de actualización completa
    BtnActualizarDashboard.addEventListener('click', cargarDashboardCompleto);
    
    // Botones de actualización individual
    BtnActualizarVentas.addEventListener('click', () => {
        deshabilitarBotonTemp(BtnActualizarVentas, '<i class="bi bi-arrow-clockwise"></i>');
        cargarGraficoVentas();
    });
    
    BtnActualizarServicios.addEventListener('click', () => {
        deshabilitarBotonTemp(BtnActualizarServicios, '<i class="bi bi-arrow-clockwise"></i>');
        cargarGraficoServicios();
    });
    
    BtnActualizarMapa.addEventListener('click', () => {
        deshabilitarBotonTemp(BtnActualizarMapa, '<i class="bi bi-arrow-clockwise"></i>');
        inicializarMapa();
    });
    
    BtnActualizarEstados.addEventListener('click', () => {
        deshabilitarBotonTemp(BtnActualizarEstados, '<i class="bi bi-arrow-clockwise"></i>');
        cargarGraficoEstados();
    });
    
    BtnActualizarMarcas.addEventListener('click', () => {
        deshabilitarBotonTemp(BtnActualizarMarcas, '<i class="bi bi-arrow-clockwise"></i>');
        cargarGraficoMarcas();
    });
    
    BtnActualizarActividad.addEventListener('click', () => {
        deshabilitarBotonTemp(BtnActualizarActividad, '<i class="bi bi-arrow-clockwise"></i>');
        cargarActividad();
    });
    
    // Auto-actualización cada 10 minutos
    setInterval(() => {
        cargarEstadisticas();
        cargarActividad();
    }, 600000);
});