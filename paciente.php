<?php
session_start();
require_once __DIR__ . "/conexion.php";
$conexion = conectarDB();

// Verificar sesión
if (!isset($_SESSION['usuario']['id_usuario'])) {
    header("Location: login.php");
    exit();
}

$id_usuario = $_SESSION['usuario']['id_usuario'];

// OBTENER DATOS DEL USUARIO
$query_perfil = "SELECT 
    u.correo, u.rol,
    p.poliza_id, p.tipo_poliza, p.fecha_inicio, p.fecha_fin, p.fecha_efectiva,
    p.id_cobertura, p.id_servicio, p.id_hospital,
    p.nombre, p.apellido1, p.apellido2, p.telefono, p.fecha_nacimiento, p.sexo, p.estatus,
    p.calle, p.num_exterior, p.num_interior, p.colonia, p.codigo_postal,
    m.nombre_municipio, e.nombre_entidad, p.entidad_fk, p.municipio_fk
FROM usuario u
LEFT JOIN poliza p ON u.id_usuario = p.usuario_fk
LEFT JOIN municipio m ON p.municipio_fk = m.municipio_pk
LEFT JOIN entidad_federativa e ON p.entidad_fk = e.entidad_pk
WHERE u.id_usuario = ?";

$stmt_perfil = $conexion->prepare($query_perfil);
$stmt_perfil->bind_param("i", $id_usuario);
$stmt_perfil->execute();
$result_perfil = $stmt_perfil->get_result();
$datos_usuario = $result_perfil->num_rows > 0 ? $result_perfil->fetch_assoc() : [];

// Variables principales
$tipo_poliza = $datos_usuario['tipo_poliza'] ?? 'Normal';
$municipio_usuario = $datos_usuario['municipio_fk'] ?? null;
$entidad_usuario = $datos_usuario['entidad_fk'] ?? null;

// OBTENER HISTORIAL DE CITAS DEL PACIENTE
$historial_citas = [];
if (!empty($datos_usuario)) {
    $query_historial = "SELECT 
        c.cita_pk, c.fecha_cita, c.hora_cita, c.motivo_consulta, c.estado, c.fecha_creacion,
        m.nombre as nombre_medico,
        h.nombre as nombre_hospital,
        esp.nombre_especialidad
    FROM cita c
    JOIN medico m ON c.medico_fk = m.id_medico
    JOIN hospital h ON c.hospital_fk = h.hospital_pk
    JOIN especialidad esp ON c.especialidad = esp.especialidad_pk
    JOIN poliza p ON c.poliza_fk = p.poliza_pk
    WHERE p.usuario_fk = ?
    ORDER BY c.fecha_cita DESC, c.hora_cita DESC";
    
    $stmt_historial = $conexion->prepare($query_historial);
    $stmt_historial->bind_param("i", $id_usuario);
    $stmt_historial->execute();
    $result_historial = $stmt_historial->get_result();
    
    while ($row = $result_historial->fetch_assoc()) {
        $historial_citas[] = $row;
    }
    $stmt_historial->close();
}

