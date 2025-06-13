<!-- Contenedor principal del formulario -->
<div class="container mt-5 p-4 rounded-4 shadow-lg bg-light" style="max-width: 900px;">
  <h3 class="mb-4 text-center text-primary fw-bold">Registro de Permiso</h3>

  <form name="FormularioPermisos" id="FormularioPermisos" method="POST">
    <input type="hidden" id="permiso_id" name="permiso_id">

    <!-- USUARIO Y APLICACIÓN -->
    <div class="row g-3 mb-3">
      <div class="col-md-6">
        <label for="usuario_id" class="form-label">Usuario <span class="text-danger">*</span></label>
        <select class="form-select" id="usuario_id" name="usuario_id" required>
          <option value="">Seleccione un usuario</option>
        </select>
      </div>
      <div class="col-md-6">
        <label for="app_id" class="form-label">Aplicación <span class="text-danger">*</span></label>
        <select class="form-select" id="app_id" name="app_id" required>
          <option value="">Seleccione una aplicación</option>
        </select>
      </div>
    </div>

    <!-- NOMBRE Y CLAVE -->
    <div class="row g-3 mb-3">
      <div class="col-md-6">
        <label for="permiso_nombre" class="form-label">Nombre del Permiso <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="permiso_nombre" name="permiso_nombre" required maxlength="150" placeholder="Ej. Crear Productos">
      </div>
      <div class="col-md-6">
        <label for="permiso_clave" class="form-label">Clave del Permiso <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="permiso_clave" name="permiso_clave" required maxlength="250" placeholder="Ej. CREAR_PRODUCTOS" style="text-transform: uppercase;">
        <div class="form-text">Clave única para identificar el permiso (sin espacios)</div>
      </div>
    </div>

    <!-- TIPO Y USUARIO ASIGNÓ -->
    <div class="row g-3 mb-3">
      <div class="col-md-6">
        <label for="permiso_tipo" class="form-label">Tipo de Permiso <span class="text-danger">*</span></label>
        <select class="form-select" id="permiso_tipo" name="permiso_tipo" required>
          <option value="">Seleccione un tipo</option>
          <option value="FUNCIONAL">Funcional - Acceso a funcionalidades</option>
          <option value="MENU">Menú - Acceso a opciones de menú</option>
          <option value="REPORTE">Reporte - Generación de reportes</option>
          <option value="ADMIN">Administración - Gestión del sistema</option>
          <option value="ESPECIAL">Especial - Permisos especiales</option>
        </select>
      </div>
      <div class="col-md-6">
        <label for="permiso_usuario_asigno" class="form-label">Usuario que Asigna</label>
        <select class="form-select" id="permiso_usuario_asigno" name="permiso_usuario_asigno">
          <option value="">Seleccione usuario que asigna</option>
        </select>
      </div>
    </div>

    <!-- DESCRIPCIÓN -->
    <div class="mb-3">
      <label for="permiso_desc" class="form-label">Descripción</label>
      <textarea class="form-control" id="permiso_desc" name="permiso_desc" rows="3" maxlength="250" placeholder="Describe para qué sirve este permiso..."></textarea>
    </div>

    <!-- MOTIVO -->
    <div class="mb-3">
      <label for="permiso_motivo" class="form-label">Motivo</label>
      <input type="text" class="form-control" id="permiso_motivo" name="permiso_motivo" maxlength="250" placeholder="Motivo de la asignación de este permiso">
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

<!-- Tabla de permisos -->
<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-lg-11">
      <div class="card shadow border-primary border-2 rounded-4">
        <div class="card-header bg-primary text-white rounded-top-4">
          <h4 class="text-center mb-0">
            <i class="bi bi-shield-lock-fill me-2"></i> Permisos Registrados en la Base de Datos
          </h4>
        </div>
        <div class="card-body p-4">
          <!-- Filtros -->
          <div class="row mb-3">
            <div class="col-md-4">
              <label for="filtro_app" class="form-label">Filtrar por Aplicación:</label>
              <select class="form-select form-select-sm" id="filtro_app">
                <option value="">Todas las aplicaciones</option>
              </select>
            </div>
            <div class="col-md-4">
              <label for="filtro_usuario" class="form-label">Filtrar por Usuario:</label>
              <select class="form-select form-select-sm" id="filtro_usuario">
                <option value="">Todos los usuarios</option>
              </select>
            </div>
            <div class="col-md-4">
              <label for="filtro_tipo" class="form-label">Filtrar por Tipo:</label>
              <select class="form-select form-select-sm" id="filtro_tipo">
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
            <table class="table table-striped table-hover table-bordered w-100 table-sm" id="TablePermisos"></table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="<?= asset('build/js/permiso/index.js') ?>"></script>