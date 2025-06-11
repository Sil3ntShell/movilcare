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
        body {
            background-color: black;
            color: white;
            background-image: url('<?= asset('images/logo.png') ?>');
            background-repeat: no-repeat;
            background-size: cover;
        }
    </style>
</head>
<body>

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