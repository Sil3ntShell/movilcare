 <!-- Hero Section con Video -->
    <section id="inicio" class="hero-section">
        <!-- Video de fondo -->
        <video class="hero-video" autoplay muted loop playsinline>
            <source src="video/techcell-bg.mp4" type="video/mp4">
            <source src="<?= asset('./images/logomovilcare.mp4') ?>" type="video/mp4">
            Tu navegador no soporta videos HTML5.
        </video>
        <div class="hero-overlay"></div>
        
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center">
                    <div class="hero-content">
                        <h1 class="hero-title">MOVILCARE GUATEMALA</h1>
                        <p class="hero-subtitle">Expertos en Reparación y Venta de Celulares</p>
                        <a href="#servicios" class="btn btn-primary btn-primary-custom">
                            <i class="fas fa-arrow-down me-2"></i>Ver Servicios
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Servicios -->
    <section id="servicios" class="py-5">
        <div class="container">
            <div class="row mb-5">
                <div class="col-12 text-center">
                    <h2 class="display-5 fw-bold mb-3">Nuestros Servicios</h2>
                    <p class="lead text-muted">Soluciones completas para tu dispositivo móvil</p>
                </div>
            </div>
            
            <div class="row g-4">
                <!-- Reparación -->
                <div class="col-lg-4 col-md-6">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="fas fa-tools"></i>
                        </div>
                        <h4 class="fw-bold mb-3 t text-danger">Reparación</h4>
                        <p class="text-muted mb-3">Cambio de pantallas, baterías, puertos de carga, cámaras y más. Garantía en todos nuestros trabajos.</p>
                        <ul class="list-unstyled text-start text-primary">
                            <li><i class="fas fa-check text-success me-2 text-primary"></i>Pantallas y LCD</li>
                            <li><i class="fas fa-check text-success me-2 text-primary"></i>Baterías</li>
                            <li><i class="fas fa-check text-success me-2 text-primary"></i>Puertos de carga</li>
                            <li><i class="fas fa-check text-success me-2 text-primary"></i>Cámaras</li>
                        </ul>
                    </div>
                </div>
                
                <!-- Venta -->
                <div class="col-lg-4 col-md-6">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <h4 class="fw-bold mb-3 text-danger">Venta</h4>
                        <p class="text-muted mb-3">Celulares nuevos y usados de las mejores marcas. Precios competitivos y financiamiento disponible.</p>
                        <ul class="list-unstyled text-start text-primary">
                            <li><i class="fas fa-check text-success me-2 text-primary"></i>Samsung</li>
                            <li><i class="fas fa-check text-success me-2 text-primary"></i>iPhone</li>
                            <li><i class="fas fa-check text-success me-2 text-primary"></i>Xiaomi</li>
                            <li><i class="fas fa-check text-success me-2 text-primary"></i>Motorola</li>
                        </ul>
                    </div>
                </div>
                
                <!-- Servicios Técnicos -->
                <div class="col-lg-4 col-md-6">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="fas fa-cogs"></i>
                        </div>
                        <h4 class="fw-bold mb-3 text-danger">Servicios Técnicos</h4>
                        <p class="text-muted mb-3">Liberación, formateo, recuperación de datos y soporte técnico especializado.</p>
                        <ul class="list-unstyled text-start text-primary">
                            <li><i class="fas fa-check text-success me-2 text-primary"></i>Liberación</li>
                            <li><i class="fas fa-check text-success me-2 text-primary"></i>Formateo</li>
                            <li><i class="fas fa-check text-success me-2 text-primary"></i>Recuperación de datos</li>
                            <li><i class="fas fa-check text-success me-2 text-primary"></i>Diagnóstico</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Call to Action -->
            <div class="row mt-5">
                <div class="col-12 text-center">
                    <h3 class="mb-3">¿Necesitas ayuda con tu celular?</h3>
                    <p class="lead mb-4">Contáctanos y obtén una cotización gratuita</p>
                    <div class="d-flex flex-column flex-md-row gap-3 justify-content-center">
                        <h1>+502 3706-2621</h1>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        // Smooth scrolling
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    const offsetTop = target.offsetTop - 80;
                    window.scrollTo({
                        top: offsetTop,
                        behavior: 'smooth'
                    });
                }
            });
        });

        // Video autoplay fallback
        document.addEventListener('DOMContentLoaded', function() {
            const video = document.querySelector('.hero-video');
            if (video) {
                video.play().catch(function(error) {
                    console.log('Video autoplay failed:', error);
                });
            }
        });
    </script>