// PROCESAR AGENDAR CITA
$mensaje_exito = $mensaje_error = '';
if (isset($_POST['agendar_cita'])) {
    $medico_fk = $_POST['medico_fk'] ?? null;
    $especialidad_cita = $_POST['especialidad_cita'] ?? '';
    $fecha_cita = $_POST['fecha_cita'] ?? '';
    $hora_cita = $_POST['hora_cita'] ?? '';
    $motivo_consulta = $_POST['motivo_consulta'] ?? '';

    // Validaciones
    if (empty($medico_fk)) {
        $mensaje_error = "Por favor selecciona un médico";
    } elseif (empty($especialidad_cita)) {
        $mensaje_error = "Por favor selecciona una especialidad";
    } elseif (empty($fecha_cita)) {
        $mensaje_error = "Por favor selecciona una fecha";
    } elseif (empty($hora_cita)) {
        $mensaje_error = "Por favor selecciona una hora";
    } elseif (empty($motivo_consulta)) {
        $mensaje_error = "Por favor describe el motivo de la consulta";
    } else {
        // Verificar que el médico pertenece a la especialidad seleccionada
        $query_verificar_medico = "SELECT COUNT(*) as count 
                                   FROM especialidad_medico 
                                   WHERE medico_fk = ? AND especialidad_fk = ?";
        $stmt_verificar = $conexion->prepare($query_verificar_medico);
        $stmt_verificar->bind_param("ii", $medico_fk, $especialidad_cita);
        $stmt_verificar->execute();
        $result_verificar = $stmt_verificar->get_result();
        $row_verificar = $result_verificar->fetch_assoc();
        
        if ($row_verificar['count'] == 0) {
            $mensaje_error = "El médico seleccionado no tiene la especialidad requerida";
        } else {
            // Obtener poliza_fk y hospital del médico
            $query_poliza = "SELECT p.poliza_pk 
                            FROM poliza p 
                            WHERE p.usuario_fk = ? 
                            LIMIT 1";
            $stmt_poliza = $conexion->prepare($query_poliza);
            $stmt_poliza->bind_param("i", $id_usuario);
            $stmt_poliza->execute();
            $poliza_result = $stmt_poliza->get_result();
            
            if ($poliza_result->num_rows > 0) {
                $poliza_data = $poliza_result->fetch_assoc();
                $poliza_fk = $poliza_data['poliza_pk'];
                
                // Obtener hospital del médico
                $query_hospital_medico = "SELECT id_hospital FROM medico WHERE id_medico = ? LIMIT 1";
                $stmt_hospital = $conexion->prepare($query_hospital_medico);
                $stmt_hospital->bind_param("i", $medico_fk);
                $stmt_hospital->execute();
                $hospital_result = $stmt_hospital->get_result();
                $hospital_data = $hospital_result->fetch_assoc();
                $hospital_fk = $hospital_data['id_hospital'];
                
                // Insertar cita
                $query_cita = "INSERT INTO cita (hospital_fk, medico_fk, poliza_fk, fecha_cita, 
                                hora_cita, motivo_consulta, estado, especialidad, fecha_creacion) 
                               VALUES (?, ?, ?, ?, ?, ?, 'Agendada', ?, NOW())";
                $stmt_cita = $conexion->prepare($query_cita);
                $stmt_cita->bind_param("iiisssi", $hospital_fk, $medico_fk, $poliza_fk, 
                                      $fecha_cita, $hora_cita, $motivo_consulta, $especialidad_cita);
                
                if ($stmt_cita->execute()) {
                    $mensaje_exito = "✅ Cita agendada exitosamente";
                    // Actualizar el historial de citas después de agregar una nueva
                    $stmt_cita->close();
                    $stmt_hospital->close();
                    
                    // Recargar historial
                    $stmt_historial = $conexion->prepare($query_historial);
                    $stmt_historial->bind_param("i", $id_usuario);
                    $stmt_historial->execute();
                    $result_historial = $stmt_historial->get_result();
                    $historial_citas = [];
                    while ($row = $result_historial->fetch_assoc()) {
                        $historial_citas[] = $row;
                    }
                    $stmt_historial->close();
                } else {
                    $mensaje_error = "❌ Error al agendar la cita: " . $stmt_cita->error;
                }
            } else {
                $mensaje_error = "❌ No se encontró póliza asociada";
            }
            $stmt_poliza->close();
        }
        $stmt_verificar->close();
    }
}

// FUNCIÓN PARA OBTENER ESPECIALIDADES DISPONIBLES SEGÚN PÓLIZA
function obtenerEspecialidadesFiltradas($conexion, $tipo_poliza, $entidad_usuario) {
    $especialidades = [];
    
    if ($tipo_poliza == 'Normal' && $entidad_usuario) {
        $query = "SELECT DISTINCT e.especialidad_pk, e.nombre_especialidad
                  FROM especialidad e
                  JOIN especialidad_medico em ON e.especialidad_pk = em.especialidad_fk
                  JOIN medico m ON em.medico_fk = m.id_medico
                  JOIN hospital h ON m.id_hospital = h.hospital_pk
                  JOIN municipio mun ON h.municipio_fk = mun.municipio_pk
                  WHERE mun.entidad_fk = ?
                  ORDER BY e.nombre_especialidad";
        $stmt = $conexion->prepare($query);
        $stmt->bind_param("i", $entidad_usuario);
    } else {
        $query = "SELECT especialidad_pk, nombre_especialidad 
                  FROM especialidad 
                  ORDER BY nombre_especialidad";
        $stmt = $conexion->prepare($query);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $especialidades[] = $row;
    }
    $stmt->close();
    
    return $especialidades;
}

