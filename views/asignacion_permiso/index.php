<!-- Contenedor principal del formulario -->
<div class="container mt-5 p-4 rounded-4 shadow-lg bg-light" style="max-width: 900px;">
  <h3 class="mb-4 text-center text-primary fw-bold">
    <i class="bi bi-person-check-fill me-2"></i>Asignación de Permisos
  </h3>

  <form name="FormularioAsignaciones" id="FormularioAsignaciones" method="POST">
    <input type="hidden" id="asignacion_id" name="asignacion_id">

    <!-- USUARIO AL QUE SE ASIGNA -->
    <div class="row g-3 mb-3">
      <div class="col-md-6">
        <label for="asignacion_usuario_id" class="form-label">Usuario que Recibe el Permiso <span class="text-danger">*</span></label>
        <select class="form-select" id="asignacion_usuario_id" name="asignacion_usuario_id" required>
          <option value="">Seleccione el usuario</option>
        </select>
        <div class="form-text">Usuario al que se le asignará el permiso</div>
      </div>
      <div class="col-md-6">
        <label for="asignacion_usuario_asigno" class="form-label">Usuario que Asigna <span class="text-danger">*</span></label>
        <select class="form-select" id="asignacion_usuario_asigno" name="asignacion_usuario_asigno" required>
          <option value="">Seleccione quien asigna</option>
        </select>
        <div class="form-text">Usuario que está realizando la asignación</div>
      </div>
    </div>

    <!-- FILTROS PARA PERMISOS -->
    <div class="row g-3 mb-3">
      <div class="col-md-6">
        <label for="filtro_aplicacion" class="form-label">Filtrar por Aplicación</label>
        <select class="form-select" id="filtro_aplicacion">
          <option value="">Todas las aplicaciones</option>
        </select>
        <div class="form-text">Filtra los permisos por aplicación</div>
      </div>
      <div class="col-md-6">
        <label for="filtro_tipo_permiso" class="form-label">Filtrar por Tipo</label>
        <select class="form-select" id="filtro_tipo_permiso">
          <option value="">Todos los tipos</option>
          <option value="FUNCIONAL">Funcional</option>
          <option value="MENU">Menú</option>
          <option value="REPORTE">Reporte</option>
          <option value="ADMIN">Administración</option>
          <option value="ESPECIAL">Especial</option>
        </select>
      </div>
    </div>

    <!-- PERMISO A ASIGNAR -->
    <div class="mb-3">
      <label for="asignacion_permiso_id" class="form-label">Permiso a Asignar <span class="text-danger">*</span></label>
      <select class="form-select" id="asignacion_permiso_id" name="asignacion_permiso_id" required>
        <option value="">Seleccione el permiso</option>
      </select>
      <div class="form-text">Permiso que será asignado al usuario</div>
    </div>

    <!-- MOTIVO -->
    <div class="mb-3">
      <label for="asignacion_motivo" class="form-label">Motivo de la Asignación</label>
      <textarea class="form-control" id="asignacion_motivo" name="asignacion_motivo" rows="3" maxlength="250" placeholder="Explique por qué se asigna este permiso al usuario..."></textarea>
      <div class="form-text">Máximo 250 caracteres</div>
    </div>

    <!-- BOTONES -->
    <div class="row justify-content-center mt-4 g-2">
      <div class="col-auto">
        <button type="submit" class="btn btn-success px-4" id="BtnGuardar">
          <i class="bi bi-check-circle me-1"></i> Asignar Permiso
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

<!-- Tabla de asignaciones -->
<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-lg-11">
      <div class="card shadow border-primary border-2 rounded-4">
        <div class="card-header bg-primary text-white rounded-top-4">
          <h4 class="text-center mb-0">
            <i class="bi bi-list-check me-2"></i> Asignaciones de Permisos Activas
          </h4>
        </div>
        <div class="card-body p-4">
          <!-- Filtros de la tabla -->
          <div class="row mb-3">
            <div class="col-md-4">
              <label for="filtro_tabla_usuario" class="form-label">Filtrar por Usuario:</label>
              <select class="form-select form-select-sm" id="filtro_tabla_usuario">
                <option value="">Todos los usuarios</option>
              </select>
            </div>
            <div class="col-md-4">
              <label for="filtro_tabla_app" class="form-label">Filtrar por Aplicación:</label>
              <select class="form-select form-select-sm" id="filtro_tabla_app">
                <option value="">Todas las aplicaciones</option>
              </select>
            </div>
            <div class="col-md-4">
              <label for="filtro_tabla_tipo" class="form-label">Filtrar por Tipo:</label>
              <select class="form-select form-select-sm" id="filtro_tabla_tipo">
                <option value="">Todos los tipos</option>
                <option value="FUNCIONAL">Funcional</option>
                <option value="MENU">Menú</option>
                <option value="REPORTE">Reporte</option>
                <option value="ADMIN">Administración</option>
                <option value="ESPECIAL">Especial</option>
              </select>
            </div>
          </div>
          
          <div class="table-responsive">
            <table class="table table-striped table-hover table-bordered w-100 table-sm" id="TableAsignaciones"></table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal para ver detalles de asignación -->
<div class="modal fade" id="modalDetalles" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title">
          <i class="bi bi-info-circle me-2"></i>Detalles de la Asignación
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="detallesContent">
        <!-- Contenido dinámico -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<script src="<?= asset('build/js/asignacion_permiso/index.js') ?>"></script>