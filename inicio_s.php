<?php
session_start();

require_once __DIR__ . "/conexion.php";
$conexion = conectarDB();

// Si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $correo = $_POST['correo'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (!empty($correo) && !empty($password)) {
        // Cambia la consulta para que no use MD5
        $stmt = $conexion->prepare("SELECT id_usuario, correo, contrasena, rol FROM usuario WHERE correo = ?");
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $stmt->store_result();
        
            if ($stmt->num_rows > 0) {
                // Enlaza los resultados
                $stmt->bind_result($id_usuario, $correo_db, $contrasena_db, $rol_db);
                $stmt->fetch();
                
                // Verifica la contraseña usando password_verify
                if (password_verify($password, $contrasena_db)) {
                    } else if (md5($password) === $contrasena_db) {
        // Contraseña correcta (md5 legacy)
        // Opcional: actualizar a password_hash para la próxima vez
        $new_hash = password_hash($password, PASSWORD_DEFAULT);
        // Actualizar la base de datos con $new_hash
    } else {
        // Contraseña incorrecta
    }

                $_SESSION['usuario'] = [
                    'id_usuario' => $id_usuario,
                    'correo' => $correo_db,
                    'rol' => $rol_db,
                    'nombre' => $correo_db // Temporal, lo actualizaremos después
                ];
               
                // Redirigir según el rol
                if ($rol_db == 'AdminGeneral') {
                    header("Location: admin.php");
                } else if ($rol_db == 'AdminRegional') {
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
        </div>
    </div>
</body>
</html>