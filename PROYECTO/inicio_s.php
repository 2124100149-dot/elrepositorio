<?php
session_start();

// Conexión directa y simple
$conexion = new mysqli('localhost', 'root', '', 'healthnet');

// Verificar conexión
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $correo = $_POST['correo'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (!empty($correo) && !empty($password)) {
        // CONSULTA PREPARADA - MÁS SEGURA
        $stmt = $conexion->prepare("SELECT correo, rol FROM usuario WHERE correo = ? AND contrasena = MD5(?)");
        $stmt->bind_param("ss", $correo, $password);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            // Login exitoso
            $stmt->bind_result($correo_db, $rol_db);
            $stmt->fetch();
            
            $_SESSION['correo'] = $correo_db;
            $_SESSION['rol'] = $rol_db;
            
            // Redirigir SEGÚN EL ROL - ESTO ES LO QUE DEBES CAMBIAR
            if ($rol_db == 'administrador') {
                header("Location: admin.php");
            } else if ($rol_db == 'medico') {
                header("Location: medico.php");  // <- Añade esta línea
            } else if ($rol_db == 'Usuario') {
                header("Location: paciente.php"); // <- Y esta si tienes pacientes
            } else {
                header("Location: P_Entrar.html"); // Redirección por defecto
            }
            exit();
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