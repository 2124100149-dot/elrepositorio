<?php
session_start();

$conexion = new mysqli('localhost', 'root', '', 'healthnet');

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Inicializar variables para evitar errores
$terminoBusqueda = '';
$tipoBusqueda = 'general';
$hospitalesPorPagina = 10;
$paginaActual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$inicio = ($paginaActual - 1) * $hospitalesPorPagina;
$hospitalesPagina = [];
$totalHospitales = 0;
$totalPaginas = 0;

$servicios_hospitalarios = [];
$query_servicios = "SELECT nombre_servicio FROM servicios";
$result_servicios = $conexion->query($query_servicios);
if ($result_servicios) {
    while ($row = $result_servicios->fetch_assoc()) {
        $servicios_hospitalarios[] = $row['nombre_servicio'];
    }
}

$especialidades_medicas = [];
$query_especialidades_lista = "SELECT nombre_especialidad FROM especialidad";
$result_especialidades_lista = $conexion->query($query_especialidades_lista);
if ($result_especialidades_lista) {
    while ($row = $result_especialidades_lista->fetch_assoc()) {
        $especialidades_medicas[] = $row['nombre_especialidad'];
    }
}

$hospitalesPorEstado = [];
$query_hospitales = "SELECT h.hospital_pk, h.nombre, h.telefono, h.calle, h.numero, h.cp, h.horario,
                            m.nombre_municipio, e.nombre_entidad
                    FROM hospital h
                    JOIN municipio m ON h.municipio_fk = m.municipio_pk
                    JOIN entidad_federativa e ON m.entidad_fk = e.entidad_pk";
                    
$result_hospitales = $conexion->query($query_hospitales);

if ($result_hospitales) {
    while ($row = $result_hospitales->fetch_assoc()) {
        $servicios_hospital = [];
        
        $query_servicios = "SELECT s.nombre_servicio 
                           FROM servicios_hospital sh
                           JOIN servicios s ON sh.servicio_fk = s.servicio_pk 
                           WHERE sh.hospital_fk = ?";
        
        $stmt = $conexion->prepare($query_servicios);
        $stmt->bind_param("i", $row['hospital_pk']);
        $stmt->execute();
        $result_servicios = $stmt->get_result();
        
        if ($result_servicios) {   
            while ($servicio = $result_servicios->fetch_assoc()) {
                $servicios_hospital[] = $servicio['nombre_servicio'];
            }
        }
        
        $especialidades_hospital = [];
        $query_especialidades = "SELECT DISTINCT esp.nombre_especialidad 
                                FROM medico med
                                JOIN especialidad_medico em ON med.id_medico = em.medico_fk
                                JOIN especialidad esp ON em.especialidad_fk = esp.especialidad_pk
                                WHERE med.id_hospital = ?";
        
        $stmt2 = $conexion->prepare($query_especialidades);
        $stmt2->bind_param("i", $row['hospital_pk']);
        $stmt2->execute();
        $result_especialidades = $stmt2->get_result();
        
        if ($result_especialidades) {   
            while ($especialidad = $result_especialidades->fetch_assoc()) {
                $especialidades_hospital[] = $especialidad['nombre_especialidad'];
            }
        }
        
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
            'servicios' => $servicios_hospital,
            'especialidades' => $especialidades_hospital
        ];
    }
}

/**
 * LISTA DOBLEMENTE CIRCULAR 
 */
class NodoHospital {
    public $hospital;
    public $siguiente;
    public $anterior;
    
    public function __construct($hospital) {
        $this->hospital = $hospital;
        $this->siguiente = null;
        $this->anterior = null;
    }
}

class ListaDoblementeCircularHospitales {
    private $cabeza;
    private $tamaño;
    
    public function __construct() {
        $this->cabeza = null;
        $this->tamaño = 0;
    }
    
    public function insertar($hospital) {
        $nuevo = new NodoHospital($hospital);
        
        if ($this->cabeza === null) {
            $this->cabeza = $nuevo;
            $nuevo->siguiente = $nuevo;
            $nuevo->anterior = $nuevo;
        } else {
            $ultimo = $this->cabeza->anterior;
            
            $ultimo->siguiente = $nuevo;
            $nuevo->anterior = $ultimo;
            $nuevo->siguiente = $this->cabeza;
            $this->cabeza->anterior = $nuevo;
        }
        $this->tamaño++;
    }
    
    private function normalizarTexto($texto) {
        if (empty($texto)) return '';
        
        $texto = mb_strtolower($texto, 'UTF-8');
        
        $acentos = [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
            'à' => 'a', 'è' => 'e', 'ì' => 'i', 'ò' => 'o', 'ù' => 'u',
            'ä' => 'a', 'ë' => 'e', 'ï' => 'i', 'ö' => 'o', 'ü' => 'u',
            'ñ' => 'n', 'ç' => 'c'
        ];
        
        return strtr($texto, $acentos);
    }
    
