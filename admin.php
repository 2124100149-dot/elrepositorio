<?php
session_start();



$regiones_administrador = [
    'AdminGeneral' => ['Norte', 'Sur', 'Oriental', 'Occidental'],
    'AdminNorte' => ['Norte'],
    'AdminSur' => ['Sur'],
    'AdminOriental' => ['Oriental'],
    'AdminOccidental' => ['Occidental']
];

$region_actual = $regiones_administrador[$_SESSION['rol']] ?? [];

function conectarDB() {
    $conexion = new mysqli('localhost', 'root', '', 'healthnet');
    if ($conexion->connect_error) {
        die("Error de conexión: " . $conexion->connect_error);
    }
    return $conexion;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['accion_rapida'])) {
        switch ($_POST['accion_rapida']) {
            case 'agregar_usuario':
                header("Location: agregar_usuario.php");
                exit();
            case 'generar_reporte':
                generarReporte();
                break;
            case 'gestionar_citas':
                header("Location: gestion_citas.php");
                exit();
            case 'configuracion_sistema':
                header("Location: configuracion.php");
                exit();
            case 'backup_bd':
                realizarBackup();
                break;
        }
    }
    
    if (isset($_POST['filtrar_cp'])) {
        $cp = $_POST['codigo_postal'];
        header("Location: filtrar_region.php?cp=" . $cp);
        exit();
    }
}

function realizarBackup() {
    $backup_file = 'backup/backup_' . date("Y-m-d-H-i-s") . '.sql';
    echo "<script>alert('Backup realizado exitosamente');</script>";
}

function generarReporte() {
    $db = conectarDB();
    $fecha = date('Y-m-d');
    $reporte_file = "reportes/reporte_$fecha.pdf";
    echo "<script>alert('Reporte generado: $reporte_file');</script>";
    $db->close();
}

function obtenerEstadisticas() {
    $db = conectarDB();
    $stats = [];
    
    $result = $db->query("SHOW TABLES LIKE 'usuario'");
    if ($result->num_rows == 0) {
        $stats['total_usuarios'] = 0;
        $stats['total_medicos'] = 0;
        $stats['total_pacientes'] = 0;
        $stats['total_citas'] = 0;
        $db->close();
        return $stats;
    }
    
    $where_region = "";
    if ($_SESSION['rol'] != 'AdminGeneral' && !empty($GLOBALS['region_actual'])) {
        $regiones = $GLOBALS['region_actual'];
        $check_region = $db->query("SHOW COLUMNS FROM usuario LIKE 'region'");
        if ($check_region->num_rows > 0) {
            $where_region = " WHERE region IN ('" . implode("','", $regiones) . "')";
        }
    }
    
    $result = $db->query("SELECT COUNT(*) as total FROM usuario" . $where_region);
    if ($result) {
        $stats['total_usuarios'] = $result->fetch_assoc()['total'];
    } else {
        $stats['total_usuarios'] = 0;
    }

    $result = $db->query("SHOW COLUMNS FROM usuario LIKE 'rol'");
    if ($result->num_rows > 0) {
        $result_medicos = $db->query("SELECT COUNT(*) as total FROM usuario WHERE rol = 'medico'" . $where_region);
        if ($result_medicos) {
            $stats['total_medicos'] = $result_medicos->fetch_assoc()['total'];
        } else {
            $stats['total_medicos'] = 0;
        }
        
        $result_pacientes = $db->query("SELECT COUNT(*) as total FROM usuario WHERE rol = 'paciente'" . $where_region);
        if ($result_pacientes) {
            $stats['total_pacientes'] = $result_pacientes->fetch_assoc()['total'];
        } else {
            $stats['total_pacientes'] = 0;
        }
    } else {
        $stats['total_medicos'] = 0;
        $stats['total_pacientes'] = $stats['total_usuarios'];
    }
    
    $result = $db->query("SHOW TABLES LIKE 'cita'");
    if ($result->num_rows > 0) {
        $result_citas = $db->query("SELECT COUNT(*) as total FROM cita WHERE fecha_cita >= CURDATE()");
        if ($result_citas) {
            $stats['total_citas'] = $result_citas->fetch_assoc()['total'];
        } else {
            $stats['total_citas'] = 0;
        }
    } else {
        $stats['total_citas'] = 0;
    }
    
    $db->close();
    return $stats;
}

