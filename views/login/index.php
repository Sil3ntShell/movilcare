<div class="d-flex align-items-center justify-content-center min-vh-100">
    <div class="login-card shadow-lg border-0 rounded-4" style="width: 100%; max-width: 400px;">
        
        <!-- HEADER -->
        <div class="card-header bg-primary text-white text-center rounded-top-4 py-4">
            <div class="mb-3">
                <img src="<?= asset('images/logo.png') ?>" alt="Logo MovilCare" style="width: 60px; height: 60px; border-radius: 50%;">
            </div>
            <h3 class="mb-0 fw-bold">MovilCare</h3>
            <p class="mb-0 opacity-75">Sistema de Gestión</p>
        </div>
        
        <!-- BODY -->
        <div class="card-body p-4" style="background: white; color: #333;">
            <form id="FormLogin" method="POST">
                
                <!-- CORREO -->
                <div class="mb-3">
                    <label for="usuario_correo" class="form-label fw-semibold">
                        <i class="bi bi-envelope-at-fill me-2"></i>Correo electrónico
                    </label>
                    <input type="email" 
                           class="form-control form-control-lg" 
                           id="usuario_correo" 
                           name="usuario_correo" 
                           placeholder="Ingrese su correo" 
                           required
                           autocomplete="username">
                    <div class="form-text">
                        <small class="text-muted">Ingrese su correo electrónico registrado</small>
                    </div>
                </div>

                <!-- CONTRASEÑA -->
                <div class="mb-4">
                    <label for="usuario_contra" class="form-label fw-semibold">
                        <i class="bi bi-lock-fill me-2"></i>Contraseña
                    </label>
                    <div class="input-group">
                        <input type="password" 
                               class="form-control form-control-lg" 
                               id="usuario_contra" 
                               name="usuario_contra" 
                               placeholder="Ingrese su contraseña" 
                               required
                               autocomplete="current-password">
                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                            <i class="bi bi-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                </div>

                <!-- BOTÓN -->
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg" id="BtnLogin">
                        <i class="bi bi-box-arrow-in-right me-2"></i>
                        <span id="loginText">Iniciar Sesión</span>
                        <span id="BtnIniciar" class="spinner-border spinner-border-sm ms-2 d-none"></span>
                    </button>
                </div>

            </form>
        </div>
        
        <!-- FOOTER -->
        <div class="card-footer text-center text-muted py-3" style="background: #f8f9fa; border-bottom-left-radius: 1rem; border-bottom-right-radius: 1rem;">
            <small>
                <i class="bi bi-shield-check me-1"></i>
                Sistema seguro - Versión 1.0
            </small>
        </div>
    </div>
</div>

<script src="<?= asset('build/js/login/index.js') ?>"></script>