    public function busquedaSecuencial($termino, $tipoBusqueda = 'general') {
        if ($this->cabeza === null) return [];
        
        $resultados = [];
        $actual = $this->cabeza;
        $contador = 0;
        
        $terminoNormalizado = $this->normalizarTexto($termino);
        
        do {
            $hospital = $actual->hospital;
            $encontrado = false;
            
            switch($tipoBusqueda) {
                case 'general':
                    $campos = ['nombre', 'municipio', 'direccion', 'estado', 'codigo_postal'];
                    foreach ($campos as $campo) {
                        if (isset($hospital[$campo]) && 
                            stripos($this->normalizarTexto($hospital[$campo]), $terminoNormalizado) !== false) {
                            $encontrado = true;
                            break;
                        }
                    }

                    if (!$encontrado && isset($hospital['servicios'])) {
                        foreach($hospital['servicios'] as $servicio) {
                            if (stripos($this->normalizarTexto($servicio), $terminoNormalizado) !== false) {
                                $encontrado = true;
                                break;
                            }
                        }
                    }
                    
                    if (!$encontrado && isset($hospital['especialidades'])) {
                        foreach($hospital['especialidades'] as $especialidad) {
                            if (stripos($this->normalizarTexto($especialidad), $terminoNormalizado) !== false) {
                                $encontrado = true;
                                break;
                            }
                        }
                    }
                    break;
                    
                case 'servicios':
                    if (isset($hospital['servicios'])) {
                        foreach($hospital['servicios'] as $servicio) {
                            if (stripos($this->normalizarTexto($servicio), $terminoNormalizado) !== false) {
                                $encontrado = true;
                                break;
                            }
                        }
                    }
                    break;
                    
                case 'especialidades':
                    if (isset($hospital['especialidades'])) {
                        foreach($hospital['especialidades'] as $especialidad) {
                            if (stripos($this->normalizarTexto($especialidad), $terminoNormalizado) !== false) {
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
            
            $actual = $actual->siguiente;
            $contador++;
        } while ($actual !== $this->cabeza && $contador < $this->tamaño);
        
        return $resultados;
    }
    
    public function obtenerTodos() {
        if ($this->cabeza === null) return [];
        
        $datos = [];
        $actual = $this->cabeza;
        $contador = 0;
        
        do {
            $datos[] = $actual->hospital;
            $actual = $actual->siguiente;
            $contador++;
        } while ($actual !== $this->cabeza && $contador < $this->tamaño);
        
        return $datos;
    }
    
    public function obtenerPorPagina($inicio, $cantidad) {
        $todos = $this->obtenerTodos();
        return array_slice($todos, $inicio, $cantidad);
    }
    
    public function obtenerTotal() {
        return $this->tamaño;
    }
}

$listaHospitales = new ListaDoblementeCircularHospitales();
foreach ($hospitalesPorEstado as $hospital) {
    $listaHospitales->insertar($hospital);
}

$resultadosBusqueda = [];
$mostrarResultados = false;

if (isset($_GET['buscar']) && !empty(trim($_GET['busqueda']))) {
    $terminoBusqueda = trim($_GET['busqueda']);
    $tipoBusqueda = isset($_GET['tipo_busqueda']) ? $_GET['tipo_busqueda'] : 'general';
    $resultadosBusqueda = $listaHospitales->busquedaSecuencial($terminoBusqueda, $tipoBusqueda);
    $mostrarResultados = true;
} else {
    $resultadosBusqueda = $listaHospitales->obtenerTodos();
    $mostrarResultados = true;
    $terminoBusqueda = '';
}

// Calcular paginación
$totalHospitales = count($resultadosBusqueda);
$totalPaginas = ceil($totalHospitales / $hospitalesPorPagina);

// Obtener hospitales para la página actual
$hospitalesPagina = array_slice($resultadosBusqueda, $inicio, $hospitalesPorPagina);

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $query_usuario = "SELECT * FROM usuario WHERE username = ? AND password = MD5(?)";
    $stmt = $conexion->prepare($query_usuario);
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $usuario = $result->fetch_assoc();
        $_SESSION['usuario'] = [
            'username' => $usuario['username'],
            'nombre' => $usuario['nombre'],
            'email' => $usuario['email'],
            'telefono' => $usuario['telefono'],
            'tipo' => $usuario['tipo']
        ];
        header("Location: inicio_s.php");
        exit();
    } else {
        $error_login = "Usuario o contraseña incorrectos";
    }
}

// Función auxiliar para obtener tipo de póliza
function obtenerTipoPoliza() {
    // Esta función debería obtener el tipo de póliza real del usuario
    // Por ahora, devolvemos un valor de ejemplo
    return 'Normal'; // o 'Premium'
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HealthNet - Sistema de Pacientes</title>
    <link rel="stylesheet" href="css/estilo_P.css">
</head>
<body>
    <!-- User Panel 
    <div class="user-panel">
        <div class="container">
            <div class="user-info">
                <div class="user-welcome">
                    <?php if (isset($_SESSION['usuario'])): ?>
                        Bienvenido, <span><?php echo $_SESSION['usuario']['nombre']; ?></span> 
                        (<?php echo $_SESSION['usuario']['tipo']; ?>)
                    <?php else: ?>
                        Bienvenido
                    <?php endif; ?>
                </div>
                <div class="user-actions">
                    <?php if (isset($_SESSION['usuario'])): ?>
                        <a href="login.php">Cerrar Sesión</a>
                    <?php else: ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div> -->

    <!-- Header -->
    <header>
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <div class="logo-icon">🏥</div>
                    <h1>HealthNet</h1>
                </div>
                <nav>
                    <ul>
                        <li><a href="inicio_s.php" class="active">Cerrar sesión</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Sidebar -->
        <div class="sidebar">
            <ul class="sidebar-menu">
                <li><a href="#busqueda" class="active" onclick="mostrarSeccion('busqueda')">🔍 Búsqueda de Hospitales</a></li>
                <li><a href="#perfil" onclick="mostrarSeccion('perfil')">👤 Mi Perfil</a></li>
                <li><a href="#poliza" onclick="mostrarSeccion('poliza')">📄 Mi Póliza</a></li>
                <li><a href="#cita" onclick="mostrarSeccion('cita')">📅 Agendar Cita</a></li>
            </ul>
        </div>

        <!-- Content Area -->
        <div class="content-area">
            <!-- Search Section -->
            <section id="busqueda" class="content-section active">
                <div class="page-header">
                    <h2>Búsqueda de Hospitales</h2>
                    <p>Encuentra el hospital que necesitas según tu póliza y ubicación</p>
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
                            
                            <div class="filter-buttons">
                                <button type="button" class="filter-btn <?php echo $tipoBusqueda == 'general' ? 'active' : ''; ?>" 
                                        onclick="cambiarFiltro('general')">
                                    🔍 Búsqueda General
                                </button>
                                <button type="button" class="filter-btn <?php echo $tipoBusqueda == 'especialidades' ? 'active' : ''; ?>" 
                                        onclick="cambiarFiltro('especialidades')">
                                    👨‍⚕️ Medicos
                                </button>
                            </div>
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
                                        <h3>🏥 <?php echo $hospital['nombre']; ?></h3>
                                    </div>
                                    <div class="hospital-body">
                                        <div class="hospital-info">
                                            <p><strong>📍</strong> <?php echo $hospital['direccion']; ?></p>
                                            <p><strong>📞</strong> <?php echo $hospital['telefono']; ?></p>
                                            <p><strong>🏙️</strong> <?php echo $hospital['municipio']; ?>, <?php echo $hospital['estado']; ?></p>
                                            <p><strong>🕒</strong> <?php echo $hospital['horario']; ?></p>
                                        </div>
                                        
                                        <?php if (isset($hospital['servicios'])): ?>
                                        <div class="servicios-hospital">
                                            <strong>🛠️ Servicios:</strong>
                                            <div class="tags">
                                                <?php foreach (array_slice($hospital['servicios'], 0, 3) as $servicio): ?>
                                                    <span class="tag"><?php echo $servicio; ?></span>
                                                <?php endforeach; ?>
                                                <?php if (count($hospital['servicios']) > 3): ?>
                                                    <span class="tag">+<?php echo count($hospital['servicios']) - 3; ?> más</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <?php endif; ?>

                                        <?php if (isset($hospital['especialidades'])): ?>
                                        <div class="especialidades-hospital">
                                            <strong>🎯 Especialidades:</strong>
                                            <div class="tags">
                                                <?php foreach (array_slice($hospital['especialidades'], 0, 3) as $especialidad): ?>
                                                    <span class="tag specialty"><?php echo $especialidad; ?></span>
                                                <?php endforeach; ?>
                                                <?php if (count($hospital['especialidades']) > 3): ?>
                                                    <span class="tag">+<?php echo count($hospital['especialidades']) - 3; ?> más</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <?php endif; ?>

                                        <button onclick="seleccionarHospital('<?php echo $hospital['nombre']; ?>')" 
                                                class="btn" style="width: 100%; margin-top: 15px;">
                                            Seleccionar este Hospital
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- PAGINACIÓN -->
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

            <!-- Las demás secciones se mantienen igual... -->
            <!-- Profile Section -->
            <section id="perfil" class="content-section">
                <!-- ... contenido del perfil ... -->
            </section>

            <!-- Policy Section -->
            <section id="poliza" class="content-section">
                <!-- ... contenido de póliza ... -->
            </section>

            <!-- Appointment Section -->
            <section id="cita" class="content-section">
                <!-- ... contenido de cita ... -->
            </section>

        </div>
    </div>

    <!-- Los modales se mantienen igual -->
</body>
</html>