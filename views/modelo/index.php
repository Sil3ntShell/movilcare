<!-- Contenedor principal del formulario -->
<div class="container mt-5 p-4 rounded-4 shadow-lg bg-light" style="max-width: 900px;">
  <h3 class="mb-4 text-center text-primary fw-bold">
    <i class="bi bi-phone me-2"></i>Registro de Modelos de Celulares
  </h3>

  <form name="FormularioModelos" id="FormularioModelos" method="POST">
    <input type="hidden" id="modelo_id" name="modelo_id">

    <div class="row g-3 mb-3">
      <div class="col-md-6">
        <label for="marca_id" class="form-label">
          Marca <span class="text-danger">*</span>
        </label>
        <div class="input-group">
          <span class="input-group-text">
            <i class="bi bi-tag"></i>
          </span>
          <select class="form-select border-2" id="marca_id" name="marca_id" required>
            <option value="">Cargando marcas...</option>
            <!-- Las marcas se cargarán dinámicamente desde la API -->
          </select>
          <div class="invalid-feedback">
            <i class="bi bi-exclamation-circle me-1"></i>
            Debe seleccionar una marca.
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <label for="modelo_nombre" class="form-label">
          Nombre del Modelo <span class="text-danger">*</span>
        </label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-phone"></i></span>
          <input type="text" 
                 class="form-control" 
                 id="modelo_nombre" 
                 name="modelo_nombre" 
                 required 
                 placeholder="Ej. Galaxy S24, iPhone 15 Pro"
                 maxlength="100">
        </div>
        <div class="form-text">
          <small>Mínimo 2 caracteres, máximo 100</small>
        </div>
      </div>
    </div>

    <div class="mb-3">
      <label for="modelo_descripcion" class="form-label">
        Descripción <span class="text-danger">*</span>
      </label>
      <textarea class="form-control" 
                id="modelo_descripcion" 
                name="modelo_descripcion" 
                rows="4" 
                required
                placeholder="Descripción detallada del modelo: características, especificaciones, etc."
                maxlength="1024"></textarea>
      <div class="form-text">
        <small>Máximo 1024 caracteres</small>
      </div>
    </div>

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

<!-- Tabla de modelos -->
<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-lg-11">
      <div class="card shadow border-primary border-2 rounded-4">
        <div class="card-header bg-primary text-white rounded-top-4">
          <h4 class="text-center mb-0">
            <i class="bi bi-table me-2"></i> Modelos Registrados en la Base de Datos
          </h4>
        </div>
        <div class="card-body p-4">
          <div class="table-responsive">
            <table class="table table-striped table-hover table-bordered w-100 table-sm" id="TableModelos">
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
    <div class="col-lg-11">
      <div class="alert alert-info" role="alert">
        <h6 class="alert-heading">
          <i class="bi bi-info-circle me-2"></i>Información sobre Modelos
        </h6>
        <p class="mb-0">
          Los modelos deben estar asociados a una marca existente. Cada marca puede tener múltiples modelos, 
          pero el nombre del modelo debe ser único dentro de cada marca. Los modelos registrados estarán 
          disponibles para asociar con productos específicos.
        </p>
      </div>
    </div>
  </div>
</div>

<script src="<?= asset('build/js/modelo/index.js') ?>"></script>