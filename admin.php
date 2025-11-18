<?php
session_start();

// Verificar si está logueado
if (!isset($_SESSION['correo'])) {
    header("Location: inicio_s.php");
    exit();
}

// Conexión simple
function conectarDB() {
    $conexion = new mysqli('localhost', 'root', '', 'healthnet');
    if ($conexion->connect_error) {
        die("Error de conexión: " . $conexion->connect_error);
    }
    return $conexion;
}

// Obtener estadísticas básicas
function obtenerEstadisticas() {
    $db = conectarDB();
    $stats = [];
    
    // Total usuarios
    $result = $db->query("SELECT COUNT(*) as total FROM usuario");
    $stats['total_usuarios'] = $result->fetch_assoc()['total'];
    
    // Verificar si existe columna 'rol'
    $result = $db->query("SHOW COLUMNS FROM usuario LIKE 'rol'");
    if ($result->num_rows > 0) {
        $result = $db->query("SELECT COUNT(*) as total FROM usuario WHERE rol = 'medico'");
        $stats['total_medicos'] = $result->fetch_assoc()['total'];
        
        $result = $db->query("SELECT COUNT(*) as total FROM usuario WHERE rol = 'paciente'");
        $stats['total_pacientes'] = $result->fetch_assoc()['total'];
    } else {
        $stats['total_medicos'] = 0;
        $stats['total_pacientes'] = $stats['total_usuarios'];
    }
    
    // Verificar si existe tabla 'cita'
    $result = $db->query("SHOW TABLES LIKE 'cita'");
    if ($result->num_rows > 0) {
        $result = $db->query("SELECT COUNT(*) as total FROM cita WHERE fecha_cita >= CURDATE()");
        $stats['total_citas'] = $result->fetch_assoc()['total'];
    } else {
        $stats['total_citas'] = 0;
    }
    
    $db->close();
    return $stats;
}

// Obtener usuarios simples
function obtenerUsuarios() {
    $db = conectarDB();
    
    // Primero verificar qué columnas existen
    $result = $db->query("SHOW COLUMNS FROM usuario");
    $columnas = [];
    while ($row = $result->fetch_assoc()) {
        $columnas[] = $row['Field'];
    }
    
    // Construir consulta según las columnas disponibles
    $campos = [];
    if (in_array('id_usuario', $columnas)) {
        $campos[] = 'id_usuario as id';
    } elseif (in_array('usuario_pk', $columnas)) {
        $campos[] = 'usuario_pk as id';
    } else {
        $campos[] = 'id';
    }
    
    $campos[] = 'correo';
    
    if (in_array('rol', $columnas)) {
        $campos[] = 'rol';
    } else {
        $campos[] = "'usuario' as rol";
    }
    
    if (in_array('fecha_creacion', $columnas)) {
        $campos[] = 'fecha_creacion';
    } elseif (in_array('fecha_registro', $columnas)) {
        $campos[] = 'fecha_registro as fecha_creacion';
    } else {
        $campos[] = 'NOW() as fecha_creacion';
    }
    
    $query = "SELECT " . implode(', ', $campos) . " FROM usuario ORDER BY fecha_creacion DESC LIMIT 10";
    $result = $db->query($query);
    
    $usuarios = [];
    while ($row = $result->fetch_assoc()) {
        $usuarios[] = [
            'id' => $row['id'],
            'nombre' => $row['correo'],
            'email' => $row['correo'],
            'tipo' => ucfirst($row['rol']),
            'fecha_registro' => $row['fecha_creacion']
        ];
    }
    
    $db->close();
    return $usuarios;
}

$usuarios = obtenerUsuarios();
$estadisticas = obtenerEstadisticas();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Principal - HealthNet</title>
    <link rel="stylesheet" href="css/estilo_A.css">
</head>
<body>
    <div class="admin-container">
        <header class="admin-header">
            <div class="header-top">
                <div class="admin-title">
                    <h1>Bienvenido a HealthNet</h1>
                    <p>Hola, <?php echo $_SESSION['correo']; ?> - Rol: <?php echo $_SESSION['rol']; ?></p>
                </div>
                <div class="header-actions">
                    <a href="inicio_s.php" class="btn btn-primary">Cerrar Sesión</a>
                </div>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">👥</div>
                    <div class="stat-number"><?php echo $estadisticas['total_usuarios']; ?></div>
                    <div class="stat-label">Usuarios Totales</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">👨‍⚕️</div>
                    <div class="stat-number"><?php echo $estadisticas['total_medicos']; ?></div>
                    <div class="stat-label">Médicos</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">👤</div>
                    <div class="stat-number"><?php echo $estadisticas['total_pacientes']; ?></div>
                    <div class="stat-label">Pacientes</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">📅</div>
                    <div class="stat-number"><?php echo $estadisticas['total_citas']; ?></div>
                    <div class="stat-label">Citas Hoy</div>
                </div>
            </div>
        </header>

        <div class="admin-content">
            <main class="content-main">
                <section style="margin-bottom: 40px;">
                    <h2 class="section-title">Usuarios Recientes</h2>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Email</th>
                                    <th>Tipo</th>
                                    <th>Fecha Registro</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($usuarios)): ?>
                                    <tr>
                                        <td colspan="4" style="text-align: center; padding: 30px; color: #666;">
                                            No hay usuarios registrados
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($usuarios as $usuario): ?>
                                    <tr>
                                        <td><?php echo $usuario['id']; ?></td>
                                        <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                                        <td>
                                            <span class="badge <?php echo $usuario['tipo'] === 'Medico' ? 'badge-info' : ($usuario['tipo'] === 'Admin' ? 'badge-warning' : 'badge-success'); ?>">
                                                <?php echo $usuario['tipo']; ?>
                                            </span>
                                        </td>
                                        <td><?php echo $usuario['fecha_registro']; ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </main>

            <aside class="content-sidebar">
                <section style="margin-bottom: 30px;">
                    <h2 class="section-title">Acciones Rápidas</h2>
                    <div class="quick-actions">
                        <button class="action-btn" onclick="agregarUsuario()">
                            ➕ Agregar Usuario
                        </button>
                        <button class="action-btn" onclick="generarReporte()">
                            📊 Generar Reporte
                        </button>
                        <button class="action-btn" onclick="gestionarCitas()">
                            📅 Gestionar Citas
                        </button>
                        <button class="action-btn" onclick="configuracionSistema()">
                            ⚙️ Configuración
                        </button>
                    </div>
                </section>

                <section>
                    <h2 class="section-title">Información del Sistema</h2>
                    <div class="activity-list">
                        <div class="activity-item">
                            <div class="activity-icon">✅</div>
                            <div class="activity-content">
                                <p>Sistema funcionando correctamente</p>
                                <div class="activity-time">Estado: Óptimo</div>
                            </div>
                        </div>
                        <div class="activity-item">
                            <div class="activity-icon">📊</div>
                            <div class="activity-content">
                                <p><?php echo $estadisticas['total_usuarios']; ?> usuarios en total</p>
                                <div class="activity-time">Base de datos</div>
                            </div>
                        </div>
                    </div>
                </section>
            </aside>
        </div>
    </div>

    <div id="modalUsuario" class="modal">
        <div class="modal-content">
            <h3>Agregar Nuevo Usuario</h3>
            <p>Esta funcionalidad estará disponible próximamente.</p>
            <div class="modal-buttons">
                <button class="close-btn" onclick="cerrarModal('modalUsuario')">Cerrar</button>
            </div>
        </div>
    </div>

    <script src="admin.js"></script>
</body>
</html>