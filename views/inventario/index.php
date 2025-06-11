<!-- Contenedor principal del formulario -->
<div class="container mt-5 p-4 rounded-4 shadow-lg bg-light" style="max-width: 1200px;">
  <h3 class="mb-4 text-center text-primary fw-bold">
    <i class="bi bi-box-seam me-2"></i>Inventario de Celulares
  </h3>

  <form name="FormularioInventario" id="FormularioInventario" method="POST">
    <input type="hidden" id="inventario_id" name="inventario_id">

    <!-- Primera fila: Modelo y Número de Serie -->
    <div class="row g-3 mb-3">
      <div class="col-md-6">
        <label for="modelo_id" class="form-label">
          Modelo <span class="text-danger">*</span>
        </label>
        <div class="input-group">
          <span class="input-group-text">
            <i class="bi bi-phone"></i>
          </span>
          <select class="form-select border-2" id="modelo_id" name="modelo_id" required>
            <option value="">Cargando modelos...</option>
          </select>
          <div class="invalid-feedback">
            <i class="bi bi-exclamation-circle me-1"></i>
            Debe seleccionar un modelo.
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <label for="inventario_numero_serie" class="form-label">
          Número de Serie <span class="text-danger">*</span>
        </label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-upc-scan"></i></span>
          <input type="text" 
                 class="form-control" 
                 id="inventario_numero_serie" 
                 name="inventario_numero_serie" 
                 required 
                 placeholder="Ej. SN123456789"
                 maxlength="100">
        </div>
        <div class="form-text">
          <small>Mínimo 5 caracteres</small>
        </div>
      </div>
    </div>

    <!-- Segunda fila: IMEI y Estado -->
    <div class="row g-3 mb-3">
      <div class="col-md-6">
        <label for="inventario_imei" class="form-label">
          IMEI <span class="text-danger">*</span>
        </label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-sim"></i></span>
          <input type="text" 
                 class="form-control" 
                 id="inventario_imei" 
                 name="inventario_imei" 
                 required 
                 placeholder="Ej. 123456789012345"
                 maxlength="15"
                 pattern="[0-9]{15}">
        </div>
        <div class="form-text">
          <small>Exactamente 15 dígitos</small>
        </div>
      </div>
      <div class="col-md-6">
        <label for="inventario_estado" class="form-label">
          Estado <span class="text-danger">*</span>
        </label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-clipboard-check"></i></span>
          <select class="form-select" id="inventario_estado" name="inventario_estado" required>
            <option value="">Seleccione el estado...</option>
            <option value="Disponible">Disponible</option>
            <option value="Vendido">Vendido</option>
            <option value="Dañado">Dañado</option>
            <option value="En reparación">En reparación</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Tercera fila: Precios -->
    <div class="row g-3 mb-3">
      <div class="col-md-4">
        <label for="inventario_precio_compra" class="form-label">
          Precio de Compra <span class="text-danger">*</span>
        </label>
        <div class="input-group">
          <span class="input-group-text">Q</span>
          <input type="number" 
                 class="form-control" 
                 id="inventario_precio_compra" 
                 name="inventario_precio_compra" 
                 required 
                 step="0.01" 
                 min="0.01"
                 placeholder="0.00">
        </div>
      </div>
      <div class="col-md-4">
        <label for="inventario_precio_venta" class="form-label">
          Precio de Venta <span class="text-danger">*</span>
        </label>
        <div class="input-group">
          <span class="input-group-text">Q</span>
          <input type="number" 
                 class="form-control" 
                 id="inventario_precio_venta" 
                 name="inventario_precio_venta" 
                 required 
                 step="0.01" 
                 min="0.01"
                 placeholder="0.00">
        </div>
      </div>
      <div class="col-md-4">
        <label for="inventario_stock_disponible" class="form-label">
          Stock Disponible <span class="text-danger">*</span>
        </label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-boxes"></i></span>
          <input type="number" 
                 class="form-control" 
                 id="inventario_stock_disponible" 
                 name="inventario_stock_disponible" 
                 required 
                 min="0"
                 value="1"
                 placeholder="1">
        </div>
      </div>
    </div>

    <!-- Cuarta fila: Ubicación -->
    <div class="row g-3 mb-3">
      <div class="col-md-12">
        <label for="inventario_ubicacion" class="form-label">
          Ubicación <span class="text-danger">*</span>
        </label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
          <input type="text" 
                 class="form-control" 
                 id="inventario_ubicacion" 
                 name="inventario_ubicacion" 
                 required 
                 placeholder="Ej. Estante A, Nivel 2, Bodega Principal"
                 maxlength="100">
        </div>
      </div>
    </div>

    <!-- Quinta fila: Observaciones -->
    <div class="mb-3">
      <label for="inventario_observaciones" class="form-label">
        Observaciones <span class="text-danger">*</span>
      </label>
      <textarea class="form-control" 
                id="inventario_observaciones" 
                name="inventario_observaciones" 
                rows="3" 
                required
                placeholder="Comentarios adicionales sobre el producto..."
                maxlength="1024"></textarea>
      <div class="form-text">
        <small>Máximo 1024 caracteres</small>
      </div>
    </div>

    <!-- Botones -->
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

<!-- Tabla de inventario -->
<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-12">
      <div class="card shadow border-primary border-2 rounded-4">
        <div class="card-header bg-primary text-white rounded-top-4">
          <h4 class="text-center mb-0">
            <i class="bi bi-table me-2"></i> Inventario de Productos
          </h4>
        </div>
        <div class="card-body p-4">
          <div class="table-responsive">
            <table class="table table-striped table-hover table-bordered w-100 table-sm" id="TableInventario">
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Información adicional -->
<div class="container mt-4">
  <div class="row justify-content-center">
    <div class="col-12">
      <div class="alert alert-info" role="alert">
        <h6 class="alert-heading">
          <i class="bi bi-info-circle me-2"></i>Información sobre el Inventario
        </h6>
        <p class="mb-0">
          El inventario registra todos los celulares disponibles en la tienda. Cada producto debe tener un número de serie 
          e IMEI únicos. Los estados disponibles son: <strong>Disponible</strong> (para venta), <strong>Vendido</strong> 
          (ya vendido), <strong>Dañado</strong> (producto defectuoso), y <strong>En reparación</strong> (temporalmente no disponible).
          El stock disponible indica cuántas unidades de ese producto específico están disponibles para venta.
        </p>
      </div>
    </div>
  </div>
</div>

<script src="<?= asset('build/js/inventario/index.js') ?>"></script>