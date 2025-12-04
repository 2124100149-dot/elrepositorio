<?php
session_start();

require_once __DIR__ . "/conexion.php";
$conexion = conectarDB();

$municipios = [];
$query_municipios = $conexion->query("SELECT municipio_pk, nombre_municipio, entidad_fk FROM municipio ORDER BY nombre_municipio");
if ($query_municipios) {
    while ($row = $query_municipios->fetch_assoc()) {
        $municipios[] = $row;
    }
}

// Obtener hospitales para médicos
$hospitales = [];
$query_hospitales = $conexion->query("SELECT hospital_pk, nombre FROM hospital ORDER BY nombre");
if ($query_hospitales) {
    while ($row = $query_hospitales->fetch_assoc()) {
        $hospitales[] = $row;
    }
}

// Obtener especialidades para médicos
$especialidades = [];
$query_especialidades = $conexion->query("SELECT especialidad_pk, nombre_especialidad FROM especialidad ORDER BY nombre_especialidad");
if ($query_especialidades) {
    while ($row = $query_especialidades->fetch_assoc()) {
        $especialidades[] = $row;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $correo = $_POST['correo'] ?? '';
    $rol = $_POST['rol'] ?? '';
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['reviewPassword'] ?? '';

    // Campos para usuario
    $poliza = $_POST['poliza'] ?? '';
    $tipo_poliza = $_POST['tipo_poliza'] ?? '';
    $fecha_inicio = $_POST['fecha_inicio'] ?? '';
    $fecha_fin = $_POST['fecha_fin'] ?? '';
    $fecha_efectiva = $_POST['fecha_efectiva'] ?? '';
    $nombre = $_POST['nombre'] ?? '';
    $ap_mat = $_POST['ap_mat'] ?? '';
    $ap_pat = $_POST['ap_pat'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $fecha_nac = $_POST['fecha_nac'] ?? '';
    $sexo = $_POST['sexo'] ?? '';
    $estatus = $_POST['estatus'] ?? '';
      $edad = 0;
  if (!empty($fecha_nac)) {
      $fechaNacimiento = new DateTime($fecha_nac);
      $hoy = new DateTime();
      $edad = $hoy->diff($fechaNacimiento)->y;
  }
    $calle = $_POST['calle'] ?? '';
    $num_ext = $_POST['num_ext'] ?? '';
    $num_int = $_POST['num_int'] ?? '';
    $colonia = $_POST['colonia'] ?? '';
    $cp = $_POST['cp'] ?? '';
    $municipio_fk = $_POST['municipio_fk'] ?? '';

    // Campos para médico
    $id_hospital = $_POST['id_hospital'] ?? '';
    $nombre_medico = $_POST['nombre_medico'] ?? '';
    $telefono_medico = $_POST['telefono_medico'] ?? '';
    $fecha_registro = date('Y-m-d');
    $especialidades_medico = $_POST['especialidades'] ?? [];

    $entidad_fk = '';
    if (!empty($municipio_fk)) {
        foreach ($municipios as $municipio) {
            if ($municipio['municipio_pk'] == $municipio_fk) {
                $entidad_fk = $municipio['entidad_fk'];
                break;
            }
        }
    }

    if (empty($correo) || empty($rol) || empty($password) || empty($password2)) {
        $error = "Por favor completa los campos obligatorios";
    } 
    else if ($password !== $password2) {
        $error = "Las contraseñas no coinciden";
    } 
    else {
        // Validaciones de fechas
        $fecha_actual = date('Y-m-d');
        
        // Validar fecha de nacimiento (no puede ser futura)
          if (!empty($fecha_nac)) {
      $fechaNacimiento = new DateTime($fecha_nac);
      $hoy = new DateTime();
      $edad = $hoy->diff($fechaNacimiento)->y;
  } else {
      $edad = 0; // o null, dependiendo de tu base de datos
  }
        
        // Validar fecha de inicio (no puede ser anterior a hoy)
        if ($fecha_inicio < $fecha_actual) {
            $error = "La fecha de inicio no puede ser anterior a hoy.";
        }
        
        // Validar fecha efectiva (no puede ser anterior a hoy)
        if ($fecha_efectiva < $fecha_actual) {
            $error = "La fecha efectiva no puede ser anterior a hoy.";
        }
        
        // Validar que fecha fin sea posterior a fecha inicio
        if ($fecha_fin <= $fecha_inicio) {
            $error = "La fecha fin debe ser posterior a la fecha de inicio.";
        }
        
        // Solo continuar si no hay errores de fecha
        if (!isset($error)) {
            $check = $conexion->prepare("SELECT id_usuario FROM usuario WHERE correo = ?");
            $check->bind_param("s", $correo);
            $check->execute();
            $check->store_result();

            if ($check->num_rows > 0) {
                $error = "Este usuario ya está registrado";
            } else {

                $hash = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $conexion->prepare("INSERT INTO usuario (correo, rol, contrasena) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $correo, $rol, $hash);

                if ($stmt->execute()) {
                    $usuario_fk = $stmt->insert_id;
                    $success = true;

                    if ($rol === "Usuario") {
                        // Registrar póliza para usuario
                        $sql2 = $conexion->prepare("
                            INSERT INTO poliza (
                                usuario_fk, poliza_id, tipo_poliza, fecha_inicio, fecha_fin, fecha_efectiva,
                                nombre, apellido1, apellido2, telefono, fecha_nacimiento, sexo, estatus, edad,
                                calle, num_exterior, num_interior, colonia, codigo_postal, municipio_fk, entidad_fk
                            ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
                        ");

                        $sql2->bind_param(
                            "issssssssssssisssisii",
                            $usuario_fk,
                            $poliza,
                            $tipo_poliza,
                            $fecha_inicio,
                            $fecha_fin,
                            $fecha_efectiva,
                            $nombre,
                            $ap_pat,
                            $ap_mat,
                            $telefono,
                            $fecha_nac,
                            $sexo,
                            $estatus,
                            $edad,
                            $calle,
                            $num_ext,
                            $num_int,
                            $colonia,
                            $cp,
                            $municipio_fk,
                            $entidad_fk
                        );

                        if (!$sql2->execute()) {
                            $error = "Error al registrar la póliza: " . $sql2->error;
                            $success = false;
                        }
                        $sql2->close();

                    } elseif ($rol === "Medico") {
                        // Registrar médico
                        $activo = 1; // Médico activo por defecto
                        
                        $sql_medico = $conexion->prepare("
                            INSERT INTO medico (
                                id_hospital, nombre, telefono, fecha_registro, activo, usuario_fk
                            ) VALUES (?, ?, ?, ?, ?, ?)
                        ");

                        $sql_medico->bind_param(
                            "isssiss",
                            $id_hospital,
                            $nombre_medico,
                            $telefono_medico,
                            $fecha_registro,
                            $activo,
                            $usuario_fk
                        );

                        if ($sql_medico->execute()) {
                            $id_medico = $sql_medico->insert_id;
                            
                            // Registrar especialidades del médico
                            if (!empty($especialidades_medico)) {
                                foreach ($especialidades_medico as $especialidad_id) {
                                    $sql_especialidad = $conexion->prepare("
                                        INSERT INTO especialidad_medico (especialidad_fk, medico_fk) 
                                        VALUES (?, ?)
                                    ");
                                    $sql_especialidad->bind_param("ii", $especialidad_id, $id_medico);
                                    $sql_especialidad->execute();
                                    $sql_especialidad->close();
                                }
                            }
                        } else {
                            $error = "Error al registrar el médico: " . $sql_medico->error;
                            $success = false;
                        }
                        $sql_medico->close();
                    }

                    if ($success && !isset($error)) {
                        $_SESSION['mensaje_exito'] = "Usuario registrado exitosamente";
                        header("Location: P_Entrar.html");
                        exit();
                    }

                } else {
                    $error = "Error al registrar: " . $stmt->error;
                }

                $stmt->close();
            }

            $check->close();
        }
    }
}

  $fecha_nac = $_POST['fecha_nac'] ?? '';
  if (!empty($fecha_nac)) {
      $fechaNacimiento = new DateTime($fecha_nac);
      $hoy = new DateTime();
      $edad = $hoy->diff($fechaNacimiento)->y;
  } else {
      $edad = 0;
  }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Usuario</title>
    <link rel="stylesheet" href="css/estilos_registro.css">
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h2>Registro de Usuario</h2>
            
            <?php if (isset($error)): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['mensaje_exito'])): ?>
                <div class="success-message">
                    <?php echo htmlspecialchars($_SESSION['mensaje_exito']); ?>
                    <?php unset($_SESSION['mensaje_exito']); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="registro-form" onsubmit="return validarFormulario()">
                <div class="form-group">
                    <label for="correo">Correo Electrónico:</label>
                    <input type="email" id="correo" name="correo" placeholder="Correo electrónico" required value="<?php echo htmlspecialchars($correo ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="rol">Rol</label>
                    <select name="rol" id="rol" required onchange="toggleCampos(); validarFechas();">
                        <option value="" disabled selected>Seleccione un rol</option>
                        <option value="AdminGeneral" <?php echo ($rol ?? '') == 'AdminGeneral' ? 'selected' : ''; ?>>Administrador General</option>
                        <option value="AdminRegional" <?php echo ($rol ?? '') == 'AdminRegional' ? 'selected' : ''; ?>>Administrador Regional</option>
                        <option value="Usuario" <?php echo ($rol ?? '') == 'Usuario' ? 'selected' : ''; ?>>Usuario</option>
                        <option value="Medico" <?php echo ($rol ?? '') == 'Medico' ? 'selected' : ''; ?>>Médico</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="password">Contraseña:</label>
                    <input type="password" id="password" name="password" placeholder="Contraseña" required>
                </div>

                <div class="form-group">
                    <label for="reviewPassword">Confirmar Contraseña:</label>
                    <input type="password" id="reviewPassword" name="reviewPassword" placeholder="Repetir contraseña" required>
                </div>

                <!-- Campos para Usuario -->
                <div id="camposUsuario" class="campos-grupo">
                    <div class="separator">
                        <span>Datos personales del usuario</span>
                    </div>
                    
                    <div class="form-group">
                        <label>Póliza:</label>
                        <input type="text" name="poliza" class="campo-rol" value="<?php echo htmlspecialchars($poliza ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label>Tipo de póliza:</label>
                        <select name="tipo_poliza" class="campo-rol">
                            <option value="" disabled selected>Seleccione el tipo de póliza</option>
                            <option value="Normal" <?php echo ($tipo_poliza ?? '') == 'Normal' ? 'selected' : ''; ?>>Normal</option>
                            <option value="Premiun" <?php echo ($tipo_poliza ?? '') == 'Premium' ? 'selected' : ''; ?>>Premium</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Fecha inicio:</label>
                        <input type="date" name="fecha_inicio" class="campo-rol" min="<?php echo date('Y-m-d'); ?>" value="<?php echo htmlspecialchars($fecha_inicio ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label>Fecha fin:</label>
                        <input type="date" name="fecha_fin" class="campo-rol" min="<?php echo date('Y-m-d'); ?>" value="<?php echo htmlspecialchars($fecha_fin ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label>Fecha efectiva:</label>
                        <input type="date" name="fecha_efectiva" class="campo-rol" min="<?php echo date('Y-m-d'); ?>" value="<?php echo htmlspecialchars($fecha_efectiva ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label>Nombre:</label>
                        <input type="text" name="nombre" class="campo-rol" value="<?php echo htmlspecialchars($nombre ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label>Apellido paterno:</label>
                        <input type="text" name="ap_pat" class="campo-rol" value="<?php echo htmlspecialchars($ap_pat ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label>Apellido materno:</label>
                        <input type="text" name="ap_mat" class="campo-rol" value="<?php echo htmlspecialchars($ap_mat ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label>Teléfono:</label>
                        <input type="text" name="telefono" class="campo-rol" value="<?php echo htmlspecialchars($telefono ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label>Fecha nacimiento:</label>
                        <input type="date" name="fecha_nac" class="campo-rol" max="<?php echo date('Y-m-d'); ?>" value="<?php echo htmlspecialchars($fecha_nac ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label>Sexo:</label>
                        <select name="sexo" class="campo-rol">
                            <option value="">Seleccione</option>
                            <option value="M" <?php echo ($sexo ?? '') == 'M' ? 'selected' : ''; ?>>Masculino</option>
                            <option value="F" <?php echo ($sexo ?? '') == 'F' ? 'selected' : ''; ?>>Femenino</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Estatus:</label>
                        <input type="text" name="estatus" class="campo-rol" value="<?php echo htmlspecialchars($estatus ?? ''); ?>">
                    </div>
<!-- 
                    <div class="form-group">
                        <label>Edad:</label>
                        <input type="number" name="edad" class="campo-rol" min="0" max="120" value="<?php echo htmlspecialchars($edad ?? ''); ?>">
                    </div> -->

                      <div class="form-group">
      <label>Edad:</label>
      <input type="number" name="edad" id="edad" class="campo-rol" readonly value="<?php echo htmlspecialchars($edad ?? ''); ?>">
  </div>
  
                    <div class="form-group">
                        <label>Calle:</label>
                        <input type="text" name="calle" class="campo-rol" value="<?php echo htmlspecialchars($calle ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label>Número exterior:</label>
                        <input type="text" name="num_ext" class="campo-rol" value="<?php echo htmlspecialchars($num_ext ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label>Número interior:</label>
                        <input type="text" name="num_int" class="campo-rol" value="<?php echo htmlspecialchars($num_int ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label>Colonia:</label>
                        <input type="text" name="colonia" class="campo-rol" value="<?php echo htmlspecialchars($colonia ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label>Código postal:</label>
                        <input type="text" name="cp" class="campo-rol" value="<?php echo htmlspecialchars($cp ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label>Municipio:</label>
                        <select name="municipio_fk" class="campo-rol">
                            <option value="">Seleccione un municipio</option>
                            <?php foreach ($municipios as $municipio): ?>
                                <option value="<?php echo $municipio['municipio_pk']; ?>" 
                                    <?php echo ($municipio_fk ?? '') == $municipio['municipio_pk'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($municipio['nombre_municipio']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Campos para Médico -->
                <div id="camposMedico" class="campos-grupo">
                    <div class="separator">
                        <span>Datos del médico</span>
                    </div>

                    <div class="form-group">
                        <label>Hospital:</label>
                        <select name="id_hospital" class="campo-rol">
                            <option value="">Seleccione un hospital</option>
                            <?php foreach ($hospitales as $hospital): ?>
                                <option value="<?php echo $hospital['hospital_pk']; ?>" 
                                    <?php echo ($id_hospital ?? '') == $hospital['hospital_pk'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($hospital['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Nombre del médico:</label>
                        <input type="text" name="nombre_medico" class="campo-rol" value="<?php echo htmlspecialchars($nombre_medico ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label>Teléfono del médico:</label>
                        <input type="text" name="telefono_medico" class="campo-rol" value="<?php echo htmlspecialchars($telefono_medico ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label>Especialidades:</label>
                        <div class="especialidades-grid">
                            <?php foreach ($especialidades as $especialidad): ?>
                                <div class="especialidad-item">
                                    <input type="checkbox" name="especialidades[]" value="<?php echo $especialidad['especialidad_pk']; ?>" 
                                        class="campo-rol" 
                                        <?php echo (in_array($especialidad['especialidad_pk'], $especialidades_medico ?? [])) ? 'checked' : ''; ?>>
                                    <label><?php echo htmlspecialchars($especialidad['nombre_especialidad']); ?></label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="button-group">
                    <button type="submit" class="btn btn-primary">Registrarse</button>
                    <button type="button" class="btn btn-secondary" onclick="location.href='admin.php'">Volver</button>
                </div>
            </form>
        </div>
    </div>
    <script src="js/agregar_usuario.js"></script>
</body>
</html>