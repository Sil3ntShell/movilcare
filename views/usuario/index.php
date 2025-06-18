<!-- Contenedor principal del formulario -->
<div class="container mt-5 p-4 rounded-4 shadow-lg bg-light" style="max-width: 900px;">
  <h3 class="mb-4 text-center text-primary fw-bold">Registro de Usuario</h3>

  <form name="userForm" id="userForm" method="POST" enctype="multipart/form-data">
    <input type="hidden" id="usuario_id" name="usuario_id">

    <!-- NOMBRES -->
    <div class="row g-3 mb-3">
      <div class="col-md-6">
        <label for="usuario_nom1" class="form-label">Primer Nombre <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="usuario_nom1" name="usuario_nom1" required maxlength="50" placeholder="Ej. Juan">
      </div>
      <div class="col-md-6">
        <label for="usuario_nom2" class="form-label">Segundo Nombre</label>
        <input type="text" class="form-control" id="usuario_nom2" name="usuario_nom2" maxlength="50" placeholder="Ej. Carlos">
      </div>
    </div>

    <!-- APELLIDOS -->
    <div class="row g-3 mb-3">
      <div class="col-md-6">
        <label for="usuario_ape1" class="form-label">Primer Apellido <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="usuario_ape1" name="usuario_ape1" required maxlength="50" placeholder="Ej. Pérez">
      </div>
      <div class="col-md-6">
        <label for="usuario_ape2" class="form-label">Segundo Apellido</label>
        <input type="text" class="form-control" id="usuario_ape2" name="usuario_ape2" maxlength="50" placeholder="Ej. García">
      </div>
    </div>

    <!-- DPI Y TELÉFONO -->
    <div class="row g-3 mb-3">
      <div class="col-md-6">
        <label for="usuario_dpi" class="form-label">DPI <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="usuario_dpi" name="usuario_dpi" required maxlength="13" placeholder="Ej. 1234567890123">
      </div>
      <div class="col-md-6">
        <label for="usuario_tel" class="form-label">Teléfono <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="usuario_tel" name="usuario_tel" required maxlength="8" placeholder="Ej. 12345678">
      </div>
    </div>

    <!-- CORREO -->
    <div class="row g-3 mb-3">
      <div class="col-md-8">
        <label for="usuario_correo" class="form-label">Correo Electrónico <span class="text-danger">*</span></label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-envelope"></i></span>
          <input type="email" class="form-control" id="usuario_correo" name="usuario_correo" required placeholder="ejemplo@correo.com">
        </div>
      </div>
    </div>

    <!-- DIRECCIÓN -->
    <div class="mb-3">
      <label for="usuario_direc" class="form-label">Dirección <span class="text-danger">*</span></label>
      <input type="text" class="form-control" id="usuario_direc" name="usuario_direc" required maxlength="150" placeholder="Ej. Zona 1, Ciudad de Guatemala">
    </div>

    <!-- CONTRASEÑAS -->
    <div id="password-section">
      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label for="usuario_contra" class="form-label">Contraseña <span class="text-danger">*</span></label>
          <div class="input-group">
            <input type="password" class="form-control" id="usuario_contra" name="usuario_contra" required minlength="10" placeholder="Mínimo 10 caracteres">
            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
              <i class="bi bi-eye" id="toggleIcon"></i>
            </button>
          </div>
          <div class="form-text">Debe contener: mayúsculas, minúsculas, números y símbolos.</div>
        </div>
        <div class="col-md-6">
          <label for="confirmar_contra" class="form-label">Confirmar Contraseña <span class="text-danger">*</span></label>
          <input type="password" class="form-control" id="confirmar_contra" name="confirmar_contra" required minlength="10" placeholder="Confirme su contraseña">
        </div>
      </div>
    </div>

    <!-- FOTOGRAFÍA -->
    <div class="mb-3">
      <label for="usuario_fotografia" class="form-label">Fotografía <i class="bi bi-camera text-muted"></i></label>
      <input type="file" class="form-control" id="usuario_fotografia" name="usuario_fotografia" accept="image/jpeg,image/jpg,image/png">
      <div class="form-text">Formatos permitidos: JPG, JPEG, PNG. Tamaño máximo: 2MB.</div>
      <div id="imagePreview" class="mt-2 d-none">
        <img id="previewImg" src="" alt="Vista previa" class="img-thumbnail" style="max-width: 150px; max-height: 150px;">
      </div>
    </div>

    <!-- BOTONES -->
    <div class="row justify-content-center mt-4 g-2">
      <div class="col-auto">
        <button type="submit" class="btn btn-success px-4" id="btnRegistrar">
          <i class="bi bi-check-circle me-1"></i> Registrar Usuario
        </button>
      </div>
      <div class="col-auto">
        <button type="button" class="btn btn-warning d-none px-4" id="btnModificar">
          <i class="bi bi-pencil-square me-1"></i> Modificar
        </button>
      </div>
      <div class="col-auto">
        <button type="button" class="btn btn-secondary px-4" id="btnLimpiar">
          <i class="bi bi-eraser me-1"></i> Limpiar
        </button>
      </div>
    </div>
  </form>
</div>

<!-- Tabla de usuarios -->
<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-lg-11">
      <div class="card shadow border-primary border-2 rounded-4">
        <div class="card-header bg-primary text-white rounded-top-4">
          <h4 class="text-center mb-0">
            <i class="bi bi-people-fill me-2"></i> Usuarios Registrados en la Base de Datos
          </h4>
        </div>
        <div class="card-body p-4">
          <div class="table-responsive">
            <table class="table table-striped table-hover table-bordered w-100 table-sm" id="TableUsuarios"></table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Enlace a JS -->
<script src="<?= asset('build/js/usuario/index.js') ?>"></script>
