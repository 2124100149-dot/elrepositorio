<<<<<<< HEAD:app/views/register.php
=======
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
    "issssssssssssisssisii",  // 21 caracteres: 5 'i' + 16 's'
    $usuario_fk,              // i
    $poliza,                  // s
    $tipo_poliza,             // s
    $fecha_inicio,            // s
    $fecha_fin,               // s
    $fecha_efectiva,          // s
    $nombre,                  // s
    $ap_pat,                  // s
    $ap_mat,                  // s
    $telefono,                // s
    $fecha_nac,               // s
    $sexo,                    // s
    $estatus,                 // s
    $edad,                    // i
    $calle,                   // s
    $num_ext,                 // s
    $num_int,                 // s
    $colonia,                 // s
    $cp,                      // s
    $municipio_fk,            // i
    $entidad_fk               // i
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
>>>>>>> e4278a8 (cambios de mi rama alets que por alguna razon se fueron a la de sofi, git haciendo de las suyas pero esta vaina ya funciona mi gente, me voy a empedar ahorita mismo!):agregar_usuario.php
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro HealthNet</title>
    <link rel="stylesheet" href="../../assets/css/estilo_IS.css">
</head>
<body>
    <div class="container">
        <div class="left">
            <img src="../../assets/imagenes/logoH.png" alt="HealthNet Logo">
            <h1>HEALTHNET</h1>
        </div>

        <div class="right">
            <h2>Registro de usuario</h2>

            <?php if (isset($error)) { ?>
                <div style="color: red; text-align: center; margin-bottom: 15px;">
                    <?php echo $error; ?>
                </div>
            <?php } ?>

            <form method="POST" action="">
<<<<<<< HEAD:app/views/register.php
=======
                <!-- CAMPOS OBLIGATORIOS -->
>>>>>>> e4278a8 (cambios de mi rama alets que por alguna razon se fueron a la de sofi, git haciendo de las suyas pero esta vaina ya funciona mi gente, me voy a empedar ahorita mismo!):agregar_usuario.php
                <label for="correo">Nombre de Usuario:</label>
                <input type="email" id="correo" name="correo" placeholder="Correo electrónico" required>

                <label for="rol">Rol</label>
                <div class="select-box">
                    <select name="rol" id="rol" required onchange="toggleCamposUsuario()">
                        <option value="" disabled selected>Seleccione un rol</option>
                        <option value="AdminGeneral">Administrador General</option>
                        <option value="AdminRegional">Administrador Regional</option>
                        <option value="medico">Médico</option>
                        <option value="Usuario">Usuario</option>
                    </select>
                </div>

                <label for="password">Contraseña:</label>
                <input type="password" id="password" name="password" placeholder="Contraseña" required>

                <label for="reviewPassword">Ingrese su contraseña nuevamente:</label>
                <input type="password" id="reviewPassword" name="reviewPassword" placeholder="Repetir contraseña" required>

                <hr style="margin: 20px 0; border: none; border-top: 1px solid #ccc;">

                <!-- CAMPOS SOLO PARA USUARIO -->
                <h3 style="color:#1e3558;">Datos personales del usuario</h3>

                <label>Póliza:</label>
                <input type="text" name="poliza" class="campoUsuario">

                <label>Tipo de póliza:</label>
                <input type="text" name="tipo_poliza" class="campoUsuario">

                <label>Fecha inicio:</label>
                <input type="date" name="fecha_inicio" class="campoUsuario">

                <label>Fecha fin:</label>
                <input type="date" name="fecha_fin" class="campoUsuario">

                <label>Fecha efectiva:</label>
                <input type="date" name="fecha_efectiva" class="campoUsuario">

                <label>Nombre:</label>
                <input type="text" name="nombre" class="campoUsuario">

                <label>Apellido paterno:</label>
                <input type="text" name="ap_pat" class="campoUsuario">

                <label>Apellido materno:</label>
                <input type="text" name="ap_mat" class="campoUsuario">

                <label>Teléfono:</label>
                <input type="text" name="telefono" class="campoUsuario">

                <label>Fecha nacimiento:</label>
                <input type="date" name="fecha_nac" class="campoUsuario">

                <label>Sexo:</label>
                <input type="text" name="sexo" class="campoUsuario">

                <label>Estatus:</label>
                <input type="text" name="estatus" class="campoUsuario">

                <label>Edad:</label>
                <input type="number" name="edad" class="campoUsuario">

                <label>Calle:</label>
                <input type="text" name="calle" class="campoUsuario">

                <label>Número exterior:</label>
                <input type="text" name="num_ext" class="campoUsuario">

                <label>Número interior:</label>
                <input type="text" name="num_int" class="campoUsuario">

                <label>Colonia:</label>
                <input type="text" name="colonia" class="campoUsuario">

                <label>Código postal:</label>
                <input type="text" name="cp" class="campoUsuario">

                <label>Municipio:</label>
                <select name="municipio_fk" class="campoUsuario" required>
                    <option value="">Seleccione un municipio</option>
                    <?php foreach ($municipios as $municipio): ?>
                        <option value="<?php echo $municipio['municipio_pk']; ?>">
                            <?php echo htmlspecialchars($municipio['nombre_municipio']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button type="submit" class="btn">Registrarse</button>
                <button type="button" class="btn" onclick="location.href='../../public/index.php'">Iniciar sesión</button>
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
            campo.style.background = "#ffffffcc";
        } else {
            campo.disabled = true;
            campo.style.background = "#dddddd";
        }
    });
}

// Ejecutarlo al inicio por si se recarga el formulario
toggleCamposUsuario();
</script>

</body>