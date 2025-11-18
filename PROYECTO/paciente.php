<?php
session_start();
 
/** 
*if (!isset($_SESSION['correo']) || $_SESSION['rol'] !== 'Usuario') {
 *   header("Location: inicio_s.php");
  *  exit();
*} 
*/

$conexion = new mysqli('localhost', 'root', '', 'healthnet');

// Verificar conexión
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

/** 
*$precios_especialidades = [];
*$query_especialidades = "SELECT nombre, precio FROM especialidad";
*$result_especialidad = $conexion->query($query_especialidad);
*if ($result_especialidad) {
 *   while ($row = $result_especialidad->fetch_assoc()) {
  *      $precios_especialidad[$row['nombre']] = $row['precio'];
   * }
*}
 */
$servicios_hospitalarios = [];
$query_servicios = "SELECT nombre_especialidad FROM servicios";
$result_servicios = $conexion->query($query_servicios);
if ($result_servicios) {
    while ($row = $result_servicios->fetch_assoc()) {
        $servicios_hospitalarios[] = $row['nombre'];
    }
}

$especialidades_medicas = [];
$query_especialidades_lista = "SELECT nombre FROM especialidad";
$result_especialidades_lista = $conexion->query($query_especialidades_lista);
if ($result_especialidades_lista) {
    while ($row = $result_especialidades_lista->fetch_assoc()) {
        $especialidades_medicas[] = $row['nombre'];
    }
}

