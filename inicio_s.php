<?php
session_start();

// Conexión directa y simple
$conexion = new mysqli('yamabiko.proxy.rlwy.net', 'root', 'JBExxPHraFCRUGRIvODbqiGvtJyhpOwl', 'healthnet', '39168');

// Verificar conexión
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $correo = $_POST['correo'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (!empty($correo) && !empty($password)) {
        // Cambiamos la consulta para obtener también el ID y la contraseña encriptada
        $stmt = $conexion->prepare("SELECT id_usuario, correo, rol, contrasena FROM usuario WHERE correo = ?");
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id_usuario, $correo_db, $rol_db, $contrasena_db);
            $stmt->fetch();
            
            // Verificamos la contraseña usando password_verify
            if (password_verify($password, $contrasena_db)) {
                // GUARDAMOS EL ID_USUARIO EN LA SESIÓN - ¡ESTO ES ESENCIAL!
                $_SESSION['usuario'] = [
                    'id' => $id_usuario, // ← ESTA LÍNEA ES LA MÁS IMPORTANTE
                    'correo' => $correo_db,
                    'rol' => $rol_db,
                    'nombre' => $correo_db // Temporalmente usamos el correo como nombre
                ];
               
                // Redirección según rol
                if ($rol_db == 'AdminGeneral' || $rol_db == 'AdminRegional' || 
                    $rol_db == 'AdminNorte' || $rol_db == 'AdminSur' || 
                    $rol_db == 'AdminOriental' || $rol_db == 'AdminOccidental') {
                    header("Location: admin.php");
                } else if ($rol_db == 'medico') {
                    header("Location: medico.php"); 
                } else if ($rol_db == 'Usuario') {
                    header("Location: paciente.php"); 
                } else {
                    header("Location: P_Entrar.html");
                }
                exit();
            } else {
                $error = "Usuario o contraseña incorrectos";
            }
        } else {
            $error = "Usuario o contraseña incorrectos";
        }
        $stmt->close();
    } else {
        $error = "Por favor completa todos los campos";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login HealthNet</title>
    <link rel="stylesheet" href="css/estilo_IS.css">
</head>
<body>
    <div class="container">
        <div class="left">
            <img src="imagenes/logoH.png" alt="HealthNet Logo">
            <h1>HEALTHNET</h1>
        </div>

        <div class="right">
            <h2>Inicio de sesión</h2>
            
            <?php if (isset($error)) { ?>
                <div style="color: red; text-align: center; margin-bottom: 15px;">
                    <?php echo $error; ?>
                </div>
            <?php } ?>
            
            <form method="POST" action="">
                <label for="correo">Usuario:</label>
                <input type="text" id="correo" name="correo" placeholder="Correo electrónico" required>

                <label for="password">Contraseña:</label>
                <input type="password" id="password" name="password" placeholder="Contraseña" required>

                <button type="submit" class="btn">Iniciar sesión</button>
            </form>

            <p class="link">¿No tienes cuenta? <a href="registro.html">Registrate</a></p>
        </div>
    </div>
    
</body>
</html>