// FUNCIÓN PARA OBTENER MÉDICOS FILTRADOS
function obtenerMedicosFiltrados($conexion, $tipo_poliza, $entidad_usuario, $especialidad_id = null) {
    $medicos = [];
    
    $query_base = "SELECT m.id_medico, m.nombre, m.telefono, h.nombre as hospital_nombre,
                          GROUP_CONCAT(DISTINCT e.nombre_especialidad SEPARATOR ', ') as especialidades
                   FROM medico m
                   JOIN hospital h ON m.id_hospital = h.hospital_pk
                   LEFT JOIN especialidad_medico em ON m.id_medico = em.medico_fk
                   LEFT JOIN especialidad e ON em.especialidad_fk = e.especialidad_pk";
    
    $where_conditions = [];
    $params = [];
    $types = "";
    
    // Filtrar por entidad si es póliza Normal
    if ($tipo_poliza == 'Normal' && $entidad_usuario) {
        $where_conditions[] = "h.municipio_fk IN (SELECT municipio_pk FROM municipio WHERE entidad_fk = ?)";
        $params[] = $entidad_usuario;
        $types .= "i";
    }
    
    // Filtrar por especialidad si se especifica
    if ($especialidad_id) {
        $where_conditions[] = "e.especialidad_pk = ?";
        $params[] = $especialidad_id;
        $types .= "i";
    }
    
    // Construir consulta completa
    if (!empty($where_conditions)) {
        $query_base .= " WHERE " . implode(" AND ", $where_conditions);
    }
    
    $query_base .= " GROUP BY m.id_medico ORDER BY m.nombre";
    
    $stmt = $conexion->prepare($query_base);
    if ($types) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $medicos[] = $row;
    }
    $stmt->close();
    
    return $medicos;
}

// OBTENER ESPECIALIDADES PARA EL FORMULARIO
$especialidades_disponibles = obtenerEspecialidadesFiltradas($conexion, $tipo_poliza, $entidad_usuario);

// OBTENER MÉDICOS INICIALES (sin especialidad filtrada)
$medicos_filtrados = obtenerMedicosFiltrados($conexion, $tipo_poliza, $entidad_usuario);

// OBTENER HOSPITALES PARA BÚSQUEDA (optimizada)
$hospitalesPorEstado = [];
$query_hospitales_completa = "SELECT 
    h.hospital_pk, h.nombre, h.telefono, h.calle, h.numero, h.cp, h.horario,
    m.nombre_municipio, e.nombre_entidad,
    GROUP_CONCAT(DISTINCT s.nombre_servicio) as servicios_str,
    GROUP_CONCAT(DISTINCT esp.nombre_especialidad) as especialidades_str
FROM hospital h
JOIN municipio m ON h.municipio_fk = m.municipio_pk
JOIN entidad_federativa e ON m.entidad_fk = e.entidad_pk
LEFT JOIN servicios_hospital sh ON h.hospital_pk = sh.hospital_fk
LEFT JOIN servicios s ON sh.servicio_fk = s.servicio_pk
LEFT JOIN medico med ON h.hospital_pk = med.id_hospital
LEFT JOIN especialidad_medico em ON med.id_medico = em.medico_fk
LEFT JOIN especialidad esp ON em.especialidad_fk = esp.especialidad_pk";

// Agregar filtro según tipo de póliza
if ($tipo_poliza == 'Normal' && $municipio_usuario) {
    $query_hospitales_completa .= " WHERE h.municipio_fk = ?";
    $stmt = $conexion->prepare($query_hospitales_completa . " GROUP BY h.hospital_pk");
    $stmt->bind_param("i", $municipio_usuario);
} else {
    $stmt = $conexion->prepare($query_hospitales_completa . " GROUP BY h.hospital_pk");
}

$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $direccion_completa = $row['calle'] . ' #' . $row['numero'] . ', CP: ' . $row['cp'];
    
    $hospitalesPorEstado[] = [
        'id' => $row['hospital_pk'],
        'nombre' => $row['nombre'],
        'direccion' => $direccion_completa,
        'telefono' => $row['telefono'],
        'municipio' => $row['nombre_municipio'],
        'estado' => $row['nombre_entidad'],
        'codigo_postal' => $row['cp'],
        'horario' => $row['horario'],
        'servicios' => !empty($row['servicios_str']) ? explode(',', $row['servicios_str']) : [],
        'especialidades' => !empty($row['especialidades_str']) ? explode(',', $row['especialidades_str']) : []
    ];
}
$stmt->close();

