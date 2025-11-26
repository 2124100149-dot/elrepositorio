<?php
// Incluir configuración para las rutas
require_once __DIR__ . '/../../config/config.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Red Medica Healthnet - Cuidando tu salud</title>
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>/estilo_P.css">    
</head>
<body>
    <div class="user-panel">
        <div class="container">
            <div class="user-info">
                <div class="user-welcome">
                    <?php if (isset($_SESSION['correo'])): ?>
                        Bienvenido, <span><?php echo explode('@', $_SESSION['correo'])[0]; ?></span> 
                        (<?php echo $_SESSION['rol']; ?>)
                    <?php else: ?>
                        Bienvenido
                    <?php endif; ?>
                </div>
                <div class="user-actions">
                    <?php if (isset($_SESSION['correo'])): ?>
                        <a href="<?php echo BASE_URL; ?>/public/index.php?logout=1">Cerrar Sesión</a>
                    <?php else: ?>
                        <a href="<?php echo BASE_URL; ?>/public/index.php">Iniciar Sesión</a>
                        <a href="<?php echo BASE_URL; ?>/public/registro.php">Registrarse</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <header>
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <div class="logo-icon"><img src="<?php echo IMAGES_URL; ?>/logoH.png" width="50px" height="50px" ></div>
                    <h1>Hospital Healthnet</h1>
                </div>
                <nav>
                    <ul>
                        <li><a href="#inicio">Inicio</a></li>
                        <li>
                            <form method="GET" class="buscador-header" id="formBuscador">
                                <input type="text" name="busqueda" placeholder="Buscar hospitales, servicios, especialidades..." 
                                value="<?php echo htmlspecialchars($terminoBusqueda); ?>">
                                <input type="hidden" name="tipo_busqueda" id="tipoBusqueda" value="<?php echo $tipoBusqueda; ?>">
                                <button type="submit" name="buscar">🔍</button>
                            </form>
                        </li>
                        <li><a href="#servicios">Servicios</a></li>
                        <li><a href="#especialidades">Especialidades</a></li>
                        <li><a href="#contacto" class="cta-button">Pedir Cita</a></li>
                    </ul>
                </nav> 
            </div>
        </div>
    </header>

    <section id="resultados-busqueda" class="resultados-section">
        <div class="container">
            <div class="section-title">
                <h2 style="color: white;">
                    <?php echo empty($terminoBusqueda) ? 'Todos los Hospitales Disponibles' : 'Resultados de Búsqueda'; ?>
                </h2>
                <?php if (!empty($terminoBusqueda)): ?>
                    <p style="color: rgba(255,255,255,0.8);">
                        Búsqueda: "<?php echo htmlspecialchars($terminoBusqueda); ?>"
                        <span class="metodo-busqueda">
                            <?php 
                                switch($tipoBusqueda) {
                                    case 'servicios': echo 'Búsqueda por Servicios'; break;
                                    case 'especialidades': echo 'Búsqueda por Especialidades'; break;
                                    default: echo 'Búsqueda General'; break;
                                }
                            ?>
                        </span>
                    </p>
                <?php else: ?>
                    <p style="color: rgba(255,255,255,0.8);">
                        Explora nuestra red de hospitales asociados
                    </p>
                <?php endif; ?>
            </div>

            <div class="filtros-busqueda">
                <button type="button" class="filtro-btn <?php echo $tipoBusqueda == 'general' ? 'active' : ''; ?>" 
                        onclick="cambiarFiltro('general')">
                    🔍 Búsqueda General
                </button>
                <button type="button" class="filtro-btn <?php echo $tipoBusqueda == 'servicios' ? 'active' : ''; ?>" 
                        onclick="cambiarFiltro('servicios')">
                    🏥 Servicios Hospitalarios
                </button>
                <button type="button" class="filtro-btn <?php echo $tipoBusqueda == 'especialidades' ? 'active' : ''; ?>" 
                        onclick="cambiarFiltro('especialidades')">
                    👨‍⚕️ Especialidades Médicas
                </button>
            </div>
            
            <div class="resultados-container">
                <?php if (!empty($resultadosBusqueda)): ?>
                    <div class="contador-resultados">
                        <?php echo count($resultadosBusqueda); ?> hospital(es) encontrado(s)
                    </div>
                    
                    <div class="hospitales-grid">
                        <?php foreach ($resultadosBusqueda as $hospital): ?>
                            <div class="hospital-card">
                                <h3>🏥 <?php echo $hospital['nombre']; ?></h3>
                                <p><strong>📍 Dirección:</strong> <?php echo $hospital['direccion']; ?></p>
                                <p><strong>📞 Teléfono:</strong> <?php echo $hospital['telefono']; ?></p>
                                <p><strong>🏙️ Municipio:</strong> <?php echo $hospital['municipio']; ?></p>
                                <p><strong>🏛️ Estado:</strong> <?php echo $hospital['estado']; ?></p>
                                
                                <?php if (isset($hospital['servicios'])): ?>
                                <div class="servicios-hospital">
                                    <strong>🛠️ Servicios:</strong>
                                    <div class="etiquetas">
                                        <?php foreach (array_slice($hospital['servicios'], 0, 3) as $servicio): ?>
                                            <span class="etiqueta"><?php echo $servicio; ?></span>
                                        <?php endforeach; ?>
                                        <?php if (count($hospital['servicios']) > 3): ?>
                                            <span class="etiqueta">+<?php echo count($hospital['servicios']) - 3; ?> más</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <?php if (isset($hospital['especialidades'])): ?>
                                <div class="especialidades-hospital">
                                    <strong>🎯 Especialidades:</strong>
                                    <div class="etiquetas">
                                        <?php foreach (array_slice($hospital['especialidades'], 0, 3) as $especialidad): ?>
                                            <span class="etiqueta especialidad"><?php echo $especialidad; ?></span>
                                        <?php endforeach; ?>
                                        <?php if (count($hospital['especialidades']) > 3): ?>
                                            <span class="etiqueta">+<?php echo count($hospital['especialidades']) - 3; ?> más</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <button onclick="seleccionarHospital('<?php echo $hospital['nombre']; ?>')" 
                                        class="btn-seleccionar">
                                    Seleccionar este Hospital
                                </button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="sin-resultados">
                        <h3> No se encontraron resultados para "<?php echo htmlspecialchars($terminoBusqueda); ?>"</h3>
                        <p>Intenta con otros términos de búsqueda como: "Odontología", "Urgencias", "Cardiología", etc.</p>
                        
                        <div class="sugerencias-busqueda">
                            <h4> Sugerencias de búsqueda:</h4>
                            <div class="sugerencias-lista">
                                <span class="sugerencia" onclick="buscarSugerencia('Odontología')">Odontología</span>
                                <span class="sugerencia" onclick="buscarSugerencia('Urgencias')">Urgencias</span>
                                <span class="sugerencia" onclick="buscarSugerencia('Cardiología')">Cardiología</span>
                                <span class="sugerencia" onclick="buscarSugerencia('Pediatría')">Pediatría</span>
                                <span class="sugerencia" onclick="buscarSugerencia('Cirugía')">Cirugía</span>
                                <span class="sugerencia" onclick="buscarSugerencia('Ciudad de México')">Ciudad de México</span>
                                <span class="sugerencia" onclick="buscarSugerencia('Jalisco')">Jalisco</span>
                                <span class="sugerencia" onclick="buscarSugerencia('Nuevo León')">Nuevo León</span>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section id="inicio" class="hero">
        <div class="container">
            <h2>Cuidamos de tu salud con excelencia</h2>
            <p>Hospital Healthnet - Siempre brindando atención médica de calidad con equipo humano comprometido con tu bienestar.</p>
            <div class="hero-buttons">
                <a href="#contacto" class="btn btn-primary">Solicitar Cita</a>
                <a href="#servicios" class="btn btn-secondary">Conocer Servicios</a>
            </div>
        </div>
    </section>

    <section id="servicios" class="services">
        <div class="container">
            <div class="section-title">
                <h2>Nuestros Servicios</h2>
                <p>Ofrecemos una amplia gama de servicios médicos con los más altos estándares de calidad</p>
            </div>
            <div class="services-grid">
                <div class="service-card">
                    <div class="service-icon">🚑</div>
                    <div class="service-content">
                        <h3>Urgencias 24/7</h3>
                        <p>Atención médica inmediata las 24 horas del día, los 365 días del año con personal altamente capacitado.</p>
                    </div>
                </div>
                <div class="service-card">
                    <div class="service-icon">🩺</div>
                    <div class="service-content">
                        <h3>Consultas Externas</h3>
                        <p>Consulta con especialistas en todas las áreas médicas con citas programadas y atención personalizada.</p>
                    </div>
                </div>
                <div class="service-card">
                    <div class="service-icon">🔪</div>
                    <div class="service-content">
                        <h3>Cirugías</h3>
                        <p>Quirófanos equipados con tecnología de última generación para procedimientos de alta complejidad.</p>
                    </div>
                </div>
                <div class="service-card">
                    <div class="service-icon">🧪</div>
                    <div class="service-content">
                        <h3>Laboratorio Clínico</h3>
                        <p>Análisis clínicos y estudios de diagnóstico completos con resultados precisos y oportunos.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="especialidades" class="specialties">
        <div class="container">
            <div class="section-title">
                <h2>Nuestras especialidades</h2>
                <p>Contamos con especialistas en todas las áreas de la medicina para brindarte la mejor atención</p>
            </div>
            <div class="specialties-grid">
                <?php foreach ($especialidades_medicas as $especialidad): ?>
                    <div class="specialty">
                        <i>❤️</i>
                        <h3><?php echo $especialidad; ?></h3>
                        <h4>Consultar precio</h4>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section id="contacto" class="contact">
        <div class="container">
            <div class="section-title">
                <h2>Solicitar Cita Médica</h2>
                <p>Completa el formulario para agendar tu cita.</p>
            </div>

            <div class="contact-container">
                <div class="contact-info">
                    <div class="contact-item">
                        <div class="contact-icon">📞</div>
                        <div>
                            <h3>Teléfono</h3>
                            <p>(555) 123-4567</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon">✉️</div>
                        <div>
                            <h3>Email</h3>
                            <p>info@hospitalHealthnet.com</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon">🕒</div>
                        <div>
                            <h3>Horario de Atención</h3>
                            <p>Lunes a Viernes: 7:00 am - 9:00 pm</p>
                        </div>
                    </div>
                </div>
                <div class="contact-form">
                    <h3>Formulario de Cita</h3>

                    <form action="<?php echo BASE_URL; ?>/public/procesar_cita.php" method="POST" id="formCita">
                        <div class="form-group">
                            <label for="hospital">Hospital Seleccionado *</label>
                            <input type="text" id="hospital" name="hospital" required readonly 
                                   placeholder="Selecciona un hospital de la sección anterior">
                        </div>
                        <div class="form-group">
                            <label for="nombre">Nombre completo *</label>
                            <input type="text" id="nombre" name="nombre" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Correo electrónico *</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="telefono">Teléfono *</label>
                            <input type="tel" id="telefono" name="telefono" required>
                        </div>
                        <div class="form-group">
                            <label for="especialidad">Especialidad requerida *</label>
                            <select id="especialidad" name="especialidad" required onchange="calcularTotal()">
                                <option value="">Selecciona una especialidad</option>
                                <?php foreach ($especialidades_medicas as $especialidad): ?>
                                    <option value="<?php echo $especialidad; ?>"><?php echo $especialidad; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="fecha_preferida">Fecha preferida *</label>
                            <input type="date" id="fecha_preferida" name="fecha_preferida" required>
                        </div>
                        <div class="form-group">
                            <label for="mensaje">Mensaje adicional</label>
                            <textarea id="mensaje" name="mensaje" placeholder="Describe brevemente tu consulta o síntomas"></textarea>
                        </div>
                        <button type="submit" class="submit-btn">Solicitar Cita</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-column">
                    <h3>Hospital healthnet</h3>
                    <p>Siempre brindando atención médica de calidad con equipo humano comprometido con tu bienestar.</p>
                </div>
                <div class="footer-column">
                    <h3>Enlaces Rápidos</h3>
                    <ul>
                        <li><a href="#inicio">Inicio</a></li>
                        <li><a href="#servicios">Servicios</a></li>
                        <li><a href="#especialidades">Especialidades</a></li>
                        <li><a href="#contacto">Contacto</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Servicios</h3>
                    <ul>
                        <li><a href="#">Urgencias</a></li>
                        <li><a href="#">Consultas</a></li>
                        <li><a href="#">Cirugías</a></li>
                        <li><a href="#">Laboratorio</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Contáctanos</h3>
                    <ul>
                        <li>📞 (555) 123-4567</li>
                        <li>✉️ info@hospitalHealthnet.com</li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 Hospital Healthnet.</p>
            </div>
        </div>
    </footer>

    <script>
        function seleccionarHospital(nombreHospital) {
            document.getElementById('hospital').value = nombreHospital;
            document.getElementById('contacto').scrollIntoView({ behavior: 'smooth' });
            
            alert('Hospital "' + nombreHospital + '" seleccionado. Ahora completa el formulario de cita.');
        }

        function cambiarFiltro(tipo) {
            document.getElementById('tipoBusqueda').value = tipo;
            
            // Actualizar clases activas
            document.querySelectorAll('.filtro-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            event.target.classList.add('active');
        }

        function buscarSugerencia(termino) {
            document.querySelector('input[name="busqueda"]').value = termino;
            document.getElementById('formBuscador').submit();
        }

        // SCROLL SUAVE PARA NAVEGACIÓN
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // EFECTO DE SCROLL EN HEADER
        window.addEventListener('scroll', function() {
            const header = document.querySelector('header');
            if (window.scrollY > 100) {
                header.style.boxShadow = '0 5px 20px rgba(0, 0, 0, 0.1)';
            } else {
                header.style.boxShadow = '0 2px 15px rgba(0, 0, 0, 0.1)';
            }
        });

        // SCROLL AUTOMÁTICO A RESULTADOS
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                document.getElementById('resultados-busqueda').scrollIntoView({ 
                    behavior: 'smooth',
                    block: 'start'
                });
            }, 300);
        });
    </script>
</body>
</html>