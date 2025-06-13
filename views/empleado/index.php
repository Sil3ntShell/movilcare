<!-- Contenedor principal del formulario -->
<div class="container mt-5 p-4 rounded-4 shadow-lg bg-light" style="max-width: 900px;">
  <h3 class="mb-4 text-center text-primary fw-bold">Registro de Empleado</h3>

  <form name="FormularioEmpleados" id="FormularioEmpleados" method="POST">
    <input type="hidden" id="empleado_id" name="empleado_id">

    <!-- NOMBRES -->
    <div class="row g-3 mb-3">
      <div class="col-md-6">
        <label for="empleado_nom1" class="form-label">Primer Nombre <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="empleado_nom1" name="empleado_nom1" required maxlength="50" placeholder="Ej. Juan">
      </div>
      <div class="col-md-6">
        <label for="empleado_nom2" class="form-label">Segundo Nombre</label>
        <input type="text" class="form-control" id="empleado_nom2" name="empleado_nom2" maxlength="50" placeholder="Ej. Carlos">
      </div>
    </div>

    <!-- APELLIDOS -->
    <div class="row g-3 mb-3">
      <div class="col-md-6">
        <label for="empleado_ape1" class="form-label">Primer Apellido <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="empleado_ape1" name="empleado_ape1" required maxlength="50" placeholder="Ej. Pérez">
      </div>
      <div class="col-md-6">
        <label for="empleado_ape2" class="form-label">Segundo Apellido</label>
        <input type="text" class="form-control" id="empleado_ape2" name="empleado_ape2" maxlength="50" placeholder="Ej. García">
      </div>
    </div>

    <!-- DPI Y TELÉFONO -->
    <div class="row g-3 mb-3">
      <div class="col-md-6">
        <label for="empleado_dpi" class="form-label">DPI <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="empleado_dpi" name="empleado_dpi" required maxlength="13" placeholder="Ej. 1234567890123">
      </div>
      <div class="col-md-6">
        <label for="empleado_tel" class="form-label">Teléfono <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="empleado_tel" name="empleado_tel" required maxlength="8" placeholder="Ej. 12345678">
      </div>
    </div>

    <!-- CORREO Y USUARIO -->
    <div class="row g-3 mb-3">
      <div class="col-md-8">
        <label for="empleado_correo" class="form-label">Correo Electrónico <span class="text-danger">*</span></label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-envelope"></i></span>
          <input type="email" class="form-control" id="empleado_correo" name="empleado_correo" required placeholder="ejemplo@correo.com">
        </div>
      </div>
    </div>

    <!-- ESPECIALIDAD Y SALARIO -->
    <div class="row g-3 mb-3">
      <div class="col-md-8">
        <label for="empleado_especialidad" class="form-label">Especialidad <span class="text-danger">*</span></label>
        <select class="form-select" id="empleado_especialidad" name="empleado_especialidad" required>
          <option value="">Seleccione una especialidad</option>
          <option value="Técnico en Reparación de Celulares">Técnico en Reparación de Celulares</option>
          <option value="Especialista en Pantallas">Especialista en Pantallas</option>
          <option value="Técnico en Software">Técnico en Software</option>
          <option value="Especialista en Placas">Especialista en Placas</option>
          <option value="Técnico en Baterías">Técnico en Baterías</option>
          <option value="Vendedor">Vendedor</option>
          <option value="Atención al Cliente">Atención al Cliente</option>
          <option value="Administrador">Administrador</option>
          <option value="Gerente">Gerente</option>
          <option value="Supervisor">Supervisor</option>
          <option value="Recepcionista">Recepcionista</option>
          <option value="Contador">Contador</option>
          <option value="Otro">Otro</option>
        </select>
      </div>
      <div class="col-md-4">
        <label for="empleado_salario" class="form-label">Salario <span class="text-danger">*</span></label>
        <div class="input-group">
          <span class="input-group-text">Q.</span>
          <input type="number" class="form-control" id="empleado_salario" name="empleado_salario" required step="0.01" min="0" placeholder="3000.00">
        </div>
      </div>
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

<!-- Tabla de empleados -->
<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-lg-11">
      <div class="card shadow border-primary border-2 rounded-4">
        <div class="card-header bg-primary text-white rounded-top-4">
          <h4 class="text-center mb-0">
            <i class="bi bi-people-fill me-2"></i> Empleados Registrados en la Base de Datos
          </h4>
        </div>
        <div class="card-body p-4">
          <div class="table-responsive">
            <table class="table table-striped table-hover table-bordered w-100 table-sm" id="TablaEmpleados"></table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="<?= asset('build/js/empleado/index.js') ?>"></script>