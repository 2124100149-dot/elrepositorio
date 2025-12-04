<?php
session_start();

if (!isset($_SESSION['usuario']) || !isset($_SESSION['usuario']['rol'])) {
    header("Location: inicio_s.php");
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
require_once __DIR__ . "/conexion.php";

// Obtener municipios para el modal
$db_temp = conectarDB();
$query_municipios = $db_temp->query("SELECT municipio_pk, nombre_municipio, entidad_fk FROM municipio ORDER BY nombre_municipio");
$municipios_list = [];
if ($query_municipios) {
    while ($row = $query_municipios->fetch_assoc()) {
        $municipios_list[] = $row;
    }
}
$db_temp->close();

// Procesar acciones de edición y eliminación
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Acciones rápidas
    if (isset($_POST['accion_rapida'])) {
        switch ($_POST['accion_rapida']) {
            case 'agregar_usuario':
                header("Location: agregar_usuario.php");
                exit();
            case 'agregar_hospital':
                header("Location: agregar_hospital.php");
                exit();
        }
    }
    
    // Editar usuario básico (solo correo y rol)
    if (isset($_POST['editar_usuario'])) {
        $id_usuario = $_POST['id_usuario'];
        $correo = $_POST['correo'];
        $rol = $_POST['rol'];
        
        $db = conectarDB();
        $stmt = $db->prepare("UPDATE usuario SET correo = ?, rol = ? WHERE id_usuario = ?");
        $stmt->bind_param("ssi", $correo, $rol, $id_usuario);
        
        if ($stmt->execute()) {
            $_SESSION['mensaje'] = "Usuario actualizado correctamente";
            $_SESSION['tipo_mensaje'] = "success";
        } else {
            $_SESSION['mensaje'] = "Error al actualizar usuario: " . $db->error;
            $_SESSION['tipo_mensaje'] = "error";
        }
        
        $stmt->close();
        $db->close();
        
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
    
    // Eliminar usuario
    if (isset($_POST['eliminar_usuario'])) {
        $id_usuario = $_POST['id_usuario'];
        
        $db = conectarDB();
        $stmt = $db->prepare("DELETE FROM usuario WHERE id_usuario = ?");
        $stmt->bind_param("i", $id_usuario);
        
        if ($stmt->execute()) {
            $_SESSION['mensaje'] = "Usuario eliminado correctamente";
            $_SESSION['tipo_mensaje'] = "success";
        } else {
            $_SESSION['mensaje'] = "Error al eliminar usuario: " . $db->error;
            $_SESSION['tipo_mensaje'] = "error";
        }
        
        $stmt->close();
        $db->close();
        
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
    
    // Edición completa de usuario (paciente)
    if (isset($_POST['editar_usuario_completo'])) {
        $id_usuario = $_POST['id_usuario'];
        $correo = $_POST['correo'] ?? '';
        $rol = $_POST['rol'] ?? '';
        
        $db = conectarDB();
        
        // Actualizar datos básicos del usuario
        $stmt = $db->prepare("UPDATE usuario SET correo = ?, rol = ? WHERE id_usuario = ?");
        $stmt->bind_param("ssi", $correo, $rol, $id_usuario);
        $stmt->execute();
        
        // Si es un paciente (Usuario), actualizar también los datos de la póliza
        if ($rol === 'Usuario') {
            $poliza_id = $_POST['poliza_id'] ?? '';
            $tipo_poliza = $_POST['tipo_poliza'] ?? '';
            $fecha_inicio = $_POST['fecha_inicio'] ?? '';
            $fecha_fin = $_POST['fecha_fin'] ?? '';
            $fecha_efectiva = $_POST['fecha_efectiva'] ?? '';
            $nombre = $_POST['nombre'] ?? '';
            $apellido1 = $_POST['apellido1'] ?? '';
            $apellido2 = $_POST['apellido2'] ?? '';
            $telefono = $_POST['telefono'] ?? '';
            $fecha_nacimiento = $_POST['fecha_nacimiento'] ?? '';
            $sexo = $_POST['sexo'] ?? '';
            $estatus = $_POST['estatus'] ?? '';
            $edad = $_POST['edad'] ?? 0;
            $calle = $_POST['calle'] ?? '';
            $num_exterior = $_POST['num_exterior'] ?? '';
            $num_interior = $_POST['num_interior'] ?? '';
            $colonia = $_POST['colonia'] ?? '';
            $codigo_postal = $_POST['codigo_postal'] ?? '';
            $municipio_fk = $_POST['municipio_fk'] ?? '';
            $entidad_fk = $_POST['entidad_fk'] ?? '';

            // Validar que entidad_fk no esté vacío
            if (empty($entidad_fk)) {
                $_SESSION['mensaje'] = "Error: No se pudo determinar la entidad para el municipio seleccionado";
                $_SESSION['tipo_mensaje'] = "error";
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            }
            
            // Calcular edad si hay fecha de nacimiento
            if (!empty($fecha_nacimiento)) {
                $fechaNacimiento = new DateTime($fecha_nacimiento);
                $hoy = new DateTime();
                $edad = $hoy->diff($fechaNacimiento)->y;
            }
            
            // Verificar si ya existe una póliza para este usuario
            $check_poliza = $db->prepare("SELECT poliza_pk FROM poliza WHERE usuario_fk = ?");
            $check_poliza->bind_param("i", $id_usuario);
            $check_poliza->execute();
            $check_poliza->store_result();
            
            if ($check_poliza->num_rows > 0) {
                // Actualizar póliza existente
                $stmt_poliza = $db->prepare("
                    UPDATE poliza SET
                        poliza_id = ?, tipo_poliza = ?, fecha_inicio = ?, fecha_fin = ?, fecha_efectiva = ?,
                        nombre = ?, apellido1 = ?, apellido2 = ?, telefono = ?, fecha_nacimiento = ?,
                        sexo = ?, estatus = ?, edad = ?, calle = ?, num_exterior = ?, num_interior = ?,
                        colonia = ?, codigo_postal = ?, municipio_fk = ?, entidad_fk = ?
                    WHERE usuario_fk = ?
                ");
                $stmt_poliza->bind_param(
                    "sssssssssssssissssii",
                    $poliza_id, $tipo_poliza, $fecha_inicio, $fecha_fin, $fecha_efectiva,
                    $nombre, $apellido1, $apellido2, $telefono, $fecha_nacimiento,
                    $sexo, $estatus, $edad, $calle, $num_exterior, $num_interior,
                    $colonia, $codigo_postal, $municipio_fk, $entidad_fk, $id_usuario
                );
            } else {
                // Insertar nueva póliza
                $stmt_poliza = $db->prepare("
                    INSERT INTO poliza (
                        usuario_fk, poliza_id, tipo_poliza, fecha_inicio, fecha_fin, fecha_efectiva,
                        nombre, apellido1, apellido2, telefono, fecha_nacimiento, sexo, estatus, edad,
                        calle, num_exterior, num_interior, colonia, codigo_postal, municipio_fk, entidad_fk
                    ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
                ");
                $stmt_poliza->bind_param(
                    "issssssssssssissssii",
                    $id_usuario, $poliza_id, $tipo_poliza, $fecha_inicio, $fecha_fin, $fecha_efectiva,
                    $nombre, $apellido1, $apellido2, $telefono, $fecha_nacimiento,
                    $sexo, $estatus, $edad, $calle, $num_exterior, $num_interior,
                    $colonia, $codigo_postal, $municipio_fk, $entidad_fk
                );
            }
            
            if ($stmt_poliza->execute()) {
                $_SESSION['mensaje'] = "Usuario actualizado correctamente";
                $_SESSION['tipo_mensaje'] = "success";
            } else {
                $_SESSION['mensaje'] = "Error al actualizar datos del paciente: " . $stmt_poliza->error;
                $_SESSION['tipo_mensaje'] = "error";
            }
            
            $stmt_poliza->close();
            $check_poliza->close();
        }
        
        $stmt->close();
        $db->close();
        
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

function obtenerEstadisticas() {
    $db = conectarDB();
    $stats = [];
    
    $result = $db->query("SELECT COUNT(*) as total FROM usuario");
    $stats['total_usuarios'] = $result ? $result->fetch_assoc()['total'] : 0;
    
    $result = $db->query("SELECT COUNT(*) as total FROM medico");
    $stats['total_medicos'] = $result ? $result->fetch_assoc()['total'] : 0;
    
    $result = $db->query("SELECT COUNT(*) as total FROM poliza");
    $stats['total_pacientes'] = $result ? $result->fetch_assoc()['total'] : 0;
    
    $result = $db->query("SELECT COUNT(*) as total FROM cita WHERE fecha_cita = CURDATE()");
    $stats['total_citas'] = $result ? $result->fetch_assoc()['total'] : 0;
    
    $result = $db->query("SELECT COUNT(*) as total FROM hospital");
    $stats['total_hospitales'] = $result ? $result->fetch_assoc()['total'] : 0;
    
    $db->close();
    return $stats;
}

function obtenerUsuarios($limit = 10) {
    $db = conectarDB();
    
    $query = "SELECT id_usuario, correo, rol FROM usuario ORDER BY id_usuario DESC LIMIT $limit";
    $result = $db->query($query);
    
    $usuarios = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $usuarios[] = [
                'id' => $row['id_usuario'],
                'correo' => $row['correo'],
                'rol' => $row['rol'],
                'tipo' => ucfirst($row['rol'])
            ];
        }
        $result->free();
    }
    
    $db->close();
    return $usuarios;
}

function buscarUsuarios($termino, $tipo = 'todos') {
    $db = conectarDB();
    $usuarios = [];
    
    $termino = "%$termino%";
    
    if ($tipo === 'todos' || $tipo === 'usuario') {
        $query = "SELECT id_usuario, correo, rol FROM usuario 
                 WHERE correo LIKE ? OR id_usuario LIKE ? OR rol LIKE ? 
                 ORDER BY id_usuario DESC LIMIT 20";
        $stmt = $db->prepare($query);
        $stmt->bind_param("sss", $termino, $termino, $termino);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $usuarios[] = [
                'tipo' => 'usuario',
                'id' => $row['id_usuario'],
                'correo' => $row['correo'],
                'rol' => $row['rol']
            ];
        }
        $stmt->close();
    }
    
    if ($tipo === 'todos' || $tipo === 'paciente') {
        $query = "SELECT poliza_pk, nombre, apellidos, telefono, codigo_postal FROM poliza 
                 WHERE nombre LIKE ? OR apellidos LIKE ? OR telefono LIKE ? OR codigo_postal LIKE ?
                 ORDER BY poliza_pk DESC LIMIT 20";
        $stmt = $db->prepare($query);
        $stmt->bind_param("ssss", $termino, $termino, $termino, $termino);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $usuarios[] = [
                'tipo' => 'paciente',
                'id' => $row['poliza_pk'],
                'nombre' => $row['nombre'],
                'apellidos' => $row['apellidos'],
                'telefono' => $row['telefono'],
                'codigo_postal' => $row['codigo_postal']
            ];
        }
        $stmt->close();
    }
    
    if ($tipo === 'todos' || $tipo === 'medico') {
        $query = "SELECT medico_pk, nombre, apellidos, especialidad, telefono FROM medico 
                 WHERE nombre LIKE ? OR apellidos LIKE ? OR especialidad LIKE ? OR telefono LIKE ?
                 ORDER BY medico_pk DESC LIMIT 20";
        $stmt = $db->prepare($query);
        $stmt->bind_param("ssss", $termino, $termino, $termino, $termino);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $usuarios[] = [
                'tipo' => 'medico',
                'id' => $row['medico_pk'],
                'nombre' => $row['nombre'],
                'apellidos' => $row['apellidos'],
                'especialidad' => $row['especialidad'],
                'telefono' => $row['telefono']
            ];
        }
        $stmt->close();
    }
    
    $db->close();
    return $usuarios;
}

function obtenerUsuarioPorId($id, $tipo) {
    $db = conectarDB();
    $usuario = null;
    
    switch($tipo) {
        case 'usuario':
            $query = "SELECT u.id_usuario, u.correo, u.rol, 
                             p.poliza_pk, p.poliza_id, p.tipo_poliza, p.fecha_inicio, p.fecha_fin, p.fecha_efectiva,
                             p.nombre, p.apellido1, p.apellido2, p.telefono, p.fecha_nacimiento, 
                             p.sexo, p.estatus, p.edad, p.calle, p.num_exterior, p.num_interior, 
                             p.colonia, p.codigo_postal, p.municipio_fk, p.entidad_fk
                      FROM usuario u 
                      LEFT JOIN poliza p ON u.id_usuario = p.usuario_fk 
                      WHERE u.id_usuario = ?";
            $stmt = $db->prepare($query);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $usuario = [
                    'tipo' => 'usuario',
                    'id' => $row['id_usuario'],
                    'correo' => $row['correo'],
                    'rol' => $row['rol'],
                    // Datos de póliza (si existen)
                    'poliza_id' => $row['poliza_id'] ?? '',
                    'tipo_poliza' => $row['tipo_poliza'] ?? '',
                    'fecha_inicio' => $row['fecha_inicio'] ?? '',
                    'fecha_fin' => $row['fecha_fin'] ?? '',
                    'fecha_efectiva' => $row['fecha_efectiva'] ?? '',
                    'nombre' => $row['nombre'] ?? '',
                    'apellido1' => $row['apellido1'] ?? '',
                    'apellido2' => $row['apellido2'] ?? '',
                    'telefono' => $row['telefono'] ?? '',
                    'fecha_nacimiento' => $row['fecha_nacimiento'] ?? '',
                    'sexo' => $row['sexo'] ?? '',
                    'estatus' => $row['estatus'] ?? '',
                    'edad' => $row['edad'] ?? 0,
                    'calle' => $row['calle'] ?? '',
                    'num_exterior' => $row['num_exterior'] ?? '',
                    'num_interior' => $row['num_interior'] ?? '',
                    'colonia' => $row['colonia'] ?? '',
                    'codigo_postal' => $row['codigo_postal'] ?? '',
                    'municipio_fk' => $row['municipio_fk'] ?? '',
                    'entidad_fk' => $row['entidad_fk'] ?? ''
                ];
            }
            $stmt->close();
            break;
            
        case 'paciente':
            $query = "SELECT poliza_pk, nombre, apellidos, telefono, codigo_postal FROM poliza WHERE poliza_pk = ?";
            $stmt = $db->prepare($query);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $usuario = [
                    'tipo' => 'paciente',
                    'id' => $row['poliza_pk'],
                    'nombre' => $row['nombre'],
                    'apellidos' => $row['apellidos'],
                    'telefono' => $row['telefono'],
                    'codigo_postal' => $row['codigo_postal']
                ];
            }
            $stmt->close();
            break;
            
        case 'medico':
            $query = "SELECT medico_pk, nombre, apellidos, especialidad, telefono FROM medico WHERE medico_pk = ?";
            $stmt = $db->prepare($query);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $usuario = [
                    'tipo' => 'medico',
                    'id' => $row['medico_pk'],
                    'nombre' => $row['nombre'],
                    'apellidos' => $row['apellidos'],
                    'especialidad' => $row['especialidad'],
                    'telefono' => $row['telefono']
                ];
            }
            $stmt->close();
            break;
    }
    
    $db->close();
    return $usuario;
}

