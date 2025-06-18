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
            --navbar-bg: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f0f23 100%);
            --dropdown-bg: rgba(26, 26, 46, 0.98);
            --hover-color: #ffc107;
            --text-color: #ffffff;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: black;
            color: black;
            background-image: url('<?= asset('images/logo.png') ?>');
        }

        /* ========== ESTILOS ORIGINALES DEL HERO Y CONTENIDO ========== */
        
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

        /* ========== NUEVOS ESTILOS DEL NAVBAR MEJORADO ========== */

        /* Navbar principal mejorado */
        .navbar-custom {
            background: var(--navbar-bg);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            position: sticky;
            top: 0;
            z-index: 1050;
        }

        .navbar-brand {
            font-weight: bold;
            font-size: 1.4rem;
            color: var(--text-color) !important;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }

        .navbar-brand:hover {
            color: var(--primary-color) !important;
            transform: scale(1.05);
        }

        .navbar-brand img {
            border-radius: 50%;
            transition: transform 0.3s ease;
        }

        .navbar-brand:hover img {
            transform: rotate(360deg);
        }

        /* Links del navbar */
        .navbar-nav .nav-link {
            color: var(--text-color) !important;
            font-weight: 500;
            padding: 0.8rem 1rem !important;
            border-radius: 8px;
            margin: 0 0.2rem;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .navbar-nav .nav-link:hover {
            color: var(--hover-color) !important;
            background: rgba(255, 193, 7, 0.1);
            transform: translateY(-2px);
        }

        .navbar-nav .nav-link.active {
            color: var(--primary-color) !important;
            background: rgba(0, 212, 255, 0.15);
            box-shadow: 0 0 15px rgba(0, 212, 255, 0.3);
        }

        /* Dropdown mejorado */
        .dropdown-menu {
            background: var(--dropdown-bg);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(10px);
            margin-top: 0.5rem;
            padding: 0.5rem;
            min-width: 250px;
        }

        .dropdown-item {
            color: var(--text-color);
            padding: 0.8rem 1rem;
            border-radius: 8px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.8rem;
            margin: 0.2rem 0;
        }

        .dropdown-item:hover {
            background: rgba(255, 193, 7, 0.15);
            color: var(--hover-color);
            transform: translateX(5px);
        }

        .dropdown-item:focus {
            background: rgba(0, 212, 255, 0.15);
            color: var(--primary-color);
        }

        /* Iconos en dropdown */
        .dropdown-item i {
            width: 20px;
            text-align: center;
            font-size: 1.1rem;
        }

        /* Separadores en dropdown */
        .dropdown-divider {
            border-color: rgba(255, 255, 255, 0.2);
            margin: 0.5rem 0;
        }

        /* Botón de menú mejorado */
        .btn-menu {
            background: linear-gradient(135deg, #dc3545, #c82333);
            border: none;
            border-radius: 8px;
            color: white;
            font-weight: 600;
            padding: 0.6rem 1.2rem;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(220, 53, 69, 0.3);
        }

        .btn-menu:hover {
            background: linear-gradient(135deg, #c82333, #a71e2a);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(220, 53, 69, 0.4);
            color: white;
        }

        /* Toggler personalizado */
        .navbar-toggler {
            border: none;
            padding: 0.25rem 0.5rem;
        }

        .navbar-toggler:focus {
            box-shadow: 0 0 0 0.25rem rgba(0, 212, 255, 0.25);
        }

        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%28255, 255, 255, 0.8%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
        }

        /* Efectos hover para grupos - DESHABILITADO para usar solo click */
        /* .dropdown:hover .dropdown-menu {
            display: block;
            animation: fadeInDown 0.3s ease;
        } */

        /* Animación para dropdowns al hacer click */
        .dropdown-menu.show {
            animation: fadeInDown 0.3s ease;
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive */
        @media (max-width: 991.98px) {
            .dropdown-menu {
                position: static !important;
                transform: none !important;
                background: rgba(26, 26, 46, 0.95);
                margin-top: 0.5rem;
                border: none;
                box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.1);
            }
            
            .navbar-nav .nav-link {
                margin: 0.2rem 0;
            }
        }

        /* Badges para notificaciones */
        .notification-badge {
            position: absolute;
            top: 5px;
            right: 5px;
            background: #dc3545;
            color: white;
            border-radius: 50%;
            font-size: 0.7rem;
            padding: 0.2rem 0.4rem;
            min-width: 18px;
            height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>

</head>
<body>
<nav class="navbar navbar-expand-lg navbar-custom">
    <div class="container-fluid">

        <!-- Brand -->
        <a class="navbar-brand" href="/empresa_celulares/">
            <img src="<?= asset('./images/logo.png') ?>" width="35px" alt="movilcare">
            <span>MovilCare</span>
        </a>

        <!-- Toggler -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navbar content -->
        <div class="collapse navbar-collapse" id="navbarContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">

                <!-- Dashboard -->
                <li class="nav-item">
                    <a class="nav-link active" href="/empresa_celulares/">
                        <i class="bi bi-house-fill"></i>
                        Dashboard
                    </a>
                </li>

                <!-- Ventas & Operaciones -->
                <?php if (isset($_SESSION['VEN_VIEW']) || isset($_SESSION['REP_VIEW'])): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-cash-stack"></i>
                            Ventas & Operaciones
                        </a>
                        <ul class="dropdown-menu">
                            <?php if (isset($_SESSION['VEN_VIEW'])): ?>
                                <li>
                                    <a class="dropdown-item" href="/empresa_celulares/venta">
                                        <i class="bi bi-cash-coin"></i>
                                        Ventas
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php if (isset($_SESSION['REP_VIEW'])): ?>
                                <li>
                                    <a class="dropdown-item" href="/empresa_celulares/recepcion">
                                        <i class="bi bi-inbox"></i>
                                        Recepción
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="/empresa_celulares/ordentrabajo">
                                        <i class="bi bi-clipboard-check"></i>
                                        Orden de Trabajo
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </li>
                <?php endif; ?>

                <!-- Servicios & Productos -->
                <?php if (isset($_SESSION['INV_VIEW'])): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-gear-wide-connected"></i>
                            Servicios & Productos
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="/empresa_celulares/tiposervicio">
                                    <i class="bi bi-gear-wide-connected"></i>
                                    Tipos de Servicio
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="/empresa_celulares/inventario">
                                    <i class="bi bi-box-seam"></i>
                                    Inventario
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="/empresa_celulares/marca">
                                    <i class="bi bi-award"></i>
                                    Marcas
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="/empresa_celulares/modelo">
                                    <i class="bi bi-phone"></i>
                                    Modelos
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php endif; ?>

                <!-- Gestión de Personas -->
                <?php if (isset($_SESSION['CLI_VIEW'])): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-people"></i>
                            Gestión de Personas
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="/empresa_celulares/cliente">
                                    <i class="bi bi-person-heart"></i>
                                    Clientes
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="/empresa_celulares/empleado">
                                    <i class="bi bi-person-badge"></i>
                                    Empleados
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php endif; ?>

                <!-- Administración -->
                <?php if (isset($_SESSION['SEG_ADMIN'])): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-people"></i>
                            Admin
                        </a>
                        <ul class="dropdown-menu">
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="/empresa_celulares/usuario">
                                    <i class="bi bi-person-gear"></i>
                                    Usuarios
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="/empresa_celulares/aplicacion">
                                    <i class="bi bi-shield-check"></i>
                                    Aplicacion
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="/empresa_celulares/permiso">
                                    <i class="bi bi-shield-check"></i>
                                    Permiso
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="/empresa_celulares/asignacion_permiso">
                                    <i class="bi bi-shield-check"></i>
                                    Asignar Permiso
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php endif; ?>

            </ul>

            <!-- Botón menú -->
            <div class="d-grid d-lg-block">
                <a href="/menu/" class="btn btn-menu">
                    <i class="bi bi-arrow-bar-left me-2"></i>
                    MENÚ PRINCIPAL
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