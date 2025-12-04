<?php
session_start();

require_once __DIR__ . "/conexion.php";
$conexion = conectarDB();

// Obtener municipios para el select
$municipios = [];
$query_municipios = $conexion->query("SELECT municipio_pk, nombre_municipio FROM municipio ORDER BY nombre_municipio");
if ($query_municipios) {
    while ($row = $query_municipios->fetch_assoc()) {
        $municipios[] = $row;
    }
}

// Obtener servicios para los checkboxes
$servicios = [];
$query_servicios = $conexion->query("SELECT servicio_pk, nombre_servicio FROM servicios ORDER BY nombre_servicio");
if ($query_servicios) {
    while ($row = $query_servicios->fetch_assoc()) {
        $servicios[] = $row;
    }
}

// Inicializar variables
$error = '';
$success = false;
$datos_formulario = [
    'nombre' => '',
    'telefono' => '',
    'calle' => '',
    'numero' => '',
    'cp' => '',
    'horario' => '',
    'municipio_fk' => ''
];
$servicios_seleccionados = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Campos del hospital
    $datos_formulario['nombre'] = $_POST['nombre'] ?? '';
    $datos_formulario['telefono'] = $_POST['telefono'] ?? '';
    $datos_formulario['calle'] = $_POST['calle'] ?? '';
    $datos_formulario['numero'] = $_POST['numero'] ?? '';
    $datos_formulario['cp'] = $_POST['cp'] ?? '';
    $datos_formulario['horario'] = $_POST['horario'] ?? '';
    $datos_formulario['municipio_fk'] = $_POST['municipio_fk'] ?? '';
    $servicios_seleccionados = $_POST['servicios'] ?? [];

    // Validaciones básicas
    if (empty($datos_formulario['nombre']) || empty($datos_formulario['telefono']) || empty($datos_formulario['municipio_fk'])) {
        $error = "Por favor completa los campos obligatorios (*)";
    } else {
        
        // Validar formato de teléfono (solo números y guiones)
        if (!preg_match('/^[\d\s\-\+\(\)]{8,15}$/', $datos_formulario['telefono'])) {
            $error = "Por favor ingresa un número de teléfono válido";
        } 
        // Validar código postal si se proporciona
        elseif (!empty($datos_formulario['cp']) && !preg_match('/^\d{5}$/', $datos_formulario['cp'])) {
            $error = "El código postal debe tener 5 dígitos";
        } else {
            
            // Verificar si el hospital ya existe
            $check = $conexion->prepare("SELECT hospital_pk FROM hospital WHERE nombre = ?");
            $check->bind_param("s", $datos_formulario['nombre']);
            $check->execute();
            $check->store_result();

            if ($check->num_rows > 0) {
                $error = "Ya existe un hospital con este nombre";
                $check->close();
            } else {
                $check->close();
                
                // Insertar el hospital - CORRECCIÓN: fotourl en lugar de fotouri
                $fotourl = ''; // Campo vacío como solicitaste
                
                $stmt = $conexion->prepare("
                    INSERT INTO hospital (
                        nombre, telefono, calle, numero, cp, horario, municipio_fk, fotourl
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                if ($stmt) {
                    $stmt->bind_param(
                        "ssssssis",  // NOTA: El último 's' es para fotourl
                        $datos_formulario['nombre'],
                        $datos_formulario['telefono'],
                        $datos_formulario['calle'],
                        $datos_formulario['numero'],
                        $datos_formulario['cp'],
                        $datos_formulario['horario'],
                        $datos_formulario['municipio_fk'],
                        $fotourl
                    );

                    if ($stmt->execute()) {
                        $hospital_id = $stmt->insert_id;
                        $success = true;
                        
                        // Registrar los servicios del hospital si se seleccionaron
                        if (!empty($servicios_seleccionados)) {
                            foreach ($servicios_seleccionados as $servicio_id) {
                                $sql_servicio = $conexion->prepare("
                                    INSERT INTO servicios_hospital (servicio_fk, hospital_fk) 
                                    VALUES (?, ?)
                                ");
                                if ($sql_servicio) {
                                    $sql_servicio->bind_param("ii", $servicio_id, $hospital_id);
                                    if (!$sql_servicio->execute()) {
                                        $error = "Error al registrar algunos servicios: " . $sql_servicio->error;
                                        $success = false;
                                    }
                                    $sql_servicio->close();
                                }
                            }
                        }
                        
                        if ($success) {
                            $_SESSION['mensaje_exito'] = "Hospital registrado exitosamente";
                            header("Location: admin.php");
                            exit();
                        }
                        
                    } else {
                        $error = "Error al registrar el hospital: " . $stmt->error;
                    }
                    
                    $stmt->close();
                } else {
                    $error = "Error en la preparación de la consulta: " . $conexion->error;
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Hospital</title>
    <link rel="stylesheet" href="css/estilos_registro.css">
    <link rel="stylesheet" href="css/registrohospital.css">
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h2>Registro de Hospital</h2>
            
            <?php if (isset($error) && !empty($error)): ?>
                <div class="error-message" style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px; border-left: 4px solid #f5c6cb;">
                    ❌ <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['mensaje_exito'])): ?>
                <div class="success-message" style="background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px; border-left: 4px solid #c3e6cb;">
                    ✅ <?php echo htmlspecialchars($_SESSION['mensaje_exito']); ?>
                    <?php unset($_SESSION['mensaje_exito']); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="registro-form" onsubmit="return validarFormulario()">
                
                <!-- Información Básica del Hospital -->
                <div class="form-section">
                    <h3>Información Básica</h3>
                    
                    <div class="form-group">
                        <label for="nombre" class="required">Nombre del Hospital:</label>
                        <input type="text" id="nombre" name="nombre" placeholder="Nombre del hospital" required 
                               maxlength="100" value="<?php echo htmlspecialchars($datos_formulario['nombre']); ?>"
                               oninput="validarNombre(this)">
                        <small class="text-muted">Máximo 100 caracteres</small>
                    </div>

                    <div class="form-group">
                        <label for="telefono" class="required">Teléfono:</label>
                        <input type="text" id="telefono" name="telefono" placeholder="Ej: 123-456-7890" required 
                               maxlength="20" value="<?php echo htmlspecialchars($datos_formulario['telefono']); ?>"
                               oninput="validarTelefono(this)">
                    </div>

                    <div class="form-group">
                        <label for="horario">Horario:</label>
                        <input type="text" id="horario" name="horario" placeholder="Ej: Lunes a Viernes 8:00 - 18:00" 
                               maxlength="100" value="<?php echo htmlspecialchars($datos_formulario['horario']); ?>">
                        <small class="text-muted">Ej: Lunes a Viernes 8:00-18:00, Sábados 9:00-13:00</small>
                    </div>
                </div>

                <!-- Dirección -->
                <div class="form-section">
                    <h3>Dirección</h3>
                    
                    <div class="form-group">
                        <label for="calle">Calle:</label>
                        <input type="text" id="calle" name="calle" placeholder="Nombre de la calle" 
                               maxlength="200" value="<?php echo htmlspecialchars($datos_formulario['calle']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="numero">Número:</label>
                        <input type="text" id="numero" name="numero" placeholder="Número exterior" 
                               maxlength="20" value="<?php echo htmlspecialchars($datos_formulario['numero']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="cp">Código Postal:</label>
                        <input type="text" id="cp" name="cp" placeholder="Código postal (5 dígitos)" 
                               maxlength="5" pattern="\d{5}" value="<?php echo htmlspecialchars($datos_formulario['cp']); ?>"
                               oninput="validarCP(this)">
                        <small class="text-muted">5 dígitos</small>
                    </div>

                    <div class="form-group">
                        <label for="municipio_fk" class="required">Municipio:</label>
                        <select name="municipio_fk" id="municipio_fk" required>
                            <option value="">Seleccione un municipio</option>
                            <?php foreach ($municipios as $municipio): ?>
                                <option value="<?php echo $municipio['municipio_pk']; ?>" 
                                    <?php echo ($datos_formulario['municipio_fk'] == $municipio['municipio_pk']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($municipio['nombre_municipio']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

               <!-- En la sección de Servicios del formulario -->
<?php if (!empty($servicios)): ?>
<div class="form-section">
    <h3>Servicios Ofrecidos</h3>
    <p>Seleccione los servicios que ofrece este hospital:</p>
    
    <div class="servicios-grid">
        <?php foreach ($servicios as $servicio): ?>
            <div class="servicio-item">
                <input type="checkbox" name="servicios[]" value="<?php echo $servicio['servicio_pk']; ?>" 
                    id="servicio_<?php echo $servicio['servicio_pk']; ?>"
                    <?php echo (in_array($servicio['servicio_pk'], $servicios_seleccionados ?? [])) ? 'checked' : ''; ?>>
                <label for="servicio_<?php echo $servicio['servicio_pk']; ?>">
                    <?php echo htmlspecialchars($servicio['nombre_servicio']); ?>
                </label>
            </div>
        <?php endforeach; ?>
    </div>
    
    <div style="margin-top: 15px;">
        <!-- CAMBIA ESTOS BOTONES PARA QUE TENGAN LOS IDs CORRECTOS -->
        <button type="button" class="btn-outline" id="selectAllBtn">
            Seleccionar Todos
        </button>
        <button type="button" class="btn-outline" id="deselectAllBtn" style="margin-left: 10px;">
            Deseleccionar Todos
        </button>
    </div>
</div>
<?php endif; ?>

                <div class="button-group" style="margin-top: 30px; display: flex; gap: 15px;">
                    <button type="submit" class="btn btn-primary" style="flex: 1; padding: 12px; background: #3498db; color: white; border: none; border-radius: 5px; cursor: pointer;">
                        Registrar Hospital
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="location.href='admin.php'" style="flex: 1; padding: 12px; background: #95a5a6; color: white; border: none; border-radius: 5px; cursor: pointer;">
                        Volver al Panel
                    </button>
                </div>
            </form>
        </div>
    </div>
    <script src="js/agregar_hospital.js"></script>
</body>
</html>