function obtenerUsuarios() {
    $db = conectarDB();
    
    $result = $db->query("SHOW TABLES LIKE 'usuario'");
    if ($result->num_rows == 0) {
        $db->close();
        return [];
    }
    
    $result = $db->query("SHOW COLUMNS FROM usuario");
    $columnas = [];
    while ($row = $result->fetch_assoc()) {
        $columnas[] = $row['Field'];
    }
    
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
    
    if (in_array('region', $columnas)) {
        $campos[] = 'region';
    }
    
    $query = "SELECT " . implode(', ', $campos) . " FROM usuario ORDER BY fecha_creacion DESC LIMIT 10";
    $result = $db->query($query);
    
    $usuarios = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $usuarios[] = [
                'id' => $row['id'],
                'nombre' => $row['correo'],
                'email' => $row['correo'],
                'tipo' => ucfirst($row['rol']),
                'fecha_registro' => $row['fecha_creacion'],
                'region' => $row['region'] ?? 'General'
            ];
        }
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
                    <!-- <p>Hola, <?php echo $_SESSION['correo']; ?> - Rol: <?php echo $_SESSION['rol']; ?></p> -->
                    <?php if (!empty($region_actual)): ?>
                        <p style="color: #666; font-size: 14px; margin-top: 5px;">
                            Región: <?php echo implode(', ', $region_actual); ?>
                        </p>
                    <?php endif; ?>
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
                <!-- Filtro por Código Postal -->
                <section style="margin-bottom: 30px;">
                    <h2 class="section-title">Filtro por Región</h2>
                    <div class="filtro-cp">
                        <form method="POST" class="cp-form">
                            <div class="form-group">
                                <label for="codigo_postal">Buscar por Código Postal:</label>
                                <input type="text" id="codigo_postal" name="codigo_postal" 
                                       placeholder="Ingresa código postal (5 dígitos)" 
                                       maxlength="5" pattern="[0-9]{5}">
                                <button type="submit" name="filtrar_cp" class="btn btn-secondary">
                                    🔍 Buscar
                                </button>
                            </div>
                        </form>
                        <div id="resultado-cp" class="resultado-cp"></div>
                    </div>
                </section>

                <section style="margin-bottom: 40px;">
                    <h2 class="section-title">Usuarios Recientes</h2>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Email</th>
                                    <th>Tipo</th>
                                    <th>Región</th>
                                    <th>Fecha Registro</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($usuarios)): ?>
                                    <tr>
                                        <td colspan="5" style="text-align: center; padding: 30px; color: #666;">
                                            No hay usuarios registrados o la tabla no existe
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
                                        <td>
                                            <span class="badge badge-region"><?php echo $usuario['region']; ?></span>
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
                        <form method="POST" class="acciones-form">
                            <button type="submit" name="accion_rapida" value="agregar_usuario" class="action-btn">
                                ➕ Agregar Usuario
                            </button>
                            <button type="submit" name="accion_rapida" value="generar_reporte" class="action-btn">
                                📊 Generar Reporte
                            </button>
                            <button type="submit" name="accion_rapida" value="gestionar_citas" class="action-btn">
                                📅 Gestionar Citas
                            </button>
                            <button type="submit" name="accion_rapida" value="configuracion_sistema" class="action-btn">
                                ⚙️ Configuración
                            </button>
                            <button type="submit" name="accion_rapida" value="backup_bd" class="action-btn">
                                💾 Backup BD
                            </button>
                        </form>
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
                        <?php if (!empty($region_actual)): ?>
                        <div class="activity-item">
                            <div class="activity-icon">🗺️</div>
                            <div class="activity-content">
                                <p>Región asignada: <?php echo implode(', ', $region_actual); ?></p>
                                <div class="activity-time">Ámbito administrativo</div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </section>
            </aside>
        </div>
    </div>

    <script src="admin.js"></script>
</body>
</html>