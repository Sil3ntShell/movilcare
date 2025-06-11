<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="build/js/app.js"></script>
    <link rel="shortcut icon" href="<?= asset('images/logo.png') ?>" type="image/x-icon">
    <link rel="stylesheet" href="<?= asset('build/styles.css') ?>">
    <title>MOVILCARE</title>

        <style>
        :root {
            --primary-color: #00d4ff;
            --primary-dark: #0099cc;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: black;
            color: black;
            background-image: url('<?= asset('images/logo.png') ?>');
        }

       .navbar-nav .nav-link {
        font-weight: 500;
        transition: color 0.3s ease;
        }

        .navbar-nav .nav-link:hover,
        .navbar-nav .nav-link.active {
        color: #ffc107 !important;
        }

        .navbar-brand span {
        letter-spacing: 0.5px;
        }


        .btn-login {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            border: none;
            border-radius: 25px;
            font-weight: bold;
        }

        /* Hero Section con Video */
        .hero-section {
            position: relative;
            height: 100vh;
            display: flex;
            align-items: center;
            overflow: hidden;
        }

        .hero-video {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: -2;
        }

        .hero-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.7), rgba(0, 212, 255, 0.3));
            z-index: -1;
        }

        .hero-content {
            color: white;
            text-align: center;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        }

        .hero-title {
            font-size: 4rem;
            font-weight: bold;
            margin-bottom: 1rem;
        }

        .hero-subtitle {
            font-size: 1.5rem;
            margin-bottom: 2rem;
        }

        .btn-primary-custom {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            border: none;
            border-radius: 50px;
            padding: 1rem 2rem;
            font-weight: bold;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }

        .btn-primary-custom:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 212, 255, 0.4);
        }

        /* Servicios */
        .service-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            height: 100%;
        }

        .service-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 212, 255, 0.2);
        }

        .service-icon {
            font-size: 3.5rem;
            color: var(--primary-color);
            margin-bottom: 1.5rem;
        }

        /* Footer simple */
        .footer-simple {
            background: #333;
            color: white;
            padding: 2rem 0;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }
            
            .hero-subtitle {
                font-size: 1.2rem;
            }
        }


        /* ====== ESTILOS PERSONALIZADOS PARA REGISTRO DE USUARIOS ====== */

        /* Formulario: Tarjeta con sombras más suaves y bordes redondeados */
        .card {
            border-radius: 1rem;
            box-shadow: 0 0.5rem 1.2rem rgba(0, 0, 0, 0.1);
        }

        /* Header del formulario */
        .card-header.bg-gradient-primary {
            background: linear-gradient(90deg, #007bff, #0056b3);
            border-top-left-radius: 1rem;
            border-top-right-radius: 1rem;
        }

        /* Header de la tabla */
        .card-header.bg-gradient-info {
            background: linear-gradient(90deg, #17a2b8, #0d6efd);
            border-top-left-radius: 1rem;
            border-top-right-radius: 1rem;
        }

        /* Inputs */
        input.form-control,
        select.form-control,
        textarea.form-control {
            border-radius: 0.5rem;
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        input.form-control:focus,
        textarea.form-control:focus,
        select.form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.2);
        }

        /* Botones */
        button.btn {
            border-radius: 0.5rem;
        }

        /* Iconos dentro de input-group */
        .input-group-text {
            border-radius: 0.5rem 0 0 0.5rem;
            background-color: #f1f1f1;
        }

        /* Previsualización de imagen */
        #previewImg {
            object-fit: cover;
            border-radius: 0.5rem;
            border: 1px solid #dee2e6;
        }

        /* Spinner */
        #loadingSpinner {
            vertical-align: middle;
        }

        /* Sección vacía de la tabla */
        #tablaContainer i {
            color: #adb5bd;
        }

        #tablaContainer p {
            font-size: 1rem;
            color: #6c757d;
        }

    </style>


</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm py-3">
  <div class="container-fluid">
    <a class="navbar-brand d-flex align-items-center gap-2" href="/empresa_celulares">
      <img src="<?= asset('images/logo.png') ?>" alt="Logo" width="36" height="36" class="rounded-circle border border-light">
      <span class="fw-bold fs-5 text-uppercase">MovilCare</span>
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto ms-3 gap-2">
        <li class="nav-item">
          <a class="nav-link active" href="/empresa_celulares">
            <i class="bi bi-house-door-fill me-2"></i>Inicio
          </a>
        </li>

        <li class="nav-item">
          <a class="nav-link" href="/empresa_celulares/usuario">
            <i class="bi bi-person-gear me-2"></i>Administrador
          </a>
        </li>

        <li class="nav-item">
          <a class="nav-link" href="/empresa_celulares/cliente">
            <i class="bi bi-people-fill me-2"></i>Clientes
          </a>
        </li>
      </ul>

      <div class="d-flex align-items-center gap-2">
        <a href="/menu/" class="btn btn-outline-danger">
          <i class="bi bi-arrow-bar-left"></i> Menú
        </a>
      </div>
    </div>
  </div>
</nav>

    <div class="progress fixed-bottom" style="height: 6px;">
        <div class="progress-bar progress-bar-animated bg-danger" id="bar" role="progressbar" aria-valuemin="0" aria-valuemax="100"></div>
    </div>
    <div class="container-fluid pt-5 mb-4" style="min-height: 85vh">
        <!-- Contenido de la pagina -->
        <?php echo $contenido; ?>

    </div>
     <!-- Footer -->
    <footer class="py-4 footer-section">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <p class="mb-1">&copy; 2024 TechCell Guatemala. Todos los derechos reservados.</p>
                    <p class="text-muted">Expertos en reparación y venta de celulares en Guatemala</p>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>