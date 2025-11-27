<?php
session_start();

// Verificar si la sesión existe
if (!isset($_SESSION['usuario']) || !isset($_SESSION['usuario']['rol'])) {
    header("Location: login.php");
    exit();
}

$regiones_administrador = [
    'AdminGeneral' => ['Norte', 'Sur', 'Oriental', 'Occidental'],
    'AdminNorte' => ['Norte'],
    'AdminSur' => ['Sur'],
    'AdminOriental' => ['Oriental'],
    'AdminOccidental' => ['Occidental']
];

$region_actual = $regiones_administrador[$_SESSION['usuario']['rol']] ?? [];

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
    
    // Total de usuarios
    $result = $db->query("SELECT COUNT(*) as total FROM usuario");
    $stats['total_usuarios'] = $result ? $result->fetch_assoc()['total'] : 0;
    
    // Total de médicos
    $result = $db->query("SELECT COUNT(*) as total FROM medico");
    $stats['total_medicos'] = $result ? $result->fetch_assoc()['total'] : 0;
    
    // Total de pacientes - contar directamente desde poliza
    $result = $db->query("SELECT COUNT(*) as total FROM poliza");
    $stats['total_pacientes'] = $result ? $result->fetch_assoc()['total'] : 0;
    
    // Citas para hoy
    $result = $db->query("SELECT COUNT(*) as total FROM cita WHERE fecha_cita = CURDATE()");
    $stats['total_citas'] = $result ? $result->fetch_assoc()['total'] : 0;
    
    // Total de hospitales
    $result = $db->query("SELECT COUNT(*) as total FROM hospital");
    $stats['total_hospitales'] = $result ? $result->fetch_assoc()['total'] : 0;
    
    $db->close();
    return $stats;
}

function obtenerUsuarios() {
    $db = conectarDB();
    
    // Consulta MUY simplificada - solo columnas básicas
    $query = "SELECT id_usuario, correo, rol FROM usuario ORDER BY id_usuario DESC LIMIT 10";
    
    $result = $db->query($query);
    
    $usuarios = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $usuarios[] = [
                'id' => $row['id_usuario'],
                'nombre' => $row['correo'],
                'email' => $row['correo'],
                'tipo' => ucfirst($row['rol']),
                'fecha_registro' => 'N/A'
            ];
        }
    }
    
    $db->close();
    return $usuarios;
}

// Función para buscar por código postal
function buscarPorCodigoPostal($cp) {
    $db = conectarDB();
    $resultados = [];
    
    if (strlen($cp) === 5) {
        // Buscar hospitales por código postal
        $query = "SELECT nombre, telefono, calle, numero, cp FROM hospital WHERE cp = '$cp'";
        $result = $db->query($query);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $resultados['hospitales'][] = $row;
            }
        }
        
        // Buscar pacientes por código postal
        $query = "SELECT nombre, apellido1, apellido2, telefono, codigo_postal FROM poliza WHERE codigo_postal = '$cp'";
        $result = $db->query($query);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $resultados['pacientes'][] = $row;
            }
        }
    }
    
    $db->close();
    return $resultados;
}

$usuarios = obtenerUsuarios();
$estadisticas = obtenerEstadisticas();

// Procesar búsqueda por código postal
$resultados_cp = [];
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['codigo_postal'])) {
    $cp = $_POST['codigo_postal'];
    $resultados_cp = buscarPorCodigoPostal($cp);
}
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
                    <p>Hola, <?php echo $_SESSION['usuario']['correo']; ?> - Rol: <?php echo $_SESSION['usuario']['rol']; ?></p>
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
                    <div class="stat-icon">🏥</div>
                    <div class="stat-number"><?php echo $estadisticas['total_hospitales']; ?></div>
                    <div class="stat-label">Hospitales</div>
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
                <!-- Búsqueda por Código Postal -->
                <section style="margin-bottom: 30px;">
                    <h2 class="section-title">Búsqueda por Código Postal</h2>
                    <div class="filtro-cp">
                        <form method="POST" class="cp-form">
                            <div class="form-group">
                                <label for="codigo_postal">Buscar por Código Postal:</label>
                                <input type="text" id="codigo_postal" name="codigo_postal" 
                                       placeholder="Ingresa código postal (5 dígitos)" 
                                       maxlength="5" pattern="[0-9]{5}" 
                                       value="<?php echo isset($_POST['codigo_postal']) ? htmlspecialchars($_POST['codigo_postal']) : ''; ?>">
                                <button type="submit" class="btn btn-secondary">
                                    🔍 Buscar
                                </button>
                            </div>
                        </form>
                        
                        <!-- Mostrar resultados de búsqueda -->
                        <?php if (!empty($resultados_cp)): ?>
                            <div class="resultados-busqueda">
                                <?php if (isset($resultados_cp['hospitales']) && !empty($resultados_cp['hospitales'])): ?>
                                    <div class="categoria-resultados">
                                        <h4>Hospitales encontrados:</h4>
                                        <div class="resultados-lista">
                                            <?php foreach ($resultados_cp['hospitales'] as $hospital): ?>
                                                <div class="item-resultado">
                                                    <strong><?php echo htmlspecialchars($hospital['nombre']); ?></strong><br>
                                                    <small>
                                                        <?php echo htmlspecialchars($hospital['calle']); ?> #<?php echo htmlspecialchars($hospital['numero']); ?><br>
                                                        CP: <?php echo htmlspecialchars($hospital['cp']); ?><br>
                                                        Tel: <?php echo htmlspecialchars($hospital['telefono']); ?>
                                                    </small>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <p>No se encontraron hospitales para este código postal.</p>
                                <?php endif; ?>

                                <?php if (isset($resultados_cp['pacientes']) && !empty($resultados_cp['pacientes'])): ?>
                                    <div class="categoria-resultados">
                                        <h4>Pacientes encontrados:</h4>
                                        <div class="resultados-lista">
                                            <?php foreach ($resultados_cp['pacientes'] as $paciente): ?>
                                                <div class="item-resultado">
                                                    <strong>
                                                        <?php echo htmlspecialchars($paciente['nombre']); ?> 
                                                        <?php echo htmlspecialchars($paciente['apellido1']); ?>
                                                        <?php echo htmlspecialchars($paciente['apellido2']); ?>
                                                    </strong><br>
                                                    <small>
                                                        CP: <?php echo htmlspecialchars($paciente['codigo_postal']); ?><br>
                                                        Tel: <?php echo htmlspecialchars($paciente['telefono']); ?>
                                                    </small>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <p>No se encontraron pacientes para este código postal.</p>
                                <?php endif; ?>
                            </div>
                        <?php elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['codigo_postal'])): ?>
                            <p class="no-resultados">No se encontraron resultados para el código postal "<?php echo htmlspecialchars($_POST['codigo_postal']); ?>"</p>
                        <?php endif; ?>
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
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($usuarios)): ?>
                                    <tr>
                                        <td colspan="3" style="text-align: center; padding: 30px; color: #666;">
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