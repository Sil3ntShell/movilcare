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
            color: white;
            background-image: url('<?= asset('images/logo.png') ?>');
        }

        /* Navbar */
        .navbar-custom {
            background: rgba(0, 0, 0, 0.9) !important;
            backdrop-filter: blur(10px);
        }

        .navbar-brand {
            color: var(--primary-color) !important;
            font-weight: bold;
            font-size: 1.8rem;
        }

        .navbar-nav .nav-link {
            color: white !important;
            font-weight: 500;
        }

        .navbar-nav .nav-link:hover {
            color: var(--primary-color) !important;
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
    </style>


</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark  bg-dark">
        
        <div class="container-fluid">

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarToggler" aria-controls="navbarToggler" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <a class="navbar-brand" href="/ejemplo/">
                <img src="<?= asset('./images/cit.png') ?>" width="35px'" alt="cit" >
                Aplicaciones
            </a>
            <div class="collapse navbar-collapse" id="navbarToggler">
                
                <ul class="navbar-nav me-auto mb-2 mb-lg-0" style="margin: 0;">
                    <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="/ejemplo/"><i class="bi bi-house-fill me-2"></i>Inicio</a>
                    </li>
  
                    <div class="nav-item dropdown " >
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="bi bi-gear me-2"></i>Dropdown
                        </a>
                        <ul class="dropdown-menu  dropdown-menu-dark "id="dropwdownRevision" style="margin: 0;">
                            <!-- <h6 class="dropdown-header">Información</h6> -->
                            <li>
                                <a class="dropdown-item nav-link text-white " href="/aplicaciones/nueva"><i class="ms-lg-0 ms-2 bi bi-plus-circle me-2"></i>Subitem</a>
                            </li>
                        
                    
                        
                        </ul>
                    </div> 

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
        
        <?php echo $contenido; ?>
    </div>
     <!-- Footer -->
    <footer class="py-4 footer-section">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <div class="social-links mb-3">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-whatsapp"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                        <a href="#"><i class="fab fa-tiktok"></i></a>
                    </div>
                    <p class="mb-1">&copy; 2024 TechCell Guatemala. Todos los derechos reservados.</p>
                    <p class="text-muted">Expertos en reparación y venta de celulares en Guatemala</p>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>