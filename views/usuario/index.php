<!-- CONTENEDOR PRINCIPAL DEL SISTEMA DE REGISTRO -->
<div class="container py-5">
    <!-- SECCIÓN DEL FORMULARIO DE REGISTRO -->
    <div class="row justify-content-center mb-5">
        <div class="col-xl-8 col-lg-10">
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-header bg-gradient-primary text-white py-3 rounded-top-4">
                    <h4 class="mb-0">
                        <i class="bi bi-person-plus-fill me-2"></i> Registro de Usuario
                    </h4>
                </div>
                <div class="card-body px-4">
                    <form id="userForm" enctype="multipart/form-data" novalidate>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="usuario_nom1" class="form-label">Primer Nombre <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="usuario_nom1" name="usuario_nom1" required maxlength="200" placeholder="Ingrese primer nombre">
                                <div class="invalid-feedback">Por favor ingrese el primer nombre.</div>
                            </div>
                            <div class="col-md-6">
                                <label for="usuario_nom2" class="form-label">Segundo Nombre</label>
                                <input type="text" class="form-control" id="usuario_nom2" name="usuario_nom2" maxlength="200" placeholder="Segundo nombre (opcional)">
                            </div>
                            <div class="col-md-6">
                                <label for="usuario_ape1" class="form-label">Primer Apellido <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="usuario_ape1" name="usuario_ape1" required maxlength="50" placeholder="Ingrese primer apellido">
                                <div class="invalid-feedback">Por favor ingrese el primer apellido.</div>
                            </div>
                            <div class="col-md-6">
                                <label for="usuario_ape2" class="form-label">Segundo Apellido</label>
                                <input type="text" class="form-control" id="usuario_ape2" name="usuario_ape2" maxlength="50" placeholder="Segundo apellido (opcional)">
                            </div>
                            <div class="col-md-6">
                                <label for="usuario_dpi" class="form-label">DPI <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="usuario_dpi" name="usuario_dpi" required maxlength="13" pattern="[0-9]{13}" placeholder="1234567890123">
                                <div class="invalid-feedback">DPI inválido (debe tener exactamente 13 dígitos).</div>
                            </div>
                            <div class="col-md-6">
                                <label for="usuario_tel" class="form-label">Teléfono <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="usuario_tel" name="usuario_tel" required maxlength="8" pattern="[0-9]{8}" placeholder="12345678">
                                <div class="invalid-feedback">Teléfono inválido (debe tener exactamente 8 dígitos).</div>
                            </div>
                            <div class="col-12">
                                <label for="usuario_direc" class="form-label">Dirección <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="usuario_direc" name="usuario_direc" required maxlength="200" placeholder="Ingrese su dirección completa">
                                <div class="invalid-feedback">Por favor ingrese su dirección.</div>
                            </div>
                            <div class="col-md-12">
                                <label for="usuario_correo" class="form-label">Correo Electrónico <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input type="email" class="form-control" id="usuario_correo" name="usuario_correo" required maxlength="100" placeholder="ejemplo@correo.com">
                                    <div class="invalid-feedback">Correo electrónico inválido.</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="usuario_contra" class="form-label">Contraseña <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="usuario_contra" name="usuario_contra" required minlength="10" placeholder="Mínimo 10 caracteres">
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                        <i class="bi bi-eye" id="toggleIcon"></i>
                                    </button>
                                    <div class="invalid-feedback">Debe tener al menos 10 caracteres con mayúsculas, minúsculas, números y símbolos.</div>
                                </div>
                                <div class="form-text">La contraseña debe contener: mayúsculas, minúsculas, números y caracteres especiales.</div>
                            </div>
                            <div class="col-md-6">
                                <label for="confirmar_contra" class="form-label">Confirmar Contraseña <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="confirmar_contra" name="confirmar_contra" required minlength="10" placeholder="Confirme su contraseña">
                                <div class="invalid-feedback">Las contraseñas no coinciden.</div>
                            </div>
                            <div class="col-md-12">
                                <label for="usuario_fotografia" class="form-label">Fotografía <i class="bi bi-camera text-muted"></i></label>
                                <input type="file" class="form-control" id="usuario_fotografia" name="usuario_fotografia" accept="image/jpeg,image/jpg,image/png">
                                <div class="form-text">Formatos permitidos: JPG, JPEG, PNG. Tamaño máximo: 2MB.</div>
                                <div id="imagePreview" class="mt-2 d-none">
                                    <img id="previewImg" src="" alt="Vista previa" class="img-thumbnail" style="max-width: 150px; max-height: 150px;">
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 d-flex justify-content-between">
                            <button type="button" class="btn btn-outline-secondary" id="btnLimpiar">
                                <i class="bi bi-arrow-clockwise me-1"></i> Limpiar
                            </button>
                            <button type="submit" class="btn btn-primary" id="btnRegistrar">
                                <span class="spinner-border spinner-border-sm d-none me-1" id="loadingSpinner"></span>
                                <i class="bi bi-check-circle me-1"></i> Registrar Usuario
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- SECCIÓN DE TABLA DE USUARIOS -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow border-0 rounded-4">
                <div class="card-header bg-gradient-info text-white d-flex justify-content-between align-items-center rounded-top-4">
                    <h5 class="mb-0">
                        <i class="bi bi-people-fill me-2"></i> Usuarios Registrados
                    </h5>
                    <button type="button" class="btn btn-light btn-sm" id="btnActualizarTabla">
                        <i class="bi bi-arrow-clockwise me-1"></i> Actualizar
                    </button>
                </div>
                <div class="card-body">
                    <div id="loadingTabla" class="text-center py-4 d-none">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando usuarios...</span>
                        </div>
                        <p class="mt-2 text-muted">Cargando usuarios...</p>
                    </div>
                    <div id="tablaContainer" class="text-center py-4 text-muted">
                        <i class="bi bi-table display-1"></i>
                        <p class="mt-2">Los usuarios registrados aparecerán aquí automáticamente</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- SCRIPT PARA CARGAR EL JAVASCRIPT -->
<script src="<?= asset('build/js/usuario/index.js') ?>"></script>