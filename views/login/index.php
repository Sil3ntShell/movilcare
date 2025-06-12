<div class="row justify-content-center">
    <div class="col-lg-4 col-md-6 col-sm-8">
        <div class="card shadow-lg border-0 rounded-lg mt-5">
            <div class="card-header bg-primary text-white text-center py-4">
                <h3 class="font-weight-light mb-0">
                    <i class="bi bi-phone me-2"></i>MovilCare
                </h3>
                <p class="mb-0">Sistema de Gestión de Celulares</p>
            </div>
            <div class="card-body p-5">
                <form id="formularioLogin">
                    <div class="mb-3">
                        <label class="form-label" for="usu_codigo">
                            <i class="bi bi-person-vcard me-2"></i>DPI
                        </label>
                        <input 
                            class="form-control" 
                            id="usu_codigo" 
                            name="usu_codigo" 
                            type="text" 
                            placeholder="Ingrese su DPI" 
                            maxlength="13"
                            required
                        />
                        <div class="invalid-feedback">
                            Por favor ingrese un DPI válido
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label" for="usu_password">
                            <i class="bi bi-key me-2"></i>Contraseña
                        </label>
                        <div class="input-group">
                            <input 
                                class="form-control" 
                                id="usu_password" 
                                name="usu_password" 
                                type="password" 
                                placeholder="Ingrese su contraseña"
                                required
                            />
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="bi bi-eye" id="eyeIcon"></i>
                            </button>
                        </div>
                        <div class="invalid-feedback">
                            Por favor ingrese su contraseña
                        </div>
                    </div>
                    
                    <div class="d-grid">
                        <button class="btn btn-primary btn-lg" type="submit" id="btnLogin">
                            <i class="bi bi-box-arrow-in-right me-2"></i>
                            Iniciar Sesión
                        </button>
                    </div>
                </form>
            </div>
            
            <div class="card-footer text-center py-3">
                <div class="small text-muted">
                    © <?= date('Y') ?> MovilCare - Sistema de Gestión
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?= asset('build/js/login/index.js') ?>"></script>