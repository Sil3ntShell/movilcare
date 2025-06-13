<!-- Contenedor principal del formulario -->
<div class="container mt-5 p-4 rounded-4 shadow-lg bg-light" style="max-width: 900px;">
  <h3 class="mb-4 text-center text-primary fw-bold">Registro de Aplicación</h3>

  <form name="FormularioAplicaciones" id="FormularioAplicaciones" method="POST">
    <input type="hidden" id="app_id" name="app_id">

    <!-- NOMBRE LARGO -->
    <div class="mb-3">
      <label for="app_nombre_largo" class="form-label">Nombre Largo <span class="text-danger">*</span></label>
      <input type="text" class="form-control" id="app_nombre_largo" name="app_nombre_largo" required maxlength="100" placeholder="Ej. Sistema de Gestión de Celulares">
      <div class="form-text">Nombre completo y descriptivo de la aplicación</div>
    </div>

    <!-- NOMBRE MEDIUM -->
    <div class="mb-3">
      <label for="app_nombre_medium" class="form-label">Nombre Mediano <span class="text-danger">*</span></label>
      <input type="text" class="form-control" id="app_nombre_medium" name="app_nombre_medium" required maxlength="50" placeholder="Ej. Gestión Celulares">
      <div class="form-text">Nombre intermedio para mostrar en menús</div>
    </div>

    <!-- NOMBRE CORTO -->
    <div class="mb-3">
      <label for="app_nombre_corto" class="form-label">Nombre Corto <span class="text-danger">*</span></label>
      <input type="text" class="form-control" id="app_nombre_corto" name="app_nombre_corto" required maxlength="20" placeholder="Ej. CELULARES" style="text-transform: uppercase;">
      <div class="form-text">Código corto único para identificar la aplicación (solo letras y números)</div>
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

<!-- Tabla de aplicaciones -->
<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-lg-11">
      <div class="card shadow border-primary border-2 rounded-4">
        <div class="card-header bg-primary text-white rounded-top-4">
          <h4 class="text-center mb-0">
            <i class="bi bi-grid-3x3-gap-fill me-2"></i> Aplicaciones Registradas en la Base de Datos
          </h4>
        </div>
        <div class="card-body p-4">
          <div class="table-responsive">
            <table class="table table-striped table-hover table-bordered w-100 table-sm" id="TableAplicaciones"></table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="<?= asset('build/js/aplicacion/index.js') ?>"></script>