// FUNCIONES DE BÚSQUEDA OPTIMIZADAS
function normalizarTexto($texto) {
    if (empty($texto)) return '';
    
    $texto = mb_strtolower($texto, 'UTF-8');
    $acentos = [
        'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
        'à' => 'a', 'è' => 'e', 'ì' => 'i', 'ò' => 'o', 'ù' => 'u',
        'ä' => 'a', 'ë' => 'e', 'ï' => 'i', 'ö' => 'o', 'ü' => 'u',
        'ñ' => 'n'
    ];
    
    return strtr($texto, $acentos);
}

function buscarHospitales($hospitales, $termino, $tipoBusqueda = 'general') {
    if (empty($termino)) return $hospitales;
    
    $terminoNormalizado = normalizarTexto($termino);
    $resultados = [];
    
    foreach ($hospitales as $hospital) {
        $encontrado = false;
        
        switch($tipoBusqueda) {
            case 'general':
                $campos = ['nombre', 'municipio', 'direccion', 'estado'];
                foreach ($campos as $campo) {
                    if (isset($hospital[$campo]) && 
                        stripos(normalizarTexto($hospital[$campo]), $terminoNormalizado) !== false) {
                        $encontrado = true;
                        break;
                    }
                }
                
                if (!$encontrado && !empty($hospital['servicios'])) {
                    foreach($hospital['servicios'] as $servicio) {
                        if (stripos(normalizarTexto($servicio), $terminoNormalizado) !== false) {
                            $encontrado = true;
                            break;
                        }
                    }
                }
                
                if (!$encontrado && !empty($hospital['especialidades'])) {
                    foreach($hospital['especialidades'] as $especialidad) {
                        if (stripos(normalizarTexto($especialidad), $terminoNormalizado) !== false) {
                            $encontrado = true;
                            break;
                        }
                    }
                }
                break;
                
            case 'especialidades':
                if (!empty($hospital['especialidades'])) {
                    foreach($hospital['especialidades'] as $especialidad) {
                        if (stripos(normalizarTexto($especialidad), $terminoNormalizado) !== false) {
                            $encontrado = true;
                            break;
                        }
                    }
                }
                break;
        }
        
        if ($encontrado) {
            $resultados[] = $hospital;
        }
    }
    
    return $resultados;
}

// PROCESAR BÚSQUEDA Y PAGINACIÓN
$terminoBusqueda = $_GET['busqueda'] ?? '';
$tipoBusqueda = $_GET['tipo_busqueda'] ?? 'general';
$hospitalesPorPagina = 10;
$paginaActual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$inicio = ($paginaActual - 1) * $hospitalesPorPagina;

// Realizar búsqueda
$resultadosBusqueda = buscarHospitales($hospitalesPorEstado, $terminoBusqueda, $tipoBusqueda);

// Paginación
$totalHospitales = count($resultadosBusqueda);
$totalPaginas = ceil($totalHospitales / $hospitalesPorPagina);
$hospitalesPagina = array_slice($resultadosBusqueda, $inicio, $hospitalesPorPagina);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HealthNet - Sistema de Pacientes</title>
    <link rel="stylesheet" href="css/estilo_P.css">
    <link rel="stylesheet" href="css/estilospaciente.css">
