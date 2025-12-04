<?php
session_start();

if (!isset($_SESSION['correo']) || $_SESSION['rol'] !== 'medico') {
    header("Location: inicio_s.php");
    exit();
}

require_once __DIR__ . "/conexion.php";
$conexion = conectarDB();

function obtenerEstadisticasMedico($correo) {
    $db = conectarDB();
    $stats = [];
    
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM cita WHERE medico_correo = ?");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $result = $stmt->get_result();
    $stats['total_citas'] = $result->fetch_assoc()['total'];
    $stmt->close();
    
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM cita WHERE medico_correo = ? AND fecha_cita = CURDATE()");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $result = $stmt->get_result();
    $stats['citas_hoy'] = $result->fetch_assoc()['total'];
    $stmt->close();
    
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM cita WHERE medico_correo = ? AND fecha_cita BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $result = $stmt->get_result();
    $stats['proximas_citas'] = $result->fetch_assoc()['total'];
    $stmt->close();
    
    $db->close();
    return $stats;
}

function obtenerCitasMedico($correo) {
    $db = conectarDB();
    
    $stmt = $db->prepare("
        SELECT c.*, p.nombre as paciente_nombre, p.apellido, p.telefono 
        FROM cita c 
        LEFT JOIN paciente p ON c.paciente_id = p.id 
        WHERE c.medico_correo = ? 
        ORDER BY c.fecha_cita DESC, c.hora_cita DESC
    ");
    $stmt->bind_param("s", $correo);
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
    $db = conectarDB();
    
    $stmt = $db->prepare("
        SELECT * FROM mensajes 
        WHERE destinatario = ? OR remitente = ? 
        ORDER BY fecha_envio DESC
    ");
    $stmt->bind_param("ss", $correo, $correo);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $mensajes = [];
    while ($row = $result->fetch_assoc()) {
        $mensajes[] = $row;
    }
    
    $stmt->close();
    $db->close();
    return $mensajes;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['enviar_mensaje'])) {
    $destinatario = $_POST['destinatario'] ?? '';
    $asunto = $_POST['asunto'] ?? '';
    $contenido = $_POST['contenido'] ?? '';
    
    if (!empty($destinatario) && !empty($asunto) && !empty($contenido)) {
        $db = conectarDB();
        $stmt = $db->prepare("INSERT INTO mensajes (remitente, destinatario, asunto, contenido) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $_SESSION['correo'], $destinatario, $asunto, $contenido);
        
        if ($stmt->execute()) {
            $mensaje_exito = "Mensaje enviado correctamente";
        } else {
            $mensaje_error = "Error al enviar el mensaje";
        }
        
        $stmt->close();
        $db->close();
    } else {
        $mensaje_error = "Por favor completa todos los campos";
    }
}

$estadisticas = obtenerEstadisticasMedico($_SESSION['correo']);
$citas = obtenerCitasMedico($_SESSION['correo']);
$mensajes = obtenerMensajesMedico($_SESSION['correo']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Médico HealthNet</title>
    <link rel="stylesheet" href="css/estilo_M.css">
</head>
<body>
    <header class="dashboard-header">
        <div class="container">
            <div class="header-content">
                <div class="user-info">
                    <h1>Bienvenido, Dr. <?php echo explode('@', $_SESSION['correo'])[0]; ?></h1>
                    <p>Panel de Control Médico - HealthNet</p>
                </div>
                <div class="header-actions">
                    <a href="inicio_s.php">Cerrar Sesión</a>
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
                                            <?php echo $cita['paciente_nombre'] . ' ' . ($cita['apellido'] ?? ''); ?>
                                        </span>
                                        <span class="cita-fecha">
                                            <?php echo date('d/m/Y', strtotime($cita['fecha_cita'])); ?> 
                                            a las <?php echo date('H:i', strtotime($cita['hora_cita'])); ?>
                                        </span>
                                    </div>
                                    <div class="cita-detalles">
                                        <p><strong>Teléfono:</strong> <?php echo $cita['telefono'] ?? 'No disponible'; ?></p>
                                        <p><strong>Motivo:</strong> <?php echo $cita['motivo'] ?? 'Consulta general'; ?></p>
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

                <!-- Citas Section -->
                <section id="seccion-citas" class="dashboard-section" style="display: none;">
                    <h2 class="section-title">Mis Citas</h2>
                    
                    <div class="citas-list">
                        <?php if (empty($citas)): ?>
                            <div class="cita-item">
                                <p style="text-align: center; color: #666; font-style: italic;">
                                    No tienes citas agendadas.
                                </p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($citas as $cita): ?>
                                <div class="cita-item">
                                    <div class="cita-header">
                                        <span class="cita-paciente">
                                            <?php echo $cita['paciente_nombre'] . ' ' . ($cita['apellido'] ?? ''); ?>
                                        </span>
                                        <span class="cita-fecha">
                                            <?php echo date('d/m/Y', strtotime($cita['fecha_cita'])); ?> 
                                            a las <?php echo date('H:i', strtotime($cita['hora_cita'])); ?>
                                        </span>
                                    </div>
                                    <div class="cita-detalles">
                                        <p><strong>Teléfono:</strong> <?php echo $cita['telefono'] ?? 'No disponible'; ?></p>
                                        <p><strong>Motivo:</strong> <?php echo $cita['motivo'] ?? 'Consulta general'; ?></p>
                                        <p><strong>Estado:</strong> 
                                            <span style="color: <?php 
                                                echo $cita['estado'] == 'confirmada' ? '#27ae60' : 
                                                     ($cita['estado'] == 'pendiente' ? '#f39c12' : '#e74c3c'); 
                                            ?>">
                                                <?php echo ucfirst($cita['estado'] ?? 'pendiente'); ?>
                                            </span>
                                        </p>
                                        <?php if (!empty($cita['notas'])): ?>
                                            <p><strong>Notas:</strong> <?php echo $cita['notas']; ?></p>
                                        <?php endif; ?>
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

                    <h3 style="margin: 25px 0 20px 0; color: #0d47a1; font-size: 1.3rem;">Mensajes Recibidos</h3>
                    <div class="mensajes-list">
                        <?php if (empty($mensajes)): ?>
                            <div class="mensaje-item">
                                <p style="text-align: center; color: #666; font-style: italic;">
                                    No hay mensajes en tu buzón.
                                </p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($mensajes as $mensaje): ?>
                                <div class="mensaje-item">
                                    <div class="mensaje-header">
                                        <div>
                                            <span class="mensaje-de">
                                                <?php echo $mensaje['remitente'] == $_SESSION['correo'] ? 'Tú' : $mensaje['remitente']; ?>
                                            </span>
                                            <div class="mensaje-asunto"><?php echo $mensaje['asunto']; ?></div>
                                        </div>
                                        <span class="mensaje-fecha">
                                            <?php echo date('d/m/Y H:i', strtotime($mensaje['fecha_envio'])); ?>
                                        </span>
                                    </div>
                                    <div class="mensaje-contenido">
                                        <?php echo nl2br(htmlspecialchars($mensaje['contenido'])); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <h3 style="margin: 30px 0 20px 0; color: #0d47a1; font-size: 1.3rem;">Enviar Nuevo Mensaje</h3>
                    <form method="POST" class="form-mensaje">
                        <div class="form-group">
                            <label for="destinatario">Para:</label>
                            <input type="email" id="destinatario" name="destinatario" required 
                                   placeholder="correo@ejemplo.com">
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

                <section id="seccion-perfil" class="dashboard-section" style="display: none;">
                    <h2 class="section-title">Mi Perfil Médico</h2>
                    
                    <div class="profile-info">
                        <h3 style="color: #0d47a1; margin-bottom: 20px; font-size: 1.3rem;">Información Personal</h3>
                        
                        <div class="profile-grid">
                            <div>
                                <p><strong>Nombre:</strong> Dr. <?php echo explode('@', $_SESSION['correo'])[0]; ?></p>
                                <p><strong>Email:</strong> <?php echo $_SESSION['correo']; ?></p>
                                <p><strong>Rol:</strong> <?php echo ucfirst($_SESSION['rol']); ?></p>
                            </div>
                            <div>
                                <p><strong>Especialidad:</strong> Medicina General</p>
                                <p><strong>Registro:</strong> <?php echo date('d/m/Y'); ?></p>
                                <p><strong>Estado:</strong> <span style="color: #27ae60;">Activo</span></p>
                            </div>
                        </div>
                    </div>

                    <div class="profile-stats">
                        <h3 style="color: #2e7d32; margin-bottom: 15px; font-size: 1.3rem;">Estadísticas</h3>
                        <p><strong>Citas este mes:</strong> <?php echo $estadisticas['total_citas']; ?></p>
                        <p><strong>Citas para hoy:</strong> <?php echo $estadisticas['citas_hoy']; ?></p>
                        <p><strong>Próximas citas:</strong> <?php echo $estadisticas['proximas_citas']; ?></p>
                        <p><strong>Mensajes:</strong> <?php echo count($mensajes); ?></p>
                    </div>
                </section>
            </main>
        </div>
    </div>

    <script src="medico.js"></script>
</body>
</html>