$hospitalesPorEstado = [];
$query_hospitales = "SELECT * FROM hospital";
$result_hospitales = $conexion->query($query_hospitales);
if ($result_hospitales) {
    while ($row = $result_hospitales->fetch_assoc()) {
        $servicios_hospital = [];
        $query_hospital_servicios = "SELECT s.nombre 
                                   FROM hospital_servicios hs 
                                   JOIN servicios s ON hs.servicio_id = s.id 
                                   WHERE hs.hospital_id = " . $row['id'];
        $result_servicios_hosp = $conexion->query($query_hospital_servicios);
        if ($result_servicios_hosp) {
            while ($servicio = $result_servicios_hosp->fetch_assoc()) {
                $servicios_hospital[] = $servicio['nombre'];
            }
        }

        $especialidades_hospital = [];
        $query_hospital_especialidades = "SELECT e.nombre 
                                        FROM hospital_especialidades he 
                                        JOIN especialidades e ON he.especialidad_id = e.id 
                                        WHERE he.hospital_id = " . $row['id'];
        $result_especialidades_hosp = $conexion->query($query_hospital_especialidades);
        if ($result_especialidades_hosp) {
            while ($especialidad = $result_especialidades_hosp->fetch_assoc()) {
                $especialidades_hospital[] = $especialidad['nombre'];
            }
        }

        $hospitalesPorEstado[] = [
            'id' => $row['id'],
            'nombre' => $row['nombre'],
            'direccion' => $row['direccion'],
            'telefono' => $row['telefono'],
            'municipio' => $row['municipio'],
            'estado' => $row['estado'],
            'codigo_postal' => $row['codigo_postal'],
            'region' => $row['region'],
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
}

$listaHospitales = new ListaDoblementeCircularHospitales();
foreach ($hospitalesPorEstado as $hospital) {
    $listaHospitales->insertar($hospital);
}

$resultadosBusqueda = [];
$terminoBusqueda = '';
$tipoBusqueda = 'general';
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

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: inicio_s.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Red Medica Healthnet - Cuidando tu salud</title>
    <link rel="stylesheet" href="CSS/estilo_P.css">    
</head>
<body>
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
                        <a href="?logout=1">Cerrar Sesión</a>
                    <?php else: ?>
                        <a href="#" onclick="abrirLogin()">Iniciar Sesión</a>
                        <a href="#" onclick="abrirLogin()">Registrarse</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div id="modalLogin" class="modal">
        <div class="modal-content">
            <h2>Iniciar Sesión</h2>
            <?php if (isset($error_login)): ?>
                <div class="mensaje error"><?php echo $error_login; ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label for="username">Usuario:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Contraseña:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="modal-buttons">
                    <button type="button" class="btn-cancel" onclick="cerrarLogin()">Cancelar</button>
                    <button type="submit" class="btn-login" name="login">Ingresar</button>
                </div>
            </form>
        </div>
    </div>

    <header>
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <div class="logo-icon"><img src="imagen/logoH.png" width="50px" height="50px" ></div>
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
                <?php foreach ($precios_especialidades as $especialidad => $precio): ?>
                    <div class="specialty">
                        <i>❤️</i>
                        <h3><?php echo ucfirst(str_replace('_', ' ', $especialidad)); ?></h3>
                        <h4>$<?php echo number_format($precio, 0); ?></h4>
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

            <?php if (isset($_GET['exito']) && $_GET['exito'] == '1'): ?>
                <div class="mensaje exito">¡Gracias! Tu cita ha sido solicitada correctamente. Te contactaremos pronto.</div>
            <?php elseif (isset($_GET['error']) && $_GET['error'] == '1'): ?>
                <div class="mensaje error">Error: Por favor completa todos los campos requeridos.</div>
            <?php endif; ?>

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
                    
                    <?php if (isset($_SESSION['usuario'])): ?>
                        <button type="button" class="auto-fill-btn" onclick="autoCompletarDatos()">
                            Auto-completar con mis datos
                        </button>
                    <?php else: ?>
                        <div style="background: #e3f2fd; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
                            <small>💡 <a href="#" onclick="abrirLogin()" style="color: #0d47a1;">Inicia sesión</a> para auto-completar tus datos</small>
                        </div>
                    <?php endif; ?>

                    <form action="procesar_cita.php" method="POST" id="formCita">
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
                                <option value="cardiologia" data-precio="900">Cardiología - $900</option>
                                <option value="pediatria" data-precio="800">Pediatría - $800</option>
                                <option value="ginecologia" data-precio="940">Ginecología - $940</option>
                                <option value="traumatologia" data-precio="1000">Traumatología - $1,000</option>
                                <option value="neurologia" data-precio="850">Neurología - $850</option>
                                <option value="oncologia" data-precio="700">Oncología - $700</option>
                                <option value="oftamologo" data-precio="600">Oftalmología - $600</option>
                                <option value="cirugia" data-precio="650">Cirugía - $650</option>
                                <option value="dermatologia" data-precio="800">Dermatología - $800</option>
                                <option value="medicina_general" data-precio="820">Medicina General - $820</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="total">Total a Pagar:</label>
                            <input type="text" id="total" name="total" readonly 
                                   style="background-color: #e8f5e8; font-weight: bold; color: #2e7d32; font-size: 1.1rem;"
                                   placeholder="Selecciona una especialidad">
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
        // FUNCIONES DE LOGIN
        function abrirLogin() {
            document.getElementById('modalLogin').style.display = 'block';
        }

        function cerrarLogin() {
            document.getElementById('modalLogin').style.display = 'none';
        }

        window.onclick = function(event) {
            const modal = document.getElementById('modalLogin');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        }

        // FUNCIÓN PARA AUTO-COMPLETAR DATOS DEL USUARIO
        function autoCompletarDatos() {
            <?php if (isset($_SESSION['usuario'])): ?>
                document.getElementById('nombre').value = '<?php echo $_SESSION['usuario']['nombre']; ?>';
                document.getElementById('email').value = '<?php echo $_SESSION['usuario']['email']; ?>';
                document.getElementById('telefono').value = '<?php echo $_SESSION['usuario']['telefono']; ?>';
                
                alert('Datos auto-completados correctamente.');
            <?php else: ?>
                alert('Debes iniciar sesión para usar esta función.');
                abrirLogin();
            <?php endif; ?>
        }

        // FUNCIÓN PARA SELECCIONAR HOSPITAL
        function seleccionarHospital(nombreHospital) {
            document.getElementById('hospital').value = nombreHospital;
            document.getElementById('contacto').scrollIntoView({ behavior: 'smooth' });
            
            alert('Hospital "' + nombreHospital + '" seleccionado. Ahora completa el formulario de cita.');
        }

        // FUNCIÓN PARA CALCULAR TOTAL DE CITA
        function calcularTotal() {
            const especialidadSelect = document.getElementById('especialidad');
            const totalInput = document.getElementById('total');
            const opcionSeleccionada = especialidadSelect.options[especialidadSelect.selectedIndex];
            
            if (opcionSeleccionada.value !== '') {
                const precio = opcionSeleccionada.getAttribute('data-precio');
                totalInput.value = '$' + precio + ' MXN';
            } else {
                totalInput.value = '';
            }
        }

        // FUNCIONES DE BÚSQUEDA MEJORADAS
        function cambiarFiltro(tipo) {
            document.getElementById('tipoBusqueda').value = tipo;
            
            // Actualizar clases activas
            document.querySelectorAll('.filtro-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            event.target.classList.add('active');
            
            // Enviar formulario automáticamente (siempre, incluso sin término)
            document.getElementById('formBuscador').submit();
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

        // SCROLL AUTOMÁTICO A RESULTADOS (ahora siempre visible)
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                document.getElementById('resultados-busqueda').scrollIntoView({ 
                    behavior: 'smooth',
                    block: 'start'
                });
            }, 300);
        });

        // INICIALIZAR CÁLCULO DE TOTAL AL CARGAR LA PÁGINA
        document.addEventListener('DOMContentLoaded', function() {
            calcularTotal();
        });
    </script>
</body>
</html>