$usuarios = obtenerUsuarios();
$estadisticas = obtenerEstadisticas();

// Variables para búsqueda
$resultados_busqueda = [];
$termino_busqueda = '';
$tipo_busqueda = 'todos';
$tiempo_busqueda = 0;

// Procesar búsqueda
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['buscar'])) {
    $termino_busqueda = trim($_POST['termino_busqueda']);
    $tipo_busqueda = $_POST['tipo_busqueda'] ?? 'todos';
    
    if (!empty($termino_busqueda)) {
        $inicio = microtime(true);
        $resultados_busqueda = buscarUsuarios($termino_busqueda, $tipo_busqueda);
        $tiempo_busqueda = round((microtime(true) - $inicio) * 1000, 2);
    }
}

// Variables para edición
$usuario_editar = null;
if (isset($_GET['editar'])) {
    $id = $_GET['id'];
    $tipo = $_GET['tipo'];
    $usuario_editar = obtenerUsuarioPorId($id, $tipo);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Principal - HealthNet</title>
    <link rel="stylesheet" href="css/estilo_A.css">
    <link rel="stylesheet" href="css/medico.css">
    <style>
    /* Estilos para el modal */
    .modal {
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .modal-content {
        background-color: #fff;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        position: relative;
    }

    .close {
        position: absolute;
        top: 15px;
        right: 20px;
        font-size: 28px;
        color: #aaa;
        cursor: pointer;
        transition: color 0.3s;
    }

    .close:hover {
        color: #000;
    }

    .form-section {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        border: 1px solid #e9ecef;
    }

    .form-section h4, .form-section h5 {
        margin-top: 0;
        color: #2c3e50;
    }

    .form-row {
        display: flex;
        gap: 15px;
        margin-bottom: 15px;
    }

    .form-row .form-group {
        flex: 1;
    }

    .form-group {
        margin-bottom: 15px;
    }

    .form-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: 600;
        color: #495057;
    }

    .form-group input, .form-group select {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #ced4da;
        border-radius: 4px;
        font-size: 14px;
        transition: border-color 0.3s;
    }

    .form-group input:focus, .form-group select:focus {
        outline: none;
        border-color: #3498db;
        box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
    }

    .btn-group {
        display: flex;
        gap: 10px;
        margin-top: 30px;
    }

    .btn-primary {
        background: #3498db;
        color: white;
        border: none;
        padding: 12px 25px;
        border-radius: 5px;
        cursor: pointer;
        font-size: 16px;
        transition: background 0.3s;
        flex: 1;
    }

    .btn-primary:hover {
        background: #2980b9;
    }

    .btn-secondary {
        background: #95a5a6;
        color: white;
        border: none;
        padding: 12px 25px;
        border-radius: 5px;
        cursor: pointer;
        font-size: 16px;
        transition: background 0.3s;
        flex: 1;
    }

    .btn-secondary:hover {
        background: #7f8c8d;
    }

    /* Para hacer el modal responsive */
    @media (max-width: 850px) {
        .modal-content {
            width: 95%;
            margin: 10px;
            max-height: 90vh;
            overflow-y: auto;
        }
    }
    body.modal-open {
overflow: hidden;
}

.modal-content {
max-height: 90vh;
overflow-y: auto;
}
    </style>
</head>
<body>
    <div class="admin-container">
        <header class="admin-header">
            <div class="header-top">
                <div class="admin-title">
                    <h1>Bienvenido a HealthNet</h1> 
                    <p>Hola, <?php echo htmlspecialchars($_SESSION['usuario']['correo'] ?? ''); ?> - Rol: <?php echo htmlspecialchars($_SESSION['usuario']['rol'] ?? ''); ?></p>
                    <?php if (!empty($region_actual)): ?>
                        <p style="color: #666; font-size: 14px; margin-top: 5px;">
                            Región: <?php echo htmlspecialchars(implode(', ', $region_actual)); ?>
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
                <!-- Mostrar mensajes -->
                <?php if (isset($_SESSION['mensaje'])): ?>
                    <div class="mensaje <?php echo $_SESSION['tipo_mensaje'] ?? 'success'; ?>">
                        <?php 
                        echo htmlspecialchars($_SESSION['mensaje']); 
                        unset($_SESSION['mensaje']);
                        unset($_SESSION['tipo_mensaje']);
                        ?>
                    </div>
                <?php endif; ?>

                <!-- Búsqueda general -->
                <section style="margin-bottom: 30px;">
                    <h2 class="section-title">Búsqueda General</h2>
                    <div class="search-form">
                        <form method="POST">
                            <div class="form-group">
                                <label for="termino_busqueda">Buscar:</label>
                                <input type="text" id="termino_busqueda" name="termino_busqueda" 
                                       placeholder="Buscar por nombre, email, teléfono, etc." 
                                       value="<?php echo htmlspecialchars($termino_busqueda); ?>">
                            </div>
                            
                            <div class="search-options">
                                <label>
                                    <input type="radio" name="tipo_busqueda" value="todos" 
                                           <?php echo ($tipo_busqueda === 'todos') ? 'checked' : ''; ?>> Todos
                                </label>
                                <label>
                                    <input type="radio" name="tipo_busqueda" value="usuario" 
                                           <?php echo ($tipo_busqueda === 'usuario') ? 'checked' : ''; ?>> Usuarios
                                </label>
                                <label>
                                    <input type="radio" name="tipo_busqueda" value="paciente" 
                                           <?php echo ($tipo_busqueda === 'paciente') ? 'checked' : ''; ?>> Pacientes
                                </label>
                                <label>
                                    <input type="radio" name="tipo_busqueda" value="medico" 
                                           <?php echo ($tipo_busqueda === 'medico') ? 'checked' : ''; ?>> Médicos
                                </label>
                            </div>
                            
                            <button type="submit" name="buscar" class="btn btn-primary">
                                🔍 Buscar
                            </button>
                        </form>
                        
                        <?php if ($tiempo_busqueda > 0): ?>
                            <div style="font-size: 12px; color: #666; margin: 10px 0;">
                                ⏱️ Búsqueda completada en <?php echo $tiempo_busqueda; ?> ms
                                (<?php echo count($resultados_busqueda); ?> resultados)
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($resultados_busqueda)): ?>
                            <div class="resultados-busqueda">
                                <h4>Resultados de búsqueda:</h4>
                                <?php foreach ($resultados_busqueda as $resultado): ?>
                                    <div class="item-resultado">
                                        <div class="item-info">
                                            <?php if ($resultado['tipo'] === 'usuario'): ?>
                                                <strong><?php echo htmlspecialchars($resultado['correo']); ?></strong>
                                                <span class="badge badge-warning">Usuario</span>
                                                <br>
                                                <small>Rol: <?php echo htmlspecialchars($resultado['rol']); ?></small>
                                            <?php elseif ($resultado['tipo'] === 'paciente'): ?>
                                                <strong><?php echo htmlspecialchars($resultado['nombre'] . ' ' . $resultado['apellidos']); ?></strong>
                                                <span class="badge badge-success">Paciente</span>
                                                <br>
                                                <small>
                                                    Tel: <?php echo htmlspecialchars($resultado['telefono'] ?? 'N/A'); ?> | 
                                                    CP: <?php echo htmlspecialchars($resultado['codigo_postal'] ?? 'N/A'); ?>
                                                </small>
                                            <?php elseif ($resultado['tipo'] === 'medico'): ?>
                                                <strong><?php echo htmlspecialchars($resultado['nombre'] . ' ' . $resultado['apellidos']); ?></strong>
                                                <span class="badge badge-info">Médico</span>
                                                <br>
                                                <small>
                                                    Especialidad: <?php echo htmlspecialchars($resultado['especialidad'] ?? 'N/A'); ?> | 
                                                    Tel: <?php echo htmlspecialchars($resultado['telefono'] ?? 'N/A'); ?>
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                        <div class="item-acciones">
                                            <a href="?editar=1&id=<?php echo $resultado['id']; ?>&tipo=<?php echo $resultado['tipo']; ?>" 
                                               class="btn btn-warning">Editar</a>
                                            <form method="POST" style="display:inline;" onsubmit="return confirm('¿Está seguro de eliminar este registro?');">
                                                <input type="hidden" name="id_usuario" value="<?php echo $resultado['id']; ?>">
                                                <input type="hidden" name="tipo_usuario" value="<?php echo $resultado['tipo']; ?>">
                                                <button type="submit" name="eliminar_usuario" class="btn btn-danger">Eliminar</button>
                                            </form>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php elseif (!empty($termino_busqueda)): ?>
                            <p class="no-resultados">❌ No se encontraron resultados para "<?php echo htmlspecialchars($termino_busqueda); ?>"</p>
                        <?php endif; ?>
                    </div>
                </section>

                <!-- Usuarios recientes -->
                <section style="margin-bottom: 40px;">
                    <h2 class="section-title">Usuarios Recientes</h2>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Email</th>
                                    <th>Tipo</th>
                                    <th>Acciones</th>
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
                                        <td><?php echo htmlspecialchars($usuario['correo']); ?></td>
                                        <td>
                                            <span class="badge <?php echo $usuario['tipo'] === 'Medico' ? 'badge-info' : ($usuario['tipo'] === 'Admin' ? 'badge-warning' : 'badge-success'); ?>">
                                                <?php echo htmlspecialchars($usuario['tipo']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="?editar=1&id=<?php echo $usuario['id']; ?>&tipo=usuario" 
                                               class="btn btn-warning">Editar</a>
                                            <form method="POST" style="display:inline;" onsubmit="return confirm('¿Está seguro de eliminar este usuario?');">
                                                <input type="hidden" name="id_usuario" value="<?php echo $usuario['id']; ?>">
                                                <button type="submit" name="eliminar_usuario" class="btn btn-danger">Eliminar</button>
                                            </form>
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
                            <button type="submit" name="accion_rapida" value="agregar_hospital" class="action-btn">
                                ➕ Agregar Hospitales
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
                                <p>Región asignada: <?php echo htmlspecialchars(implode(', ', $region_actual)); ?></p>
                                <div class="activity-time">Ámbito administrativo</div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </section>
            </aside>
        </div>
    </div>

    <!-- Modal para editar usuario -->
    <?php if ($usuario_editar): ?>
    <div id="modalEditar" class="modal" style="display: block;">
        <div class="modal-content" style="max-width: 800px;">
            <span class="close" onclick="cerrarModal()">&times;</span>
            <h3>Editar <?php echo ucfirst($usuario_editar['tipo']); ?></h3>
            
            <form method="POST" id="formEditarUsuario">
                <input type="hidden" name="id_usuario" value="<?php echo $usuario_editar['id']; ?>">
                <input type="hidden" name="tipo_usuario" value="<?php echo $usuario_editar['tipo']; ?>">
                
                <!-- Datos básicos del usuario -->
                <div class="form-section">
                    <h4>Datos de Acceso</h4>
                    <div class="form-group">
                        <label for="correo">Correo:</label>
                        <input type="email" id="correo" name="correo" value="<?php echo htmlspecialchars($usuario_editar['correo']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="rol">Rol:</label>
                        <select id="rol" name="rol" required onchange="toggleCamposEdicion()">
                            <option value="AdminGeneral" <?php echo ($usuario_editar['rol'] === 'AdminGeneral') ? 'selected' : ''; ?>>Admin General</option>
                            <option value="AdminNorte" <?php echo ($usuario_editar['rol'] === 'AdminNorte') ? 'selected' : ''; ?>>Admin Norte</option>
                            <option value="AdminSur" <?php echo ($usuario_editar['rol'] === 'AdminSur') ? 'selected' : ''; ?>>Admin Sur</option>
                            <option value="AdminOriental" <?php echo ($usuario_editar['rol'] === 'AdminOriental') ? 'selected' : ''; ?>>Admin Oriental</option>
                            <option value="AdminOccidental" <?php echo ($usuario_editar['rol'] === 'AdminOccidental') ? 'selected' : ''; ?>>Admin Occidental</option>
                            <option value="Medico" <?php echo ($usuario_editar['rol'] === 'Medico') ? 'selected' : ''; ?>>Médico</option>
                            <option value="Usuario" <?php echo ($usuario_editar['rol'] === 'Usuario') ? 'selected' : ''; ?>>Usuario (Paciente)</option>
                        </select>
                    </div>
                </div>
                
                <!-- Sección de datos del paciente (solo para pacientes) -->
                <div id="camposPaciente" style="<?php echo ($usuario_editar['rol'] === 'Usuario') ? '' : 'display: none;'; ?>">
                    <h4>Datos del Paciente</h4>
                    
                    <div class="form-group">
                        <label for="poliza_id">ID de Póliza:</label>
                        <input type="text" id="poliza_id" name="poliza_id" value="<?php echo htmlspecialchars($usuario_editar['poliza_id'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="tipo_poliza">Tipo de Póliza:</label>
                        <select id="tipo_poliza" name="tipo_poliza">
                            <option value="">Seleccione</option>
                            <option value="Normal" <?php echo (($usuario_editar['tipo_poliza'] ?? '') == 'Normal') ? 'selected' : ''; ?>>Normal</option>
                            <option value="Premium" <?php echo (($usuario_editar['tipo_poliza'] ?? '') == 'Premium') ? 'selected' : ''; ?>>Premium</option>
                        </select>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="fecha_inicio">Fecha Inicio:</label>
                            <input type="date" id="fecha_inicio" name="fecha_inicio" value="<?php echo htmlspecialchars($usuario_editar['fecha_inicio'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="fecha_fin">Fecha Fin:</label>
                            <input type="date" id="fecha_fin" name="fecha_fin" value="<?php echo htmlspecialchars($usuario_editar['fecha_fin'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="fecha_efectiva">Fecha Efectiva:</label>
                        <input type="date" id="fecha_efectiva" name="fecha_efectiva" value="<?php echo htmlspecialchars($usuario_editar['fecha_efectiva'] ?? ''); ?>">
                    </div>
                    
                    <!-- Datos personales -->
                    <h5>Datos Personales</h5>
                    <div class="form-group">
                        <label for="nombre">Nombre:</label>
                        <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($usuario_editar['nombre'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="apellido1">Apellido Paterno:</label>
                            <input type="text" id="apellido1" name="apellido1" value="<?php echo htmlspecialchars($usuario_editar['apellido1'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="apellido2">Apellido Materno:</label>
                            <input type="text" id="apellido2" name="apellido2" value="<?php echo htmlspecialchars($usuario_editar['apellido2'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="telefono">Teléfono:</label>
                        <input type="text" id="telefono" name="telefono" value="<?php echo htmlspecialchars($usuario_editar['telefono'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="fecha_nacimiento">Fecha Nacimiento:</label>
                            <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" 
                                   max="<?php echo date('Y-m-d'); ?>" 
                                   value="<?php echo htmlspecialchars($usuario_editar['fecha_nacimiento'] ?? ''); ?>"
                                   onchange="calcularEdad()">
                        </div>
                        <div class="form-group">
                            <label for="edad">Edad:</label>
                            <input type="number" id="edad" name="edad" readonly value="<?php echo htmlspecialchars($usuario_editar['edad'] ?? 0); ?>">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="sexo">Sexo:</label>
                            <select id="sexo" name="sexo">
                                <option value="">Seleccione</option>
                                <option value="M" <?php echo (($usuario_editar['sexo'] ?? '') == 'M') ? 'selected' : ''; ?>>Masculino</option>
                                <option value="F" <?php echo (($usuario_editar['sexo'] ?? '') == 'F') ? 'selected' : ''; ?>>Femenino</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="estatus">Estatus:</label>
                            <input type="text" id="estatus" name="estatus" value="<?php echo htmlspecialchars($usuario_editar['estatus'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <!-- Dirección -->
                    <h5>Dirección</h5>
                    <div class="form-group">
                        <label for="calle">Calle:</label>
                        <input type="text" id="calle" name="calle" value="<?php echo htmlspecialchars($usuario_editar['calle'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="num_exterior">Número Exterior:</label>
                            <input type="text" id="num_exterior" name="num_exterior" value="<?php echo htmlspecialchars($usuario_editar['num_exterior'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="num_interior">Número Interior:</label>
                            <input type="text" id="num_interior" name="num_interior" value="<?php echo htmlspecialchars($usuario_editar['num_interior'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="colonia">Colonia:</label>
                        <input type="text" id="colonia" name="colonia" value="<?php echo htmlspecialchars($usuario_editar['colonia'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="codigo_postal">Código Postal:</label>
                        <input type="text" id="codigo_postal" name="codigo_postal" value="<?php echo htmlspecialchars($usuario_editar['codigo_postal'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="municipio_fk">Municipio:</label>
                        <select id="municipio_fk" name="municipio_fk" required onchange="actualizarEntidadFK()">
                            <option value="">Seleccione un municipio</option>
                            <?php foreach ($municipios_list as $municipio): ?>
                                <option value="<?php echo $municipio['municipio_pk']; ?>" 
                                    data-entidad="<?php echo htmlspecialchars($municipio['entidad_fk']); ?>"
                                    <?php echo (($usuario_editar['municipio_fk'] ?? '') == $municipio['municipio_pk']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($municipio['nombre_municipio']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Campo oculto para entidad_fk -->
                    <input type="hidden" id="entidad_fk" name="entidad_fk" value="<?php echo htmlspecialchars($usuario_editar['entidad_fk'] ?? ''); ?>">
                </div>
                
                <!-- Botones de acción -->
                <div class="btn-group">
                    <?php if ($usuario_editar['rol'] === 'Usuario'): ?>
                        <button type="submit" name="editar_usuario_completo" class="btn btn-primary" onclick="return validarFormularioEdicion()">
                            Guardar Cambios
                        </button>
                    <?php else: ?>
                        <button type="submit" name="editar_usuario" class="btn btn-primary">
                            Guardar Cambios
                        </button>
                    <?php endif; ?>
                    <button type="button" onclick="cerrarModal()" class="btn btn-secondary">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>
    <script src="js/admin.js"></script>
</html>