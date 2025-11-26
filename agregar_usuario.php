<?php
require 'conexion.php';

session_start();

// Conexión
$conexion = conectarDB();
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Procesar registro
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $correo = $_POST['correo'] ?? '';
    $rol = $_POST['rol'] ?? '';
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['reviewPassword'] ?? '';

    if (empty($correo) || empty($rol) || empty($password) || empty($password2)) {
        $error = "Por favor completa todos los campos";
    } 
    else if ($password !== $password2) {
        $error = "Las contraseñas no coinciden";
    } 
    else {

        // Verificar si ya existe el usuario
        $check = $conexion->prepare("SELECT correo FROM usuario WHERE correo = ?");
        $check->bind_param("s", $correo);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = "Este usuario ya está registrado";
        } else {

            // Registrar usuario nuevo
            $stmt = $conexion->prepare("INSERT INTO usuario (correo, rol, contrasena) VALUES (?, ?, MD5(?))");
            $stmt->bind_param("sss", $correo, $rol, $password);

            if ($stmt->execute()) {
                header("Location: P_Entrar.html");  // Redirigir al login
                exit();
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
    <title>Registro HealthNet</title>
    <link rel="stylesheet" href="css/estilo_IS.css">
</head>
<body>
    <div class="container">
        
        <div class="left">
            <img src="imagenes/logoH.png" alt="HealthNet Logo">
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

                <label for="correo">Nombre de Usuario:</label>
                <input type="email" id="correo" name="correo" placeholder="Correo electrónico" required>

                <label for="rol">Rol</label>
                <div class="select-box">
                    <select name="rol" id="rol" required>
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

                <button type="submit" class="btn">Registrarse</button>
                <button type="button" class="btn" onclick="location.href='inicio_s.php'">Iniciar sesión</button>
            </form>
        </div>
    </div>
</body>
</html>