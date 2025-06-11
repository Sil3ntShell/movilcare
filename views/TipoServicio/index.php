<!-- Contenedor principal del formulario -->
<div class="container mt-5 p-4 rounded-4 shadow-lg bg-light" style="max-width: 900px;">
  <h3 class="mb-4 text-center text-primary fw-bold">Gestión de Tipos de Servicio</h3>

  <form name="FormularioTipoServicio" id="FormularioTipoServicio" method="POST">
    <input type="hidden" id="tipo_servicio_id" name="tipo_servicio_id">

    <!-- NOMBRE DEL SERVICIO -->
    <div class="mb-3">
      <label for="tipo_servicio_nombre" class="form-label">Nombre del Servicio <span class="text-danger">*</span></label>
      <input type="text" class="form-control" id="tipo_servicio_nombre" name="tipo_servicio_nombre" required maxlength="100" placeholder="Ej. Cambio de Pantalla">
    </div>

    <!-- DESCRIPCIÓN -->
    <div class="mb-3">
      <label for="tipo_servicio_descripcion" class="form-label">Descripción del Servicio</label>
      <textarea class="form-control" id="tipo_servicio_descripcion" name="tipo_servicio_descripcion" rows="3" placeholder="Describe detalladamente el servicio que se ofrece..."></textarea>
    </div>

    <!-- PRECIO Y TIEMPO -->
    <div class="row g-3 mb-3">
      <div class="col-md-6">
        <label for="tipo_servicio_precio_base" class="form-label">Precio Base <span class="text-danger">*</span></label>
        <div class="input-group">
          <span class="input-group-text">Q.</span>
          <input type="number" class="form-control" id="tipo_servicio_precio_base" name="tipo_servicio_precio_base" required step="0.01" min="0" placeholder="150.00">
        </div>
        <div class="form-text">Precio base del servicio (puede variar según complejidad)</div>
      </div>
      <div class="col-md-6">
        <label for="tipo_servicio_tiempo_estimado" class="form-label">Tiempo Estimado <span class="text-danger">*</span></label>
        <div class="input-group">
          <input type="number" class="form-control" id="tipo_servicio_tiempo_estimado" name="tipo_servicio_tiempo_estimado" required min="1" placeholder="60">
          <span class="input-group-text">minutos</span>
        </div>
        <div class="form-text">Tiempo estimado para completar el servicio</div>
      </div>
    </div>

    <!-- EJEMPLOS RÁPIDOS -->
    <div class="mb-3">
      <label class="form-label">Ejemplos de Servicios Comunes:</label>
      <div class="d-flex flex-wrap gap-2">
        <button type="button" class="btn btn-outline-info btn-sm ejemplo-servicio" 
                data-nombre="Cambio de Pantalla" data-precio="200.00" data-tiempo="120">
          Cambio de Pantalla
        </button>
        <button type="button" class="btn btn-outline-info btn-sm ejemplo-servicio" 
                data-nombre="Cambio de Batería" data-precio="80.00" data-tiempo="30">
          Cambio de Batería
        </button>
        <button type="button" class="btn btn-outline-info btn-sm ejemplo-servicio" 
                data-nombre="Reparación de Placa" data-precio="350.00" data-tiempo="480">
          Reparación de Placa
        </button>
        <button type="button" class="btn btn-outline-info btn-sm ejemplo-servicio" 
                data-nombre="Liberación de Equipo" data-precio="50.00" data-tiempo="15">
          Liberación
        </button>
        <button type="button" class="btn btn-outline-info btn-sm ejemplo-servicio" 
                data-nombre="Diagnóstico General" data-precio="25.00" data-tiempo="30">
          Diagnóstico
        </button>
      </div>
      <div class="form-text">Haz clic en cualquier ejemplo para llenar el formulario automáticamente</div>
    </div>

    <!-- BOTONES -->
    <div class="row justify-content-center mt-4 g-2">
      <div class="col-auto">
        <button type="submit" class="btn btn-success px-4" id="BtnGuardar">
          <i class="bi bi-check-circle me-1"></i> Guardar
        </button>
      </div>
      <div class="col-auto">
        <button type="button" class="btn btn-warning d-none px-4" id="BtnModificar">
          <i class="bi bi-pencil-square me-1"></i> Modificar
        </button>
      </div>
      <div class="col-auto">
        <button type="reset" class="btn btn-secondary px-4" id="BtnLimpiar">
          <i class="bi bi-eraser me-1"></i> Limpiar
        </button>
      </div>
    </div>
  </form>
</div>

<!-- Tabla de tipos de servicio -->
<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-lg-11">
      <div class="card shadow border-primary border-2 rounded-4">
        <div class="card-header bg-primary text-white rounded-top-4">
          <h4 class="text-center mb-0">
            <i class="bi bi-tools me-2"></i> Tipos de Servicio Registrados
          </h4>
        </div>
        <div class="card-body p-4">
          <div class="table-responsive">
            <table class="table table-striped table-hover table-bordered w-100 table-sm" id="TablaTipoServicio"></table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="<?= asset('build/js/tiposervicio/index.js') ?>"></script>