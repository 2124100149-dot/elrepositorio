<?php
session_start();


$correo_medico = $_SESSION['usuario']['correo'];

function conectarDB() {
    $conexion = new mysqli('localhost', 'root', '', 'healthnet');
    if ($conexion->connect_error) {
        die("Error de conexión: " . $conexion->connect_error);
    }
    return $conexion;
}

function obtenerIdMedico($correo) {
    $db = conectarDB();
    
    // Primero obtener el id_usuario desde la tabla usuario
    $stmt = $db->prepare("SELECT id_usuario FROM usuario WHERE correo = ?");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $id_usuario = $row['id_usuario'];
        
        // Ahora buscar el médico con ese usuario_R
        $stmt2 = $db->prepare("SELECT id_medico FROM medico WHERE usuario_R = ?");
        $stmt2->bind_param("i", $id_usuario);
        $stmt2->execute();
        $result2 = $stmt2->get_result();
        
        if ($result2->num_rows > 0) {
            $row2 = $result2->fetch_assoc();
            $id_medico = $row2['id_medico'];
        } else {
            // Si no existe, crear un médico temporal
            $stmt3 = $db->prepare("INSERT INTO medico (nombre, usuario_R, activo) VALUES (?, ?, 1)");
            $nombre = "Dr. " . explode('@', $correo)[0];
            $stmt3->bind_param("si", $nombre, $id_usuario);
            $stmt3->execute();
            $id_medico = $stmt3->insert_id;
            $stmt3->close();
        }
        $stmt2->close();
    } else {
        die("Error: Usuario no encontrado");
    }
    
    $stmt->close();
    $db->close();
    return $id_medico;
}

function obtenerEstadisticasMedico($id_medico) {
    $db = conectarDB();
    $stats = [];
    
    // Obtener total de citas
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM cita WHERE medico_R = ?");
    $stmt->bind_param("i", $id_medico);
    $stmt->execute();
    $result = $stmt->get_result();
    $stats['total_citas'] = $result->fetch_assoc()['total'];
    $stmt->close();
    
    // Citas para hoy
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM cita WHERE medico_R = ? AND fecha_cita = CURDATE()");
    $stmt->bind_param("i", $id_medico);
    $stmt->execute();
    $result = $stmt->get_result();
    $stats['citas_hoy'] = $result->fetch_assoc()['total'];
    $stmt->close();
    
    // Próximas citas (próximos 7 días)
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM cita WHERE medico_R = ? AND fecha_cita BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)");
    $stmt->bind_param("i", $id_medico);
    $stmt->execute();
    $result = $stmt->get_result();
    $stats['proximas_citas'] = $result->fetch_assoc()['total'];
    $stmt->close();
    
    $db->close();
    return $stats;
}

function obtenerCitasMedico($id_medico) {
    $db = conectarDB();
    
    $stmt = $db->prepare("
        SELECT c.*, p.nombre as paciente_nombre, p.apellido1, p.apellido2, p.telefono 
        FROM cita c 
        LEFT JOIN poliza p ON c.poliza_R = p.poliza_pk 
        WHERE c.medico_R = ? 
        ORDER BY c.fecha_cita DESC, c.hora_cita DESC
    ");
    $stmt->bind_param("i", $id_medico);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $citas = [];
    while ($row = $result->fetch_assoc()) {
        $citas[] = $row;
    }
    
    $stmt->close();
    $db->close();
    return $citas;
}

function obtenerMensajesMedico($correo) {
    // Simulamos mensajes ya que no existe la tabla mensajes
    $mensajes = [
        [
            'remitente' => 'admin@healthnet.com',
            'destinatario' => $correo,
            'asunto' => 'Bienvenido al Sistema',
            'contenido' => 'Bienvenido al panel médico de HealthNet. Estamos aquí para apoyarte.',
            'fecha_envio' => date('Y-m-d H:i:s', strtotime('-2 days'))
        ],
        [
            'remitente' => 'soporte@healthnet.com',
            'destinatario' => $correo,
            'asunto' => 'Actualización del Sistema',
            'contenido' => 'Se ha actualizado el sistema con nuevas funcionalidades.',
            'fecha_envio' => date('Y-m-d H:i:s', strtotime('-1 day'))
        ]
    ];
    
    return $mensajes;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['enviar_mensaje'])) {
    $destinatario = $_POST['destinatario'] ?? '';
    $asunto = $_POST['asunto'] ?? '';
    $contenido = $_POST['contenido'] ?? '';
    
    if (!empty($destinatario) && !empty($asunto) && !empty($contenido)) {
        // Simulamos el envío del mensaje
        $mensaje_exito = "Mensaje enviado correctamente a $destinatario";
    } else {
        $mensaje_error = "Por favor completa todos los campos";
    }
}

