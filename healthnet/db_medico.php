<?php
session_start();



// Leer las citas agendadas
$citas = [];
if (file_exists('citas.txt')) {
    $citas_content = file_get_contents('citas.txt');
    $citas_array = explode("----------------------------------------", $citas_content);
    
    foreach ($citas_array as $cita) {
        if (trim($cita)) {
            $citas[] = $cita;
        }
    }
    $citas = array_reverse($citas); // Mostrar las más recientes primero
}

// Leer mensajes del buzón
$mensajes = [];
if (file_exists('buzon_medico.txt')) {
    $mensajes_content = file_get_contents('buzon_medico.txt');
    $mensajes_array = explode("----------------------------------------", $mensajes_content);
    
    foreach ($mensajes_array as $mensaje) {
        if (trim($mensaje)) {
            $mensajes[] = $mensaje;
        }
    }
    $mensajes = array_reverse($mensajes);
}

// Procesar nuevo mensaje
if (isset($_POST['enviar_mensaje'])) {
    $destinatario = htmlspecialchars(trim($_POST['destinatario']));
    $asunto = htmlspecialchars(trim($_POST['asunto']));
    $contenido = htmlspecialchars(trim($_POST['contenido']));
    
    if (!empty($destinatario) && !empty($asunto) && !empty($contenido)) {
        $nuevo_mensaje = "De: Dr. " . $_SESSION['usuario']['nombre'] . "\n";
        $nuevo_mensaje .= "Para: " . $destinatario . "\n";
        $nuevo_mensaje .= "Asunto: " . $asunto . "\n";
        $nuevo_mensaje .= "Mensaje: " . $contenido . "\n";
        $nuevo_mensaje .= "Fecha: " . date('Y-m-d H:i:s') . "\n";
        $nuevo_mensaje .= "----------------------------------------\n";
        
        if (file_put_contents('buzon_medico.txt', $nuevo_mensaje, FILE_APPEND | LOCK_EX)) {
            $mensaje_exito = "Mensaje enviado correctamente";
        } else {
            $mensaje_error = "Error al enviar el mensaje";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Médico</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f5f7fa;
            color: #333;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header */
        .dashboard-header {
            background: linear-gradient(135deg, #1a6fc4 0%, #0d47a1 100%);
            color: white;
            padding: 20px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .user-info h1 {
            font-size: 1.5rem;
            margin-bottom: 5px;
        }

        .user-info p {
            opacity: 0.9;
        }

        .header-actions a {
            color: white;
            text-decoration: none;
            margin-left: 15px;
            padding: 8px 15px;
            border: 1px solid white;
            border-radius: 5px;
            transition: all 0.3s;
        }

        .header-actions a:hover {
            background-color: white;
            color: #0d47a1;
        }

        /* Dashboard Grid */
        .dashboard-grid {
            display: grid;
            grid-template-columns: 250px 1fr;
            gap: 30px;
            margin: 30px 0;
        }

        /* Sidebar */
        .sidebar {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .sidebar-menu {
            list-style: none;
        }

        .sidebar-menu li {
            margin-bottom: 10px;
        }

        .sidebar-menu a {
            display: block;
            padding: 12px 15px;
            color: #333;
            text-decoration: none;
            border-radius: 5px;
            transition: all 0.3s;
        }

        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background-color: #0d47a1;
            color: white;
        }

        /* Main Content */
        .main-content {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .section-title {
            color: #0d47a1;
            margin-bottom: 25px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e3f2fd;
        }

        /* Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 10px;
            text-align: center;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .stat-label {
            opacity: 0.9;
        }

        /* Citas List */
        .citas-list {
            max-height: 400px;
            overflow-y: auto;
        }

        .cita-item {
            background: #f8f9fa;
            padding: 20px;
            margin-bottom: 15px;
            border-radius: 8px;
            border-left: 4px solid #0d47a1;
        }

        .cita-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .cita-paciente {
            font-weight: bold;
            color: #0d47a1;
        }

        .cita-fecha {
            color: #666;
            font-size: 0.9rem;
        }

        .cita-detalles {
            color: #555;
            line-height: 1.5;
        }

        /* Buzón */
        .mensajes-list {
            max-height: 400px;
            overflow-y: auto;
            margin-bottom: 30px;
        }

        .mensaje-item {
            background: #f8f9fa;
            padding: 20px;
            margin-bottom: 15px;
            border-radius: 8px;
            border-left: 4px solid #28a745;
        }

        .mensaje-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .mensaje-de {
            font-weight: bold;
            color: #0d47a1;
        }

        .mensaje-asunto {
            color: #333;
            font-weight: 500;
        }

        .mensaje-fecha {
            color: #666;
            font-size: 0.9rem;
        }

        .mensaje-contenido {
            color: #555;
            line-height: 1.5;
            margin-top: 10px;
        }

        /* Formulario Mensaje */
        .form-mensaje {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 8px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }

        .form-group textarea {
            height: 120px;
            resize: vertical;
        }

        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn-primary {
            background-color: #0d47a1;
            color: white;
        }

        .btn-primary:hover {
            background-color: #1a6fc4;
        }

        /* Mensajes de éxito/error */
        .mensaje {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
            font-weight: bold;
        }

        .mensaje.exito {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .mensaje.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
            
            .sidebar {
                order: 2;
            }
            
            .main-content {
                order: 1;
            }
            
            .header-content {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="dashboard-header">
        <div class="container">
            <div class="header-content">
                <div class="user-info">
        
                    <p>Panel de Control Médico</p>
                </div>
                <div class="header-actions">
                    <a href="inicios.php">Cerrar Sesión</a>
                </div>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="dashboard-grid">
            <!-- Sidebar -->
            <nav class="sidebar">
                <ul class="sidebar-menu">
                    <li><a href="#dashboard" class="active" onclick="mostrarSeccion('dashboard')">📊 Dashboard</a></li>
                    <li><a href="#citas" onclick="mostrarSeccion('citas')">📅 Citas Agendadas</a></li>
                    <li><a href="#buzon" onclick="mostrarSeccion('buzon')">📬 Buzón de Mensajes</a></li>
                    <li><a href="#perfil" onclick="mostrarSeccion('perfil')">👤 Mi Perfil</a></li>
                </ul>
            </nav>

            <main class="main-content">
                <section id="seccion-dashboard" class="dashboard-section">
                    <h2 class="section-title">Dashboard</h2>
                    
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-number"><?php echo count($citas); ?></div>
                            <div class="stat-label">Citas Totales</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number"><?php echo count($mensajes); ?></div>
                            <div class="stat-label">Mensajes Recibidos</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number"><?php echo date('d/m/Y'); ?></div>
                            <div class="stat-label">Fecha Actual</div>
                        </div>
                    </div>

                    <h3 style="margin: 30px 0 15px 0; color: #0d47a1;">Próximas Citas</h3>
                    <div class="citas-list">
                        <?php if (empty($citas)): ?>
                            <p>No hay citas agendadas.</p>
                        <?php else: ?>
                            <?php for ($i = 0; $i < min(3, count($citas)); $i++): ?>
                                <div class="cita-item">
                                    <?php
                                    $lineas = explode("\n", $citas[$i]);
                                    $paciente = $hospital = $especialidad = $fecha = '';
                                    foreach ($lineas as $linea) {
                                        if (strpos($linea, 'Nombre:') !== false) {
                                            $paciente = trim(str_replace('Nombre:', '', $linea));
                                        } elseif (strpos($linea, 'Hospital:') !== false) {
                                            $hospital = trim(str_replace('Hospital:', '', $linea));
                                        } elseif (strpos($linea, 'Especialidad:') !== false) {
                                            $especialidad = trim(str_replace('Especialidad:', '', $linea));
                                        } elseif (strpos($linea, 'Fecha preferida:') !== false) {
                                            $fecha = trim(str_replace('Fecha preferida:', '', $linea));
                                        }
                                    }
                                    ?>
                                    <div class="cita-header">
                                        <span class="cita-paciente"><?php echo $paciente; ?></span>
                                        <span class="cita-fecha"><?php echo $fecha; ?></span>
                                    </div>
                                    <div class="cita-detalles">
                                        <p><strong>Hospital:</strong> <?php echo $hospital; ?></p>
                                        <p><strong>Especialidad:</strong> <?php echo $especialidad; ?></p>
                                    </div>
                                </div>
                            <?php endfor; ?>
                        <?php endif; ?>
                    </div>
                </section>

                <!-- Sección Citas -->
                <section id="seccion-citas" class="dashboard-section" style="display: none;">
                    <h2 class="section-title">Citas Agendadas</h2>
                    
                    <div class="citas-list">
                        <?php if (empty($citas)): ?>
                            <p>No hay citas agendadas.</p>
                        <?php else: ?>
                            <?php foreach ($citas as $cita): ?>
                                <div class="cita-item">
                                    <?php
                                    $lineas = explode("\n", $cita);
                                    $paciente = $hospital = $especialidad = $fecha = $telefono = $email = $total = '';
                                    foreach ($lineas as $linea) {
                                        if (strpos($linea, 'Nombre:') !== false) {
                                            $paciente = trim(str_replace('Nombre:', '', $linea));
                                        } elseif (strpos($linea, 'Hospital:') !== false) {
                                            $hospital = trim(str_replace('Hospital:', '', $linea));
                                        } elseif (strpos($linea, 'Especialidad:') !== false) {
                                            $especialidad = trim(str_replace('Especialidad:', '', $linea));
                                        } elseif (strpos($linea, 'Fecha preferida:') !== false) {
                                            $fecha = trim(str_replace('Fecha preferida:', '', $linea));
                                        } elseif (strpos($linea, 'Teléfono:') !== false) {
                                            $telefono = trim(str_replace('Teléfono:', '', $linea));
                                        } elseif (strpos($linea, 'Email:') !== false) {
                                            $email = trim(str_replace('Email:', '', $linea));
                                        } elseif (strpos($linea, 'Total:') !== false) {
                                            $total = trim(str_replace('Total:', '', $linea));
                                        }
                                    }
                                    ?>
                                    <div class="cita-header">
                                        <span class="cita-paciente"><?php echo $paciente; ?></span>
                                        <span class="cita-fecha"><?php echo $fecha; ?></span>
                                    </div>
                                    <div class="cita-detalles">
                                        <p><strong>Hospital:</strong> <?php echo $hospital; ?></p>
                                        <p><strong>Especialidad:</strong> <?php echo $especialidad; ?></p>
                                        <p><strong>Contacto:</strong> <?php echo $telefono; ?> | <?php echo $email; ?></p>
                                        <p><strong>Total:</strong> <?php echo $total; ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </section>

                <section id="seccion-buzon" class="dashboard-section" style="display: none;">
                    <h2 class="section-title">Buzón de Mensajes</h2>
                    
                    <?php if (isset($mensaje_exito)): ?>
                        <div class="mensaje exito"><?php echo $mensaje_exito; ?></div>
                    <?php endif; ?>
                    
                    <?php if (isset($mensaje_error)): ?>
                        <div class="mensaje error"><?php echo $mensaje_error; ?></div>
                    <?php endif; ?>

                    <h3 style="margin: 25px 0 15px 0; color: #0d47a1;">Mensajes Recibidos</h3>
                    <div class="mensajes-list">
                        <?php if (empty($mensajes)): ?>
                            <p>No hay mensajes en el buzón.</p>
                        <?php else: ?>
                            <?php foreach ($mensajes as $mensaje): ?>
                                <div class="mensaje-item">
                                    <?php
                                    $lineas = explode("\n", $mensaje);
                                    $de = $para = $asunto = $contenido = $fecha = '';
                                    foreach ($lineas as $linea) {
                                        if (strpos($linea, 'De:') !== false) {
                                            $de = trim(str_replace('De:', '', $linea));
                                        } elseif (strpos($linea, 'Para:') !== false) {
                                            $para = trim(str_replace('Para:', '', $linea));
                                        } elseif (strpos($linea, 'Asunto:') !== false) {
                                            $asunto = trim(str_replace('Asunto:', '', $linea));
                                        } elseif (strpos($linea, 'Mensaje:') !== false) {
                                            $contenido = trim(str_replace('Mensaje:', '', $linea));
                                        } elseif (strpos($linea, 'Fecha:') !== false) {
                                            $fecha = trim(str_replace('Fecha:', '', $linea));
                                        }
                                    }
                                    ?>
                                    <div class="mensaje-header">
                                        <div>
                                            <span class="mensaje-de"><?php echo $de; ?></span>
                                            <span class="mensaje-asunto"> - <?php echo $asunto; ?></span>
                                        </div>
                                        <span class="mensaje-fecha"><?php echo $fecha; ?></span>
                                    </div>
                                    <div class="mensaje-contenido">
                                        <?php echo $contenido; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <h3 style="margin: 30px 0 15px 0; color: #0d47a1;">Enviar Nuevo Mensaje</h3>
                    <form method="POST" class="form-mensaje">
                        <div class="form-group">
                            <label for="destinatario">Para:</label>
                            <input type="text" id="destinatario" name="destinatario" required 
                                   placeholder="Nombre del destinatario">
                        </div>
                        <div class="form-group">
                            <label for="asunto">Asunto:</label>
                            <input type="text" id="asunto" name="asunto" required 
                                   placeholder="Asunto del mensaje">
                        </div>
                        <div class="form-group">
                            <label for="contenido">Mensaje:</label>
                            <textarea id="contenido" name="contenido" required 
                                      placeholder="Escribe tu mensaje aquí..."></textarea>
                        </div>
                        <button type="submit" name="enviar_mensaje" class="btn btn-primary">Enviar Mensaje</button>
                    </form>
                </section>

                <!-- Sección Perfil -->
                <section id="seccion-perfil" class="dashboard-section" style="display: none;">
                    <h2 class="section-title">Mi Perfil</h2>
                    
                    <div style="background: #f8f9fa; padding: 25px; border-radius: 8px;">
                        <h3 style="color: #0d47a1; margin-bottom: 20px;">Información Personal</h3>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <div>
                                <p><strong>Nombre:</strong> Dr. <?php echo $_SESSION['usuario']['nombre']; ?></p>
                                <p><strong>Email:</strong> <?php echo $_SESSION['usuario']['email']; ?></p>
                                <p><strong>Teléfono:</strong> <?php echo $_SESSION['usuario']['telefono']; ?></p>
                            </div>
                            <div>
                                <p><strong>Tipo de Usuario:</strong> <?php echo $_SESSION['usuario']['tipo']; ?></p>
                                <p><strong>Usuario:</strong> <?php echo $_SESSION['usuario']['username']; ?></p>
                                <p><strong>Especialidad:</strong> Cardiología</p>
                            </div>
                        </div>
                    </div>

                    <div style="background: #e8f5e8; padding: 20px; border-radius: 8px; margin-top: 25px;">
                        <h3 style="color: #2e7d32; margin-bottom: 15px;">Estadísticas</h3>
                        <p><strong>Citas atendidas este mes:</strong> 24</p>
                        <p><strong>Pacientes activos:</strong> 156</p>
                        <p><strong>Calificación promedio:</strong> ⭐⭐⭐⭐☆ (4.2/5)</p>
                    </div>
                </section>
            </main>
        </div>
    </div>

    <script>
        // Navegación entre secciones
        function mostrarSeccion(seccion) {
            // Ocultar todas las secciones
            document.querySelectorAll('.dashboard-section').forEach(sec => {
                sec.style.display = 'none';
            });
            
            // Mostrar la sección seleccionada
            document.getElementById('seccion-' + seccion).style.display = 'block';
            
            // Actualizar menú activo
            document.querySelectorAll('.sidebar-menu a').forEach(link => {
                link.classList.remove('active');
            });
            event.target.classList.add('active');
        }

        // Mostrar dashboard por defecto
        document.addEventListener('DOMContentLoaded', function() {
            mostrarSeccion('dashboard');
        });
    </script>
</body>
</html>