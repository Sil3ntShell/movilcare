
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="card-title mb-0">
                            <i class="bi bi-box-arrow-in-right me-2"></i>
                            Iniciar Sesión
                        </h4>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-4 text-center">Por favor ingresa tus credenciales</p>
                        
                        <form id="loginForm" novalidate>
                          
                            <!-- Correo Electrónico -->
                            <div class="mb-3">
                                <label for="usuario_correo" class="form-label">
                                    Correo Electrónico <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-envelope"></i>
                                    </span>
                                    <input type="email" class="form-control" id="usuario_correo" name="usuario_correo" 
                                           required placeholder="ejemplo@correo.com">
                                    <div class="invalid-feedback">
                                        Por favor ingrese un correo electrónico válido.
                                    </div>
                                </div>
                            </div>

                            <!-- Contraseña -->
                            <div class="mb-4">
                                <label for="usuario_contra" class="form-label">
                                    Contraseña <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-lock"></i>
                                    </span>
                                    <input type="password" class="form-control" id="usuario_contra" name="usuario_contra" 
                                           required placeholder="Ingresa tu contraseña">
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                        <i class="bi bi-eye" id="toggleIcon"></i>
                                    </button>
                                    <div class="invalid-feedback">
                                        Por favor ingrese su contraseña.
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Esto es por si olvido la contraseña -->
                            <!-- <p class="small mb-5 pb-lg-2"><a class="text-white-50" href="#!">Forgot password?</a></p> -->

                            <!-- Recordar sesión (opcional) -->
                            <!-- <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="recordar_sesion" name="recordar_sesion">
                                    <label class="form-check-label" for="recordar_sesion">
                                        Recordar sesión
                                    </label>
                                </div>
                            </div> -->

                            <!-- Botones -->
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-box-arrow-in-right me-1"></i>
                                    Iniciar Sesión
                                </button>
                            </div>

                            <!-- Es para poder registrarse -->
                            <!-- <div>
                              <p class="mb-0">Don't have an account? <a href="#!" class="text-white-50 fw-bold">Sign Up</a>
                              </p>
                            </div> -->

                            <!-- Es para iniciar sesion con facebook, twitter y google  -->
                            <!-- <div class="d-flex justify-content-center text-center mt-4 pt-1">
                              <a href="#!" class="text-white"><i class="fab fa-facebook-f fa-lg"></i></a>
                              <a href="#!" class="text-white"><i class="fab fa-twitter fa-lg mx-4 px-2"></i></a>
                              <a href="#!" class="text-white"><i class="fab fa-google fa-lg"></i></a>
                            </div> -->

                        </form>
                    </div>
                </div>
            </div>
            <!-- <img src="<?= asset('images/logo.png') ?>" alt="Logo" class="img-fluid mt-3"> -->
        </div>
    </div>