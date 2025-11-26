<?php
// Incluir configuración
require_once __DIR__ . '/../../config/config.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login HealthNet</title>
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>/estilo_IS.css">
</head>
<body>
    <div class="container">
        <div class="left">
            <img src="<?php echo IMAGES_URL; ?>/logoH.png" alt="HealthNet Logo">
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

            <p class="link">¿No tienes cuenta? <a href="<?php echo BASE_URL; ?>/public/registro.php">¡registrate!</a></p>
            
            <!-- Para pruebas -->
            <div style="margin-top: 20px; padding: 10px; background: #f5f5f5; border-radius: 5px;">
                <small><strong>Usuarios de prueba:</strong></small><br>
                <small>admin@healthnet.com / 123456 (Admin)</small><br>
                <small>medico@healthnet.com / 123456 (Médico)</small><br>
                <small>usuario@healthnet.com / 123456 (Paciente)</small>
            </div>
        </div>
    </div>
</body>
</html>