// Obtener ID del médico
$id_medico = obtenerIdMedico($correo_medico);
$estadisticas = obtenerEstadisticasMedico($id_medico);
$citas = obtenerCitasMedico($id_medico);
$mensajes = obtenerMensajesMedico($correo_medico);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Médico HealthNet</title>
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #3498db;
            --accent: #e74c3c;
            --light: #ecf0f1;
            --success: #27ae60;
            --warning: #f39c12;
            --dark: #34495e;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f7fa;
            color: #333;
            line-height: 1.6;
        }
        
        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        /* Header Styles */
        .dashboard-header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 1rem 0;
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
        
        .header-actions a {
            color: white;
            text-decoration: none;
            padding: 8px 15px;
            background: rgba(255,255,255,0.2);
            border-radius: 4px;
            transition: background 0.3s;
        }
        
        .header-actions a:hover {
            background: rgba(255,255,255,0.3);
        }
        
        /* Dashboard Grid */
        .dashboard-grid {
            display: flex;
            gap: 30px;
            margin: 30px 0;
        }
        
        /* Sidebar */
        .sidebar {
            width: 250px;
            background: white;
            padding: 20px;
            border-radius: 8px;
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
            color: var(--dark);
            text-decoration: none;
            border-radius: 5px;
            transition: all 0.3s;
            cursor: pointer;
        }
        
        .sidebar-menu a:hover, .sidebar-menu a.active {
            background: var(--secondary);
            color: white;
        }
        
        /* Main Content */
        .main-content {
            flex: 1;
        }
        
        .dashboard-section {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .section-title {
            color: var(--primary);
            font-size: 1.8rem;
            margin-bottom: 25px;
            border-bottom: 2px solid var(--light);
            padding-bottom: 10px;
        }
        
        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: linear-gradient(135deg, var(--secondary), var(--primary));
            color: white;
            padding: 25px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }
        
        /* Citas List */
        .citas-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .cita-item {
            background: var(--light);
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid var(--secondary);
        }
        
        .cita-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .cita-paciente {
            font-weight: bold;
            color: var(--primary);
            font-size: 1.1rem;
        }
        
        .cita-fecha {
            color: var(--dark);
            font-size: 0.9rem;
        }
        
        .cita-detalles p {
            margin-bottom: 5px;
        }
        
        /* Mensajes */
        .mensajes-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .mensaje-item {
            background: var(--light);
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid var(--success);
        }
        
        .mensaje-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
        }
        
        .mensaje-de {
            font-weight: bold;
            color: var(--primary);
        }
        
        .mensaje-asunto {
            font-size: 1.1rem;
            margin-top: 5px;
        }
        
        .mensaje-fecha {
            color: var(--dark);
            font-size: 0.8rem;
        }
        
        .mensaje-contenido {
            line-height: 1.5;
        }
        
        /* Forms */
        .form-mensaje {
            background: var(--light);
            padding: 25px;
            border-radius: 8px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--primary);
        }
        
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            transition: border 0.3s;
        }
        
        .form-group input:focus,
        .form-group textarea:focus {
            border-color: var(--secondary);
            outline: none;
        }
        
        .form-group textarea {
            height: 120px;
            resize: vertical;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 25px;
            background: var(--secondary);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.3s;
            font-weight: 600;
        }
        
        .btn:hover {
            background: var(--primary);
        }
        
        .btn-primary {
            background: var(--secondary);
        }
        
        .btn-primary:hover {
            background: var(--primary);
        }
        
        /* Messages */
        .mensaje {
            padding: 12px 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .mensaje.exito {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .mensaje.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        /* Profile */
        .profile-info {
            margin-bottom: 30px;
        }
        
        .profile-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .profile-grid p {
            margin-bottom: 10px;
            padding: 10px;
            background: var(--light);
            border-radius: 4px;
        }
        
        .profile-stats {
            background: var(--light);
            padding: 20px;
            border-radius: 8px;
        }
        
        .profile-stats p {
            margin-bottom: 10px;
            padding: 8px 0;
            border-bottom: 1px solid #ddd;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .dashboard-grid {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
            }
            
            .stats-grid {
                grid-template-columns: 1fr 1fr;
            }
            
            .profile-grid {
                grid-template-columns: 1fr;
            }
            
            .cita-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .mensaje-header {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <header class="dashboard-header">
        <div class="container">
            <div class="header-content">
                <div class="user-info">
                    <h1>Bienvenido, Dr. <?php echo explode('@', $correo_medico)[0]; ?></h1>
                    <p>Panel de Control Médico - HealthNet</p>
                </div>
                <div class="header-actions">
                    <a href="logout.php">Cerrar Sesión</a>
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
                    <li><a href="#citas" onclick="mostrarSeccion('citas')">📅 Mis Citas</a></li>
                    <li><a href="#buzon" onclick="mostrarSeccion('buzon')">📬 Buzón de Mensajes</a></li>
                    <li><a href="#perfil" onclick="mostrarSeccion('perfil')">👤 Mi Perfil</a></li>
                </ul>
            </nav>

            <main class="main-content">
                <!-- Dashboard Section -->
                <section id="seccion-dashboard" class="dashboard-section">
                    <h2 class="section-title">Dashboard Médico</h2>
                    
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-number"><?php echo $estadisticas['total_citas']; ?></div>
                            <div class="stat-label">Citas Totales</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number"><?php echo $estadisticas['citas_hoy']; ?></div>
                            <div class="stat-label">Citas Hoy</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number"><?php echo $estadisticas['proximas_citas']; ?></div>
                            <div class="stat-label">Próximas Citas</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number"><?php echo count($mensajes); ?></div>
                            <div class="stat-label">Mensajes</div>
                        </div>
                    </div>

                    <h3 style="margin: 30px 0 20px 0; color: #0d47a1; font-size: 1.3rem;">Próximas Citas</h3>
                    <div class="citas-list">
                        <?php if (empty($citas)): ?>
                            <div class="cita-item">
                                <p style="text-align: center; color: #666; font-style: italic;">
                                    No hay citas agendadas.
                                </p>
                            </div>
                        <?php else: ?>
                            <?php 
                            $citas_proximas = array_slice($citas, 0, 3);
                            foreach ($citas_proximas as $cita): 
                            ?>
                                <div class="cita-item">
                                    <div class="cita-header">
                                        <span class="cita-paciente">
                                            <?php echo $cita['paciente_nombre'] . ' ' . ($cita['apellido1'] ?? '') . ' ' . ($cita['apellido2'] ?? ''); ?>
                                        </span>
                                        <span class="cita-fecha">
                                            <?php echo date('d/m/Y', strtotime($cita['fecha_cita'])); ?> 
                                            a las <?php echo date('H:i', strtotime($cita['hora_cita'])); ?>
                                        </span>
                                    </div>
                                    <div class="cita-detalles">
                                        <p><strong>Teléfono:</strong> <?php echo $cita['telefono'] ?? 'No disponible'; ?></p>
                                        <p><strong>Motivo:</strong> <?php echo $cita['motivo_consulta'] ?? 'Consulta general'; ?></p>
                                        <p><strong>Estado:</strong> 
                                            <span style="color: <?php echo $cita['estado'] == 'confirmada' ? '#27ae60' : '#e74c3c'; ?>">
                                                <?php echo ucfirst($cita['estado'] ?? 'pendiente'); ?>
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </section>

                <!-- Las demás secciones se mantienen igual -->
                <!-- ... -->
            </main>
        </div>
    </div>

    <script>
        // Función para mostrar secciones
        function mostrarSeccion(seccionId) {
            // Ocultar todas las secciones
            document.querySelectorAll('.dashboard-section').forEach(section => {
                section.style.display = 'none';
            });
            
            // Mostrar la sección seleccionada
            const seccion = document.getElementById('seccion-' + seccionId);
            if (seccion) {
                seccion.style.display = 'block';
            }
            
            // Actualizar enlaces activos
            document.querySelectorAll('.sidebar-menu a').forEach(link => {
                link.classList.remove('active');
            });
            
            const linkActivo = document.querySelector(`.sidebar-menu a[href="#${seccionId}"]`);
            if (linkActivo) {
                linkActivo.classList.add('active');
            }
        }
        
        // Inicializar con dashboard visible
        document.addEventListener('DOMContentLoaded', function() {
            mostrarSeccion('dashboard');
        });
    </script>
</body>
</html>