</head>
<body>
    <header>
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <div class="logo-icon">🏥</div>
                    <h1>HealthNet</h1>
                </div>
                <nav>
                    <ul>
                        <li><a href="inicio_s.php">Cerrar sesión</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <div class="main-content">
        <div class="sidebar">
            <ul class="sidebar-menu">
                <li><a href="#busqueda" class="active" onclick="mostrarSeccion('busqueda')">🔍 Búsqueda de Hospitales</a></li>
                <li><a href="#perfil" onclick="mostrarSeccion('perfil')">👤 Mi Perfil</a></li>
                <li><a href="#poliza" onclick="mostrarSeccion('poliza')">📄 Mi Póliza</a></li>
                <li><a href="#cita" onclick="mostrarSeccion('cita')">📅 Agendar Cita</a></li>
                <li><a href="#historial" onclick="mostrarSeccion('historial')">📋 Historial Médico</a></li>
            </ul>
        </div>

        <div class="content-area">
            <!-- SECCIÓN BÚSQUEDA -->
            <section id="busqueda" class="content-section active">
                <!-- ... (mantener igual tu código actual de búsqueda) ... -->
                <div class="page-header">
                    <h2>Búsqueda de Hospitales</h2>
                    <p>Encuentra el hospital más cercano</p>
                </div>

                <div class="search-section">
                    <div class="search-container">
                        <form method="GET" id="formBuscador">
                            <div class="search-box">
                                <input type="text" name="busqueda" placeholder="Buscar hospitales, servicios, especialidades..." 
                                       value="<?php echo htmlspecialchars($terminoBusqueda); ?>">
                                <button type="submit" name="buscar">🔍 Buscar</button>
                            </div>
                            <input type="hidden" name="tipo_busqueda" id="tipoBusqueda" value="<?php echo $tipoBusqueda; ?>">
                            <input type="hidden" name="pagina" value="1">
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <?php echo empty($terminoBusqueda) ? 'Todos los Hospitales Disponibles' : 'Resultados de Búsqueda'; ?>
                        </h3>
                        <div class="result-count">
                            Mostrando <?php echo count($hospitalesPagina); ?> de <?php echo $totalHospitales; ?> hospital(es) - Página <?php echo $paginaActual; ?> de <?php echo $totalPaginas; ?>
                        </div>
                    </div>

                    <?php if (!empty($hospitalesPagina)): ?>
                        <div class="hospital-grid">
                            <?php foreach ($hospitalesPagina as $hospital): ?>
                                <div class="hospital-card">
                                    <div class="hospital-header">
                                        <h3>🏥 <?php echo htmlspecialchars($hospital['nombre']); ?></h3>
                                    </div>
                                    <div class="hospital-body">
                                        <div class="hospital-info">
                                            <p><strong>📍</strong> <?php echo htmlspecialchars($hospital['direccion']); ?></p>
                                            <p><strong>📞</strong> <?php echo htmlspecialchars($hospital['telefono']); ?></p>
                                            <p><strong>🏙️</strong> <?php echo htmlspecialchars($hospital['municipio']); ?>, <?php echo htmlspecialchars($hospital['estado']); ?></p>
                                            <p><strong>🕒</strong> <?php echo htmlspecialchars($hospital['horario']); ?></p>
                                        </div>
                                        
                                        <?php if (!empty($hospital['servicios'])): ?>
                                        <div class="servicios-hospital">
                                            <strong>🛠️ Servicios:</strong>
                                            <div class="tags">
                                                <?php foreach (array_slice($hospital['servicios'], 0, 3) as $servicio): ?>
                                                    <span class="tag"><?php echo htmlspecialchars($servicio); ?></span>
                                                <?php endforeach; ?>
                                                <?php if (count($hospital['servicios']) > 3): ?>
                                                    <span class="tag">+<?php echo count($hospital['servicios']) - 3; ?> más</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <?php endif; ?>

                                        <?php if (!empty($hospital['especialidades'])): ?>
                                        <div class="especialidades-hospital">
                                            <strong>🎯 Especialidades:</strong>
                                            <div class="tags">
                                                <?php foreach (array_slice($hospital['especialidades'], 0, 3) as $especialidad): ?>
                                                    <span class="tag specialty"><?php echo htmlspecialchars($especialidad); ?></span>
                                                <?php endforeach; ?>
                                                <?php if (count($hospital['especialidades']) > 3): ?>
                                                    <span class="tag">+<?php echo count($hospital['especialidades']) - 3; ?> más</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <?php if ($totalPaginas > 1): ?>
                        <div class="paginacion">
                            <?php if ($paginaActual > 1): ?>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => 1])); ?>" class="pagina-btn">« Primera</a>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => $paginaActual - 1])); ?>" class="pagina-btn">‹ Anterior</a>
                            <?php endif; ?>

                            <?php for ($i = max(1, $paginaActual - 2); $i <= min($totalPaginas, $paginaActual + 2); $i++): ?>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => $i])); ?>" 
                                   class="pagina-btn <?php echo $i == $paginaActual ? 'active' : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>

                            <?php if ($paginaActual < $totalPaginas): ?>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => $paginaActual + 1])); ?>" class="pagina-btn">Siguiente ›</a>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => $totalPaginas])); ?>" class="pagina-btn">Última »</a>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                    <?php else: ?>
                        <div class="message info">
                            <h3>No se encontraron resultados para "<?php echo htmlspecialchars($terminoBusqueda); ?>"</h3>
                            <p>Intenta con otros términos de búsqueda o ajusta los filtros.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

            <!-- SECCIÓN PERFIL -->
            <section id="perfil" class="content-section">
                <!-- ... (mantener igual tu código actual de perfil) ... -->
                <div class="page-header">
                    <h2>Mi Perfil</h2>
                    <p>Información personal y de contacto</p>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Datos Personales</h3>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($datos_usuario)): ?>
                            <div class="profile-grid">
                                <div class="profile-card">
                                    <h4>Información Básica</h4>
                                    <div class="profile-group">
                                        <label>Nombre completo:</label>
                                        <span><?php echo htmlspecialchars($datos_usuario['nombre'] . ' ' . $datos_usuario['apellido1'] . ' ' . $datos_usuario['apellido2']); ?></span>
                                    </div>
                                    <div class="profile-group">
                                        <label>Correo electrónico:</label>
                                        <span><?php echo htmlspecialchars($datos_usuario['correo']); ?></span>
                                    </div>
                                    <div class="profile-group">
                                        <label>Teléfono:</label>
                                        <span><?php echo htmlspecialchars($datos_usuario['telefono']); ?></span>
                                    </div>
                                    <div class="profile-group">
                                        <label>Fecha de nacimiento:</label>
                                        <span><?php echo htmlspecialchars($datos_usuario['fecha_nacimiento']); ?></span>
                                    </div>
                                    <?php if (!empty($datos_usuario['fecha_nacimiento'])): 
                                        $fecha_nac = new DateTime($datos_usuario['fecha_nacimiento']);
                                        $hoy = new DateTime();
                                        $edad = $hoy->diff($fecha_nac)->y;
                                    ?>
                                    <div class="profile-group">
                                        <label>Edad:</label>
                                        <span><?php echo $edad; ?> años</span>
                                    </div>
                                    <?php endif; ?>
                                    <div class="profile-group">
                                        <label>Sexo:</label>
                                        <span><?php echo htmlspecialchars($datos_usuario['sexo']); ?></span>
                                    </div>
                                    <div class="profile-group">
                                        <label>Estatus:</label>
                                        <span><?php echo htmlspecialchars($datos_usuario['estatus']); ?></span>
                                    </div>
                                </div>

                                <div class="profile-card">
                                    <h4>Dirección</h4>
                                    <div class="profile-group">
                                        <label>Calle:</label>
                                        <span><?php echo htmlspecialchars($datos_usuario['calle']); ?></span>
                                    </div>
                                    <div class="profile-group">
                                        <label>Número exterior:</label>
                                        <span><?php echo htmlspecialchars($datos_usuario['num_exterior']); ?></span>
                                    </div>
                                    <div class="profile-group">
                                        <label>Número interior:</label>
                                        <span><?php echo htmlspecialchars($datos_usuario['num_interior']); ?></span>
                                    </div>
                                    <div class="profile-group">
                                        <label>Colonia:</label>
                                        <span><?php echo htmlspecialchars($datos_usuario['colonia']); ?></span>
                                    </div>
                                    <div class="profile-group">
                                        <label>Código postal:</label>
                                        <span><?php echo htmlspecialchars($datos_usuario['codigo_postal']); ?></span>
                                    </div>
                                    <div class="profile-group">
                                        <label>Municipio:</label>
                                        <span><?php echo htmlspecialchars($datos_usuario['nombre_municipio']); ?></span>
                                    </div>
                                    <div class="profile-group">
                                        <label>Entidad:</label>
                                        <span><?php echo htmlspecialchars($datos_usuario['nombre_entidad']); ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <p>No se encontraron datos del perfil.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </section>

            <!-- SECCIÓN PÓLIZA -->
            <section id="poliza" class="content-section">
                <!-- ... (mantener igual tu código actual de póliza) ... -->
                <div class="page-header">
                    <h2>Mi Póliza</h2>
                    <p>Información detallada de tu póliza de seguro</p>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Detalles de la Póliza</h3>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($datos_usuario) && !empty($datos_usuario['poliza_id'])): ?>
                            <div class="profile-grid">
                                <div class="profile-card">
                                    <h4>Información de la Póliza</h4>
                                    <div class="profile-group">
                                        <label>Número de póliza:</label>
                                        <span><?php echo htmlspecialchars($datos_usuario['poliza_id']); ?></span>
                                    </div>
                                    <div class="profile-group">
                                        <label>Tipo de póliza:</label>
                                        <span><?php echo htmlspecialchars($datos_usuario['tipo_poliza']); ?></span>
                                    </div>
                                    <div class="profile-group">
                                        <label>ID de cobertura:</label>
                                        <span><?php echo htmlspecialchars($datos_usuario['id_cobertura']); ?></span>
                                    </div>
                                    <div class="profile-group">
                                        <label>ID de servicio:</label>
                                        <span><?php echo htmlspecialchars($datos_usuario['id_servicio']); ?></span>
                                    </div>
                                    <div class="profile-group">
                                        <label>Hospital asignado:</label>
                                        <span><?php echo htmlspecialchars($datos_usuario['id_hospital']); ?></span>
                                    </div>
                                </div>

                                <div class="profile-card">
                                    <h4>Vigencia de la Póliza</h4>
                                    <div class="profile-group">
                                        <label>Fecha de inicio:</label>
                                        <span><?php echo htmlspecialchars($datos_usuario['fecha_inicio']); ?></span>
                                    </div>
                                    <div class="profile-group">
                                        <label>Fecha de fin:</label>
                                        <span><?php echo htmlspecialchars($datos_usuario['fecha_fin']); ?></span>
                                    </div>
                                    <div class="profile-group">
                                        <label>Fecha efectiva:</label>
                                        <span><?php echo htmlspecialchars($datos_usuario['fecha_efectiva']); ?></span>
                                    </div>
                                    <div class="profile-group">
                                        <label>Estado:</label>
                                        <span class="estatus-activo">Activa</span>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <p>No se encontró información de póliza asociada a tu cuenta.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </section>

            <!-- SECCIÓN CITA -->
            <section id="cita" class="content-section">
                <div class="page-header">
                    <h2>Agendar Cita Médica</h2>
                    <p>
                        <?php if ($tipo_poliza == 'Normal'): ?>
                            🔒 <strong>Póliza Básica:</strong> Solo puedes agendar con doctores en tu entidad federativa
                        <?php else: ?>
                            ⭐ <strong>Póliza Premium:</strong> Puedes agendar con cualquier doctor
                        <?php endif; ?>
                    </p>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Nueva Cita</h3>
                    </div>
                    <div class="card-body">
                        <?php if (isset($mensaje_exito)): ?>
                            <div class="message success"><?php echo $mensaje_exito; ?></div>
                        <?php endif; ?>
                        <?php if (isset($mensaje_error)): ?>
                            <div class="message error"><?php echo $mensaje_error; ?></div>
                        <?php endif; ?>

                        <form method="POST" action="" id="formCita">
                            <div class="profile-grid">
                                <div class="profile-card">
                                    <div class="form-group">
                                        <label for="especialidad_cita">Especialidad requerida:</label>
                                        <select name="especialidad_cita" id="especialidad_cita" required 
                                                onchange="filtrarMedicos()">
                                            <option value="">Selecciona una especialidad</option>
                                            <?php foreach ($especialidades_disponibles as $esp): ?>
                                                <option value="<?php echo $esp['especialidad_pk']; ?>">
                                                    <?php echo htmlspecialchars($esp['nombre_especialidad']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="medico_fk">Seleccionar Médico:</label>
                                        <select name="medico_fk" id="medico_fk" required>
                                            <option value="">Primero selecciona una especialidad</option>
                                            <?php foreach ($medicos_filtrados as $medico): ?>
                                                <option value="<?php echo $medico['id_medico']; ?>" 
                                                        data-especialidades="<?php echo htmlspecialchars($medico['especialidades']); ?>">
                                                    Dr. <?php echo htmlspecialchars($medico['nombre']); ?> - 
                                                    Hospital: <?php echo htmlspecialchars($medico['hospital_nombre']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="fecha_cita">Fecha de la cita:</label>
                                        <input type="date" name="fecha_cita" id="fecha_cita" required 
                                               min="<?php echo date('Y-m-d'); ?>">
                                    </div>

                                    <div class="form-group">
                                        <label for="hora_cita">Hora de la cita:</label>
                                        <input type="time" name="hora_cita" id="hora_cita" required 
                                               min="08:00" max="18:00">
                                    </div>
                                </div>

                                <div class="profile-card">
                                    <div class="form-group">
                                        <label for="motivo_consulta">Motivo de la consulta:</label>
                                        <textarea name="motivo_consulta" id="motivo_consulta" rows="8" 
                                                  placeholder="Describe brevemente el motivo de tu consulta..." 
                                                  required></textarea>
                                    </div>

                                    <div class="form-actions">
                                        <button type="submit" name="agendar_cita" class="btn-agendar">
                                            📅 Agendar Cita
                                        </button>
                                    </div>

                                    <div class="cita-info">
                                        <p><strong>Nota:</strong> La cita se agendará con el hospital al que pertenece el médico seleccionado.</p>
                                        <?php if ($tipo_poliza == 'Normal'): ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </section>

            <!-- NUEVA SECCIÓN: HISTORIAL MÉDICO -->
            <section id="historial" class="content-section">
                <div class="page-header">
                    <h2>Historial Médico</h2>
                    <p>Registro de todas tus citas médicas</p>
                </div>

                <div class="card-header">
    <h3 class="historial-title">Citas Realizadas</h3>
    <div class="historial-count">
        <?php echo count($historial_citas); ?> cita(s) en total
    </div>
</div>
                        <?php if (!empty($historial_citas)): ?>
                            <div id="lista-citas">
                                <?php foreach ($historial_citas as $cita): 
                                    // Formatear fecha y hora
                                    $fecha_formateada = date('d/m/Y', strtotime($cita['fecha_cita']));
                                    $hora_formateada = date('h:i A', strtotime($cita['hora_cita']));
                                    
                                    // Clase CSS según estado
                                    $clase_estado = strtolower($cita['estado']);
                                ?>
                                <div class="cita-item <?php echo $clase_estado; ?>" data-estado="<?php echo $cita['estado']; ?>">
                                    <div class="cita-header">
                                        <div class="cita-fecha">
                                            📅 <?php echo $fecha_formateada; ?> - 🕒 <?php echo $hora_formateada; ?>
                                        </div>
                                        <div class="cita-estado estado-<?php echo $clase_estado; ?>">
                                            <?php echo $cita['estado']; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="cita-detalles">
                                        <div class="cita-detalle">
                                            <span class="detalle-label">👨‍⚕️ Médico:</span>
                                            <span class="detalle-valor">Dr. <?php echo htmlspecialchars($cita['nombre_medico']); ?></span>
                                        </div>
                                        <div class="cita-detalle">
                                            <span class="detalle-label">🏥 Hospital:</span>
                                            <span class="detalle-valor"><?php echo htmlspecialchars($cita['nombre_hospital']); ?></span>
                                        </div>
                                        <div class="cita-detalle">
                                            <span class="detalle-label">🎯 Especialidad:</span>
                                            <span class="detalle-valor"><?php echo htmlspecialchars($cita['nombre_especialidad']); ?></span>
                                        </div>
                                        <div class="cita-detalle">
                                            <span class="detalle-label">📅 Fecha de creación:</span>
                                            <span class="detalle-valor"><?php echo date('d/m/Y', strtotime($cita['fecha_creacion'])); ?></span>
                                        </div>
                                    </div>
                                    
                                    <?php if (!empty($cita['motivo_consulta'])): ?>
                                    <div class="cita-motivo">
                                        <span class="motivo-label">📝 Motivo de consulta:</span>
                                        <p class="motivo-texto"><?php echo htmlspecialchars($cita['motivo_consulta']); ?></p>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="no-citas">
                                <span class="no-citas-icon">📋</span>
                                <h3>No tienes citas registradas</h3>
                                <p>Cuando agendes una cita médica, aparecerá aquí en tu historial.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
        </div>
    </div>
    <script src="js/paciente.js"></script>
</body>
</html>