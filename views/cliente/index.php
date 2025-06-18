<!-- Contenedor principal del formulario -->
<div class="container mt-5 p-4 rounded-4 shadow-lg bg-light" style="max-width: 900px;">
  <h3 class="mb-4 text-center text-primary fw-bold">Registro de Cliente</h3>

  <form name="FormularioClientes" id="FormularioClientes" method="POST">
    <input type="hidden" id="cliente_id" name="cliente_id">

    <!-- Nombres -->
    <div class="row g-3 mb-3">
      <div class="col-md-6">
        <label for="cliente_nom1" class="form-label">Primer Nombre <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="cliente_nom1" name="cliente_nom1" required placeholder="Ej. Myron">
      </div>
      <div class="col-md-6">
        <label for="cliente_nom2" class="form-label">Segundo Nombre <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="cliente_nom2" name="cliente_nom2" required placeholder="Ej. Raúl">
      </div>
    </div>

    <!-- Apellidos -->
    <div class="row g-3 mb-3">
      <div class="col-md-6">
        <label for="cliente_ape1" class="form-label">Primer Apellido <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="cliente_ape1" name="cliente_ape1" required placeholder="Ej. Montoya">
      </div>
      <div class="col-md-6">
        <label for="cliente_ape2" class="form-label">Segundo Apellido <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="cliente_ape2" name="cliente_ape2" required placeholder="Ej. López">
      </div>
    </div>

    <!-- DPI / NIT -->
    <div class="row g-3 mb-3">
      <div class="col-md-6">
        <label for="cliente_dpi" class="form-label">DPI</label>
        <input type="text" class="form-control" id="cliente_dpi" name="cliente_dpi" maxlength="13" placeholder="Ej. 1234567890123">
      </div>
      <div class="col-md-6">
        <label for="cliente_nit" class="form-label">NIT</label>
        <input type="text" class="form-control" id="cliente_nit" name="cliente_nit" placeholder="Ej. 1234567-8">
      </div>
    </div>

    <!-- Correo / Teléfono -->
    <div class="row g-3 mb-3">
      <div class="col-md-8">
        <label for="cliente_correo" class="form-label">Correo Electrónico</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-envelope"></i></span>
          <input type="email" class="form-control" id="cliente_correo" name="cliente_correo" placeholder="ejemplo@correo.com">
        </div>
      </div>
      <div class="col-md-4">
        <label for="cliente_tel" class="form-label">Teléfono <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="cliente_tel" name="cliente_tel" required placeholder="Ej. 22334455">
      </div>
    </div>

    <!-- Dirección -->
    <div class="mb-3">
      <label for="cliente_direc" class="form-label">Dirección <span class="text-danger">*</span></label>
      <input type="text" class="form-control" id="cliente_direc" name="cliente_direc" required placeholder="Ej. Zona 1, Ciudad de Guatemala">
    </div>

    <!-- Fecha nacimiento -->
    <div class="mb-3">
      <label for="cliente_fecha_nacimiento" class="form-label">Fecha de Nacimiento</label>
      <input type="date" id="cliente_fecha_nacimiento" name="cliente_fecha_nacimiento" lang="en" />

    </div>

    <!-- Observaciones -->
    <div class="mb-3">
      <label for="cliente_observaciones" class="form-label">Observaciones</label>
      <textarea class="form-control" id="cliente_observaciones" name="cliente_observaciones" rows="3" placeholder="Comentarios adicionales..."></textarea>
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

<!-- Tabla de clientes -->
<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-lg-11">
      <div class="card shadow border-primary border-2 rounded-4">
        <div class="card-header bg-primary text-white rounded-top-4">
          <h4 class="text-center mb-0">
            <i class="bi bi-table me-2"></i> Clientes Registrados en la Base de Datos
          </h4>
        </div>
        <div class="card-body p-4">
          <div class="table-responsive">
            <table class="table table-striped table-hover table-bordered w-100 table-sm" id="TableClientes"></table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="<?= asset('build/js/cliente/index.js') ?>"></script>
