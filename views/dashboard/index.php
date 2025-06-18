<!-- Dashboard Empresa de Celulares -->
<div class="container-fluid mt-4">
  
  <!-- Header del Dashboard -->
  <div class="row mb-4">
    <div class="col-12">
      <div class="bg-gradient-primary text-white rounded-4 p-4 shadow">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h2 class="mb-1 fw-bold">📱 Dashboard - Empresa de Celulares</h2>
            <p class="mb-0 opacity-75">Panel de control y estadísticas del negocio</p>
          </div>
          <div class="text-end">
            <div class="h4 mb-0" id="fechaHoy"><?= date('d/m/Y') ?></div>
            <small class="opacity-75"><?= date('l, F') ?></small>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Tarjetas de Estadísticas Principales -->
  <div class="row g-3 mb-4">
    <!-- Ventas del Mes -->
    <div class="col-xl-2 col-lg-4 col-md-6">
      <div class="card bg-primary text-white shadow-sm h-100">
        <div class="card-body text-center">
          <i class="bi bi-cart-check display-6 mb-2"></i>
          <div class="h4 fw-bold" id="ventasMes">0</div>
          <div class="small">Ventas del Mes</div>
        </div>
      </div>
    </div>

    <!-- Ingresos del Mes -->
    <div class="col-xl-2 col-lg-4 col-md-6">
      <div class="card bg-success text-white shadow-sm h-100">
        <div class="card-body text-center">
          <i class="bi bi-currency-dollar display-6 mb-2"></i>
          <div class="h4 fw-bold" id="ingresosMes">Q 0</div>
          <div class="small">Ingresos del Mes</div>
        </div>
      </div>
    </div>

    <!-- Clientes Activos -->
    <div class="col-xl-2 col-lg-4 col-md-6">
      <div class="card bg-info text-white shadow-sm h-100">
        <div class="card-body text-center">
          <i class="bi bi-people display-6 mb-2"></i>
          <div class="h4 fw-bold" id="clientesActivos">0</div>
          <div class="small">Clientes Activos</div>
        </div>
      </div>
    </div>

    <!-- Servicios Completados -->
    <div class="col-xl-2 col-lg-4 col-md-6">
      <div class="card bg-warning text-white shadow-sm h-100">
        <div class="card-body text-center">
          <i class="bi bi-tools display-6 mb-2"></i>
          <div class="h4 fw-bold" id="serviciosCompletados">0</div>
          <div class="small">Servicios del Mes</div>
        </div>
      </div>
    </div>

    <!-- Órdenes Pendientes -->
    <div class="col-xl-2 col-lg-4 col-md-6">
      <div class="card bg-danger text-white shadow-sm h-100">
        <div class="card-body text-center">
          <i class="bi bi-clock-history display-6 mb-2"></i>
          <div class="h4 fw-bold" id="ordenesPendientes">0</div>
          <div class="small">Órdenes Pendientes</div>
        </div>
      </div>
    </div>

    <!-- Inventario Disponible -->
    <div class="col-xl-2 col-lg-4 col-md-6">
      <div class="card bg-secondary text-white shadow-sm h-100">
        <div class="card-body text-center">
          <i class="bi bi-phone display-6 mb-2"></i>
          <div class="h4 fw-bold" id="inventarioDisponible">0</div>
          <div class="small">Dispositivos Disponibles</div>
        </div>
      </div>
    </div>
  </div>

  <!-- Gráficos Principales -->
  <div class="row g-3 mb-4">
    
    <!-- Gráfico de Ventas Mensuales -->
    <div class="col-xl-8">
      <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
          <h5 class="mb-0">
            <i class="bi bi-bar-chart me-2"></i>
            Ventas Mensuales - Últimos 12 Meses
          </h5>
          <button class="btn btn-light btn-sm" id="BtnActualizarVentas">
            <i class="bi bi-arrow-clockwise"></i>
          </button>
        </div>
        <div class="card-body">
          <canvas id="ChartVentasMensuales" height="100"></canvas>
        </div>
      </div>
    </div>

    <!-- Servicios Más Solicitados -->
    <div class="col-xl-4">
      <div class="card shadow-sm">
        <div class="card-header bg-warning text-white d-flex justify-content-between align-items-center">
          <h5 class="mb-0">
            <i class="bi bi-wrench me-2"></i>
            Servicios Top
          </h5>
          <button class="btn btn-light btn-sm" id="BtnActualizarServicios">
            <i class="bi bi-arrow-clockwise"></i>
          </button>
        </div>
        <div class="card-body">
          <canvas id="ChartServiciosTop" height="200"></canvas>
        </div>
      </div>
    </div>
  </div>

  <!-- Mapa y Estados -->
  <div class="row g-3 mb-4">
    
    <!-- Mapa de Ubicaciones de Clientes -->
    <div class="col-xl-8">
      <div class="card shadow-sm">
        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
          <h5 class="mb-0">
            <i class="bi bi-geo-alt me-2"></i>
            Ubicaciones de Clientes - Ciudad de Guatemala
          </h5>
          <button class="btn btn-light btn-sm" id="BtnActualizarMapa">
            <i class="bi bi-arrow-clockwise"></i>
          </button>
        </div>
        <div class="card-body p-0">
          <div id="mapaClientes" style="height: 400px; width: 100%;"></div>
        </div>
      </div>
    </div>

    <!-- Estados de Dispositivos -->
    <div class="col-xl-4">
      <div class="card shadow-sm">
        <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
          <h5 class="mb-0">
            <i class="bi bi-phone-flip me-2"></i>
            Estados de Dispositivos
          </h5>
          <button class="btn btn-light btn-sm" id="BtnActualizarEstados">
            <i class="bi bi-arrow-clockwise"></i>
          </button>
        </div>
        <div class="card-body">
          <canvas id="ChartEstadosDispositivos" height="200"></canvas>
        </div>
      </div>
    </div>
  </div>

  <!-- Dispositivos por Marca y Actividad -->
  <div class="row g-3 mb-4">
    
    <!-- Dispositivos por Marca -->
    <div class="col-xl-6">
      <div class="card shadow-sm">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
          <h5 class="mb-0">
            <i class="bi bi-phone-vibrate me-2"></i>
            Dispositivos por Marca
          </h5>
          <button class="btn btn-light btn-sm" id="BtnActualizarMarcas">
            <i class="bi bi-arrow-clockwise"></i>
          </button>
        </div>
        <div class="card-body">
          <canvas id="ChartDispositivosMarca" height="150"></canvas>
        </div>
      </div>
    </div>

    <!-- Actividad Reciente -->
    <div class="col-xl-6">
      <div class="card shadow-sm">
        <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
          <h5 class="mb-0">
            <i class="bi bi-activity me-2"></i>
            Actividad Reciente
          </h5>
          <button class="btn btn-light btn-sm" id="BtnActualizarActividad">
            <i class="bi bi-arrow-clockwise"></i>
          </button>
        </div>
        <div class="card-body" style="max-height: 400px; overflow-y: auto;">
          <div id="listaActividad">
            <div class="text-center text-muted">
              <i class="bi bi-clock-history" style="font-size: 2rem;"></i>
              <p class="mt-2">Cargando actividad...</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Botón de Actualización General -->
  <div class="row mb-4">
    <div class="col-12 text-center">
      <button class="btn btn-primary btn-lg px-5 shadow" id="BtnActualizarDashboard">
        <i class="bi bi-arrow-clockwise me-2"></i>
        🔄 Actualizar Dashboard Completo
      </button>
    </div>
  </div>

</div>

<!-- CSS adicional para el gradiente -->
<style>
  .bg-gradient-primary {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
  }
  
  .spin {
    animation: spin 1s linear infinite;
  }
  
  @keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
  }
  
  /* Estilos para el mapa */
  .leaflet-popup-content {
    font-family: 'Bootstrap Icons', sans-serif;
  }
  
  .marker-cluster-small {
    background-color: rgba(181, 226, 140, 0.6);
  }
  
  .marker-cluster-medium {
    background-color: rgba(241, 211, 87, 0.6);
  }
  
  .marker-cluster-large {
    background-color: rgba(253, 156, 115, 0.6);
  }
</style>

<script src="<?= asset('build/js/dashboard/index.js') ?>"></script>