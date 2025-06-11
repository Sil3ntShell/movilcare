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
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        
        <div class="container-fluid">

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarToggler" aria-controls="navbarToggler" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <a class="navbar-brand" href="/empresa_celulares/">
                <img src="<?= asset('./images/logo.png') ?>" width="35px" alt="movilcare">
                MovilCare
            </a>
            <div class="collapse navbar-collapse" id="navbarToggler">
                
                <ul class="navbar-nav me-auto mb-2 mb-lg-0" style="margin: 0;">
                    <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="/empresa_celulares/"><i class="bi bi-house-fill me-2"></i>Dashboard</a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="/empresa_celulares/venta"><i class="bi bi-cash-coin me-2"></i>Ventas</a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="/empresa_celulares/recepcion"><i class="bi bi-inbox me-2"></i>Recepción</a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="/empresa_celulares/ordentrabajo"><i class="bi bi-clipboard-check me-2"></i>Orden Trabajo</a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="/empresa_celulares/tiposervicio"><i class="bi bi-gear-wide-connected me-2"></i>Tipo Servicio</a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="/empresa_celulares/inventario"><i class="bi bi-box-seam me-2"></i>Inventario</a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="/empresa_celulares/marca"><i class="bi bi-award me-2"></i>Marca</a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="/empresa_celulares/modelo"><i class="bi bi-phone me-2"></i>Modelo</a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="/empresa_celulares/cliente"><i class="bi bi-person-heart me-2"></i>Cliente</a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="/empresa_celulares/empleado"><i class="bi bi-person-badge me-2"></i>Empleado</a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="/empresa_celulares/usuario"><i class="bi bi-person-gear me-2"></i>Usuario</a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="/empresa_celulares/rol"><i class="bi bi-shield-check me-2"></i>Rol</a>
                    </li>

                </ul> 
                <div class="col-lg-1 d-grid mb-lg-0 mb-2">
                    <!-- Ruta relativa desde el archivo donde se incluye menu.php -->
                    <a href="/menu/" class="btn btn-danger"><i class="bi bi-arrow-bar-left"></i>MENÚ</a>
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