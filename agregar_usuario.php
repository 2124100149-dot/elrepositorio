<?php
require 'conexion.php';
session_start();

// Conexión
$conexion = conectarDB();
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Obtener municipios para el dropdown
$municipios = [];
$query_municipios = $conexion->query("SELECT municipio_pk, nombre_municipio, entidad_fk FROM municipio ORDER BY nombre_municipio");
if ($query_municipios) {
    while ($row = $query_municipios->fetch_assoc()) {
        $municipios[] = $row;
    }
}

// Procesar registro
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // CAMPOS SIEMPRE REQUERIDOS
    $correo = $_POST['correo'] ?? '';
    $rol = $_POST['rol'] ?? '';
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['reviewPassword'] ?? '';

    // CAMPOS DEL USUARIO FINAL (SOLO APLICAN SI ROL = Usuario)
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
    $edad = $_POST['edad'] ?? '';
    $calle = $_POST['calle'] ?? '';
    $num_ext = $_POST['num_ext'] ?? '';
    $num_int = $_POST['num_int'] ?? '';
    $colonia = $_POST['colonia'] ?? '';
    $cp = $_POST['cp'] ?? '';
    $municipio_fk = $_POST['municipio_fk'] ?? '';

    // Buscar el entidad_fk correspondiente al municipio seleccionado
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

        // Verificar si ya existe el usuario
        $check = $conexion->prepare("SELECT id_usuario FROM usuario WHERE correo = ?");
        $check->bind_param("s", $correo);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = "Este usuario ya está registrado";
        } else {

            // Registrar usuario básico
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $conexion->prepare("INSERT INTO usuario (correo, rol, contrasena) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $correo, $rol, $hash);

            if ($stmt->execute()) {

                // SI EL ROL ES "Usuario", REGISTRAMOS LA PÓLIZA
                if ($rol === "Usuario") {

                    // Obtener el ID generado del usuario
                    $usuario_fk = $stmt->insert_id;

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
                    }

                    $sql2->close();
                }

                if (!isset($error)) {
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

            <form method="POST" action="" class="registro-form">
                <div class="form-group">
                    <label for="correo">Nombre de Usuario:</label>
                    <input type="email" id="correo" name="correo" placeholder="Correo electrónico" required>
                </div>

                <div class="form-group">
                    <label for="rol">Rol</label>
                    <select name="rol" id="rol" required onchange="toggleCamposUsuario()">
                        <option value="" disabled selected>Seleccione un rol</option>
                        <option value="AdminGeneral">Administrador General</option>
                        <option value="AdminRegional">Administrador Regional</option>
                        <option value="Usuario">Usuario</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="password">Contraseña:</label>
                    <input type="password" id="password" name="password" placeholder="Contraseña" required>
                </div>

                <div class="form-group">
                    <label for="reviewPassword">Ingrese su contraseña nuevamente:</label>
                    <input type="password" id="reviewPassword" name="reviewPassword" placeholder="Repetir contraseña" required>
                </div>

                <div class="separator">
                    <span>Datos personales del usuario</span>
                </div>

                <!-- CAMPOS SOLO PARA USUARIO -->
                <div id="camposUsuario" class="campos-usuario">
                    <div class="form-group">
                        <label>Póliza:</label>
                        <input type="text" name="poliza" class="campoUsuario">
                    </div>

                    <div class="form-group">
                        <label>Tipo de póliza:</label>
                        <input type="text" name="tipo_poliza" class="campoUsuario">
                    </div>

                    <div class="form-group">
                        <label>Fecha inicio:</label>
                        <input type="date" name="fecha_inicio" class="campoUsuario">
                    </div>

                    <div class="form-group">
                        <label>Fecha fin:</label>
                        <input type="date" name="fecha_fin" class="campoUsuario">
                    </div>

                    <div class="form-group">
                        <label>Fecha efectiva:</label>
                        <input type="date" name="fecha_efectiva" class="campoUsuario">
                    </div>

                    <div class="form-group">
                        <label>Nombre:</label>
                        <input type="text" name="nombre" class="campoUsuario">
                    </div>

                    <div class="form-group">
                        <label>Apellido paterno:</label>
                        <input type="text" name="ap_pat" class="campoUsuario">
                    </div>

                    <div class="form-group">
                        <label>Apellido materno:</label>
                        <input type="text" name="ap_mat" class="campoUsuario">
                    </div>

                    <div class="form-group">
                        <label>Teléfono:</label>
                        <input type="text" name="telefono" class="campoUsuario">
                    </div>

                    <div class="form-group">
                        <label>Fecha nacimiento:</label>
                        <input type="date" name="fecha_nac" class="campoUsuario">
                    </div>

                    <div class="form-group">
                        <label>Sexo:</label>
                        <input type="text" name="sexo" class="campoUsuario">
                    </div>

                    <div class="form-group">
                        <label>Estatus:</label>
                        <input type="text" name="estatus" class="campoUsuario">
                    </div>

                    <div class="form-group">
                        <label>Edad:</label>
                        <input type="number" name="edad" class="campoUsuario">
                    </div>

                    <div class="form-group">
                        <label>Calle:</label>
                        <input type="text" name="calle" class="campoUsuario">
                    </div>

                    <div class="form-group">
                        <label>Número exterior:</label>
                        <input type="text" name="num_ext" class="campoUsuario">
                    </div>

                    <div class="form-group">
                        <label>Número interior:</label>
                        <input type="text" name="num_int" class="campoUsuario">
                    </div>

                    <div class="form-group">
                        <label>Colonia:</label>
                        <input type="text" name="colonia" class="campoUsuario">
                    </div>

                    <div class="form-group">
                        <label>Código postal:</label>
                        <input type="text" name="cp" class="campoUsuario">
                    </div>

                    <div class="form-group">
                        <label>Municipio:</label>
                        <select name="municipio_fk" class="campoUsuario">
                            <option value="">Seleccione un municipio</option>
                            <?php foreach ($municipios as $municipio): ?>
                                <option value="<?php echo $municipio['municipio_pk']; ?>">
                                    <?php echo htmlspecialchars($municipio['nombre_municipio']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="button-group">
                    <button type="submit" class="btn btn-primary">Registrarse</button>
                    <button type="button" class="btn btn-secondary" onclick="location.href='admin.php'">Volver</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function toggleCamposUsuario() {
        let rol = document.getElementById("rol").value;
        let campos = document.querySelectorAll(".campoUsuario");

        campos.forEach(campo => {
            if (rol === "Usuario") {
                campo.disabled = false;
                campo.style.background = "#ffffff";
            } else {
                campo.disabled = true;
                campo.style.background = "#dddddd";
            }
        });
    }

    // Ejecutarlo al inicio por si se recarga el formulario
    document.addEventListener('DOMContentLoaded', function() {
        toggleCamposUsuario();
    });
    </script>
</body>
</html>